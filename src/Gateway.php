<?php
/**
 * Mollie gateway.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Banks\BankAccountDetails;
use Pronamic\WordPress\Pay\Banks\BankTransferDetails;
use Pronamic\WordPress\Pay\Core\Gateway as Core_Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Payments\FailureReason;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus;

/**
 * Title: Mollie
 * Description:
 * Copyright: 2005-2021 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.4
 * @since   1.1.0
 */
class Gateway extends Core_Gateway {
	/**
	 * Client.
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 * Config
	 *
	 * @var Config
	 */
	protected $config;

	/**
	 * Profile data store.
	 *
	 * @var ProfileDataStore
	 */
	private $profile_data_store;

	/**
	 * Customer data store.
	 *
	 * @var CustomerDataStore
	 */
	private $customer_data_store;

	/**
	 * Constructs and initializes an Mollie gateway
	 *
	 * @param Config $config Config.
	 */
	public function __construct( Config $config ) {
		parent::__construct( $config );

		$this->set_method( self::METHOD_HTTP_REDIRECT );

		// Supported features.
		$this->supports = array(
			'payment_status_request',
			'recurring_direct_debit',
			'recurring_credit_card',
			'recurring',
			'refunds',
			'webhook',
			'webhook_log',
			'webhook_no_config',
		);

		// Client.
		$this->client = new Client( (string) $config->api_key );

		// Data Stores.
		$this->profile_data_store  = new ProfileDataStore();
		$this->customer_data_store = new CustomerDataStore();

		// Actions.
		add_action( 'pronamic_payment_status_update', array( $this, 'copy_customer_id_to_wp_user' ), 99, 1 );
	}

	/**
	 * Get issuers
	 *
	 * @see Core_Gateway::get_issuers()
	 * @return array<int, array<string, array<string>>>
	 */
	public function get_issuers() {
		$groups = array();

		try {
			$result = $this->client->get_issuers();

			$groups[] = array(
				'options' => $result,
			);
		} catch ( Error $e ) {
			// Catch Mollie error.
			$error = new \WP_Error(
				'mollie_error',
				sprintf( '%1$s (%2$s) - %3$s', $e->get_title(), $e->getCode(), $e->get_detail() )
			);

			$this->set_error( $error );
		} catch ( \Exception $e ) {
			// Catch exceptions.
			$error = new \WP_Error( 'mollie_error', $e->getMessage() );

			$this->set_error( $error );
		}

		return $groups;
	}

	/**
	 * Get available payment methods.
	 *
	 * @see Core_Gateway::get_available_payment_methods()
	 * @return array<int, string>
	 */
	public function get_available_payment_methods() {
		$payment_methods = array();

		// Set sequence types to get payment methods for.
		$sequence_types = array( Sequence::ONE_OFF, Sequence::RECURRING, Sequence::FIRST );

		$results = array();

		foreach ( $sequence_types as $sequence_type ) {
			// Get active payment methods for Mollie account.
			try {
				$result = $this->client->get_payment_methods( $sequence_type );
			} catch ( Error $e ) {
				// Catch Mollie error.
				$error = new \WP_Error(
					'mollie_error',
					sprintf( '%1$s (%2$s) - %3$s', $e->get_title(), $e->getCode(), $e->get_detail() )
				);

				$this->set_error( $error );

				break;
			} catch ( \Exception $e ) {
				// Catch exceptions.
				$error = new \WP_Error( 'mollie_error', $e->getMessage() );

				$this->set_error( $error );

				break;
			}

			if ( Sequence::FIRST === $sequence_type ) {
				foreach ( $result as $method => $title ) {
					unset( $result[ $method ] );

					// Get WordPress payment method for direct debit method.
					$method         = Methods::transform_gateway_method( $method );
					$payment_method = array_search( $method, PaymentMethods::get_recurring_methods(), true );

					if ( $payment_method ) {
						$results[ $payment_method ] = $title;
					}
				}
			}

			if ( is_array( $result ) ) {
				$results = array_merge( $results, $result );
			}
		}

		// Transform to WordPress payment methods.
		foreach ( $results as $method => $title ) {
			$method = (string) $method;

			$payment_method = Methods::transform_gateway_method( $method );

			if ( PaymentMethods::is_recurring_method( $method ) ) {
				$payment_method = $method;
			}

			if ( null !== $payment_method ) {
				$payment_methods[] = (string) $payment_method;
			}
		}

		$payment_methods = array_unique( $payment_methods );

		return $payment_methods;
	}

	/**
	 * Get supported payment methods
	 *
	 * @see Pronamic_WP_Pay_Gateway::get_supported_payment_methods()
	 * @return array<string>
	 */
	public function get_supported_payment_methods() {
		return array(
			PaymentMethods::APPLE_PAY,
			PaymentMethods::BANCONTACT,
			PaymentMethods::BANK_TRANSFER,
			PaymentMethods::BELFIUS,
			PaymentMethods::CREDIT_CARD,
			PaymentMethods::DIRECT_DEBIT,
			PaymentMethods::DIRECT_DEBIT_BANCONTACT,
			PaymentMethods::DIRECT_DEBIT_IDEAL,
			PaymentMethods::DIRECT_DEBIT_SOFORT,
			PaymentMethods::EPS,
			PaymentMethods::GIROPAY,
			PaymentMethods::IDEAL,
			PaymentMethods::KBC,
			PaymentMethods::PAYPAL,
			PaymentMethods::PRZELEWY24,
			PaymentMethods::SOFORT,
		);
	}

	/**
	 * Get webhook URL for Mollie.
	 *
	 * @return string|null
	 */
	public function get_webhook_url() {
		$url = \rest_url( Integration::REST_ROUTE_NAMESPACE . '/webhook' );

		$host = wp_parse_url( $url, PHP_URL_HOST );

		if ( is_array( $host ) ) {
			// Parsing failure.
			$host = '';
		}

		if ( 'localhost' === $host ) {
			// Mollie doesn't allow localhost.
			return null;
		} elseif ( '.dev' === substr( $host, -4 ) ) {
			// Mollie doesn't allow the .dev TLD.
			return null;
		} elseif ( '.local' === substr( $host, -6 ) ) {
			// Mollie doesn't allow the .local TLD.
			return null;
		} elseif ( '.test' === substr( $host, -5 ) ) {
			// Mollie doesn't allow the .test TLD.
			return null;
		}

		return $url;
	}

	/**
	 * Start
	 *
	 * @see Pronamic_WP_Pay_Gateway::start()
	 * @param Payment $payment Payment.
	 * @return void
	 * @throws \Exception Throws exception on error creating Mollie customer for payment.
	 */
	public function start( Payment $payment ) {
		$request = new PaymentRequest(
			AmountTransformer::transform( $payment->get_total_amount() ),
			(string) $payment->get_description()
		);

		$request->redirect_url = $payment->get_return_url();
		$request->webhook_url  = $this->get_webhook_url();

		// Locale.
		$customer = $payment->get_customer();

		if ( null !== $customer ) {
			$request->locale = LocaleHelper::transform( $customer->get_locale() );
		}

		// Customer ID.
		$customer_id = $this->get_customer_id_for_payment( $payment );

		if ( null === $customer_id ) {
			$customer_id = $this->create_customer_for_payment( $payment );
		}

		if ( null !== $customer_id ) {
			$request->customer_id = $customer_id;
		}

		// Payment method.
		$payment_method = $payment->get_method();

		// Recurring payment method.
		$subscription = $payment->get_subscription();

		$is_recurring_method = ( $subscription && PaymentMethods::is_recurring_method( (string) $payment_method ) );

		// Consumer bank details.
		$consumer_bank_details = $payment->get_consumer_bank_details();

		if ( PaymentMethods::DIRECT_DEBIT === $payment_method && null !== $consumer_bank_details ) {
			$consumer_name = $consumer_bank_details->get_name();
			$consumer_iban = $consumer_bank_details->get_iban();

			$request->consumer_name    = $consumer_name;
			$request->consumer_account = $consumer_iban;

			// Check if one-off SEPA Direct Debit can be used, otherwise short circuit payment.
			if ( null !== $customer_id ) {
				// Find or create mandate.
				$mandate_id = $this->client->has_valid_mandate( $customer_id, PaymentMethods::DIRECT_DEBIT, $consumer_iban );

				if ( false === $mandate_id ) {
					$mandate = $this->client->create_mandate( $customer_id, $consumer_bank_details );

					if ( ! \property_exists( $mandate, 'id' ) ) {
						throw new \Exception( 'Missing mandate ID.' );
					}

					$mandate_id = $mandate->id;
				}

				// Charge immediately on-demand.
				$request->set_sequence_type( Sequence::RECURRING );
				$request->set_mandate_id( (string) $mandate_id );

				$is_recurring_method = true;

				$payment->recurring = true;
			}
		}

		if ( false === $is_recurring_method && null !== $payment_method ) {
			// Always use 'direct debit mandate via iDEAL/Bancontact/Sofort' payment methods as recurring method.
			$is_recurring_method = PaymentMethods::is_direct_debit_method( $payment_method );

			// Check for non-recurring methods for subscription payments.
			if ( false === $is_recurring_method && null !== $payment->get_periods() ) {
				$direct_debit_methods = PaymentMethods::get_direct_debit_methods();

				$is_recurring_method = \in_array( $payment_method, $direct_debit_methods, true );
			}
		}

		if ( $is_recurring_method ) {
			$request->sequence_type = $payment->get_recurring() ? Sequence::RECURRING : Sequence::FIRST;

			if ( Sequence::FIRST === $request->sequence_type ) {
				$payment_method = PaymentMethods::get_first_payment_method( $payment_method );
			}

			if ( Sequence::RECURRING === $request->sequence_type ) {
				// Use mandate from subscription.
				if ( $subscription && empty( $request->mandate_id ) ) {
					$subscription_mandate_id = $subscription->get_meta( 'mollie_mandate_id' );

					if ( false !== $subscription_mandate_id ) {
						$request->set_mandate_id( $subscription_mandate_id );
					}
				}

				// Use credit card for recurring Apple Pay payments.
				if ( PaymentMethods::APPLE_PAY === $payment_method ) {
					$payment_method = PaymentMethods::CREDIT_CARD;
				}

				$direct_debit_methods = PaymentMethods::get_direct_debit_methods();

				$recurring_method = \array_search( $payment_method, $direct_debit_methods, true );

				if ( \is_string( $recurring_method ) ) {
					$payment_method = $recurring_method;
				}

				$payment->set_action_url( $payment->get_return_url() );
			}
		}

		// Leap of faith if the WordPress payment method could not transform to a Mollie method?
		$request->method = Methods::transform( $payment_method, $payment_method );

		/**
		 * Metadata.
		 *
		 * Provide any data you like, for example a string or a JSON object.
		 * We will save the data alongside the payment. Whenever you fetch
		 * the payment with our API, we’ll also include the metadata. You
		 * can use up to approximately 1kB.
		 *
		 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment
		 * @link https://en.wikipedia.org/wiki/Metadata
		 */
		$metadata = null;

		/**
		 * Filters the Mollie metadata.
		 *
		 * @since 2.2.0
		 *
		 * @param mixed   $metadata Metadata.
		 * @param Payment $payment  Payment.
		 */
		$metadata = \apply_filters( 'pronamic_pay_mollie_payment_metadata', $metadata, $payment );

		$request->set_metadata( $metadata );

		// Issuer.
		if ( Methods::IDEAL === $request->method ) {
			$request->issuer = $payment->get_issuer();
		}

		// Billing email.
		$billing_email = $payment->get_email();

		/**
		 * Filters the Mollie payment billing email used for bank transfer payment instructions.
		 *
		 * @since 2.2.0
		 *
		 * @param string|null $billing_email Billing email.
		 * @param Payment     $payment       Payment.
		 */
		$billing_email = \apply_filters( 'pronamic_pay_mollie_payment_billing_email', $billing_email, $payment );

		$request->set_billing_email( $billing_email );

		// Due date.
		if ( ! empty( $this->config->due_date_days ) ) {
			try {
				$due_date = new DateTime( sprintf( '+%s days', $this->config->due_date_days ) );
			} catch ( \Exception $e ) {
				$due_date = null;
			}

			$request->set_due_date( $due_date );
		}

		// Create payment.
		$result = $this->client->create_payment( $request );

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.

		// Set transaction ID.
		if ( isset( $result->id ) ) {
			$payment->set_transaction_id( $result->id );
		}

		// Set expiry date.
		if ( isset( $result->expiresAt ) ) {
			try {
				$expires_at = new DateTime( $result->expiresAt );
			} catch ( \Exception $e ) {
				$expires_at = null;
			}

			$payment->set_expiry_date( $expires_at );
		}

		// Set status.
		if ( isset( $result->status ) ) {
			$payment->set_status( Statuses::transform( $result->status ) );
		}

		// Set bank transfer recipient details.
		if ( isset( $result->details ) ) {
			$bank_transfer_recipient_details = $payment->get_bank_transfer_recipient_details();

			if ( null === $bank_transfer_recipient_details ) {
				$bank_transfer_recipient_details = new BankTransferDetails();

				$payment->set_bank_transfer_recipient_details( $bank_transfer_recipient_details );
			}

			$bank_details = $bank_transfer_recipient_details->get_bank_account();

			if ( null === $bank_details ) {
				$bank_details = new BankAccountDetails();

				$bank_transfer_recipient_details->set_bank_account( $bank_details );
			}

			$details = $result->details;

			if ( isset( $details->bankName ) ) {
				/**
				 * Set `bankName` as bank details name, as result "Stichting Mollie Payments"
				 * is not the name of a bank, but the account holder name.
				 */
				$bank_details->set_name( $details->bankName );
			}

			if ( isset( $details->bankAccount ) ) {
				$bank_details->set_iban( $details->bankAccount );
			}

			if ( isset( $details->bankBic ) ) {
				$bank_details->set_bic( $details->bankBic );
			}

			if ( isset( $details->transferReference ) ) {
				$bank_transfer_recipient_details->set_reference( $details->transferReference );
			}
		}

		// Handle links.
		if ( isset( $result->_links ) ) {
			$links = $result->_links;

			// Action URL.
			if ( isset( $links->checkout->href ) ) {
				$payment->set_action_url( $links->checkout->href );
			}

			// Change payment state URL.
			if ( isset( $links->changePaymentState->href ) ) {
				$payment->set_meta( 'mollie_change_payment_state_url', $links->changePaymentState->href );
			}
		}

		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.
	}

	/**
	 * Update status of the specified payment
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 */
	public function update_status( Payment $payment ) {
		$transaction_id = $payment->get_transaction_id();

		if ( null === $transaction_id ) {
			return;
		}

		$mollie_payment = $this->client->get_payment( $transaction_id );

		$payment->set_status( Statuses::transform( $mollie_payment->get_status() ) );

		/**
		 * Mollie profile.
		 */
		$mollie_profile = new Profile();

		$mollie_profile->set_id( $mollie_payment->get_profile_id() );

		$profile_internal_id = $this->profile_data_store->get_or_insert_profile( $mollie_profile );

		/**
		 * If the Mollie payment contains a customer ID we will try to connect
		 * this Mollie customer ID the WordPress user and subscription.
		 * This can be useful in case when a WordPress user is created after
		 * a successful payment.
		 *
		 * @link https://www.gravityforms.com/add-ons/user-registration/
		 */
		$mollie_customer_id = $mollie_payment->get_customer_id();

		if ( null !== $mollie_customer_id ) {
			$mollie_customer = new Customer( $mollie_customer_id );

			$customer_internal_id = $this->customer_data_store->get_or_insert_customer(
				$mollie_customer,
				array(
					'profile_id' => $profile_internal_id,
				),
				array(
					'profile_id' => '%s',
				)
			);

			// Meta.
			$customer_id = $payment->get_meta( 'mollie_customer_id' );

			if ( empty( $customer_id ) ) {
				$payment->set_meta( 'mollie_customer_id', $mollie_customer->get_id() );
			}

			// Customer.
			$customer = $payment->get_customer();

			if ( null !== $customer ) {
				// Connect to user.
				$user_id = $customer->get_user_id();

				if ( null !== $user_id ) {
					$user = \get_user_by( 'id', $user_id );

					if ( false !== $user ) {
						$this->customer_data_store->connect_mollie_customer_to_wp_user( $mollie_customer, $user );
					}
				}
			}

			// Subscription.
			$subscription = $payment->get_subscription();

			if ( null !== $subscription ) {
				$customer_id = $subscription->get_meta( 'mollie_customer_id' );

				if ( empty( $customer_id ) ) {
					$subscription->set_meta( 'mollie_customer_id', $mollie_customer->get_id() );
				}

				// Update mandate in subscription meta.
				$mollie_mandate_id = $mollie_payment->get_mandate_id();

				if ( null !== $mollie_mandate_id ) {
					$mandate_id = $subscription->get_meta( 'mollie_mandate_id' );

					// Only update if no mandate has been set yet or if payment succeeded.
					if ( empty( $mandate_id ) || PaymentStatus::SUCCESS === $payment->get_status() ) {
						$this->update_subscription_mandate( $subscription, $mollie_mandate_id, $payment->get_method() );
					}
				}
			}
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.
		$mollie_payment_details = $mollie_payment->get_details();

		if ( null !== $mollie_payment_details ) {
			$consumer_bank_details = $payment->get_consumer_bank_details();

			if ( null === $consumer_bank_details ) {
				$consumer_bank_details = new BankAccountDetails();

				$payment->set_consumer_bank_details( $consumer_bank_details );
			}

			if ( isset( $mollie_payment_details->consumerName ) ) {
				$consumer_bank_details->set_name( $mollie_payment_details->consumerName );
			}

			if ( isset( $mollie_payment_details->cardHolder ) ) {
				$consumer_bank_details->set_name( $mollie_payment_details->cardHolder );
			}

			if ( isset( $mollie_payment_details->cardNumber ) ) {
				// The last four digits of the card number.
				$consumer_bank_details->set_account_number( $mollie_payment_details->cardNumber );
			}

			if ( isset( $mollie_payment_details->cardCountryCode ) ) {
				// The ISO 3166-1 alpha-2 country code of the country the card was issued in.
				$consumer_bank_details->set_country( $mollie_payment_details->cardCountryCode );
			}

			if ( isset( $mollie_payment_details->consumerAccount ) ) {
				switch ( $mollie_payment->get_method() ) {
					case Methods::BELFIUS:
					case Methods::DIRECT_DEBIT:
					case Methods::IDEAL:
					case Methods::KBC:
					case Methods::SOFORT:
						$consumer_bank_details->set_iban( $mollie_payment_details->consumerAccount );

						break;
					case Methods::BANCONTACT:
					case Methods::BANKTRANSFER:
					case Methods::PAYPAL:
					default:
						$consumer_bank_details->set_account_number( $mollie_payment_details->consumerAccount );

						break;
				}
			}

			if ( isset( $mollie_payment_details->consumerBic ) ) {
				$consumer_bank_details->set_bic( $mollie_payment_details->consumerBic );
			}

			/*
			 * Failure reason.
			 */
			$failure_reason = $payment->get_failure_reason();

			if ( null === $failure_reason ) {
				$failure_reason = new FailureReason();

				$payment->set_failure_reason( $failure_reason );
			}

			// SEPA Direct Debit.
			if ( isset( $mollie_payment_details->bankReasonCode ) ) {
				$failure_reason->set_code( $mollie_payment_details->bankReasonCode );
			}

			if ( isset( $mollie_payment_details->bankReason ) ) {
				$failure_reason->set_message( $mollie_payment_details->bankReason );
			}

			// Credit card.
			if ( isset( $mollie_payment_details->failureReason ) ) {
				$failure_reason->set_code( $mollie_payment_details->failureReason );
			}

			if ( isset( $mollie_payment_details->failureMessage ) ) {
				$failure_reason->set_message( $mollie_payment_details->failureMessage );
			}
		}

		$links = $mollie_payment->get_links();

		// Change payment state URL.
		if ( \property_exists( $links, 'changePaymentState' ) ) {
			$payment->set_meta( 'mollie_change_payment_state_url', $links->changePaymentState->href );
		}

		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.

		if ( $mollie_payment->has_chargebacks() ) {
			$mollie_chargebacks = $this->client->get_payment_chargebacks(
				$mollie_payment->get_id(),
				array( 'limit' => 1 )
			);

			$mollie_chargeback = \reset( $mollie_chargebacks );

			if ( false !== $mollie_chargeback ) {
				$subscriptions = array_filter(
					$payment->get_subscriptions(),
					function( $subscription ) {
						return SubscriptionStatus::ACTIVE === $subscription->get_status();
					}
				);

				foreach ( $subscriptions as $subscription ) {
					if ( $mollie_chargeback->get_created_at() > $subscription->get_activated_at() ) {
						$subscription->set_status( SubscriptionStatus::ON_HOLD );

						$subscription->add_note(
							\sprintf(
								/* translators: 1: Mollie chargeback ID, 2: Mollie payment ID */
								\__( 'Subscription put on hold due to chargeback `%1$s` of payment `%2$s`.', 'pronamic_ideal' ),
								\esc_html( $mollie_chargeback->get_id() ),
								\esc_html( $mollie_payment->get_id() )
							)
						);

						$subscription->save();
					}
				}
			}
		}

		// Refunds.
		$amount_refunded = $mollie_payment->get_amount_refunded();

		if ( null !== $amount_refunded ) {
			$refunded_amount = new Money( $amount_refunded->get_value(), $amount_refunded->get_currency() );

			$payment->set_refunded_amount( $refunded_amount );
		}
	}

	/**
	 * Update subscription mandate.
	 *
	 * @param Subscription $subscription   Subscription.
	 * @param string       $mandate_id     Mollie mandate ID.
	 * @param string|null  $payment_method Payment method.
	 * @return void
	 * @throws \Exception Throws exception if subscription note could not be added.
	 */
	public function update_subscription_mandate( Subscription $subscription, $mandate_id, $payment_method = null ) {
		$customer_id = (string) $subscription->get_meta( 'mollie_customer_id' );

		$mandate = $this->client->get_mandate( $mandate_id, $customer_id );

		if ( ! \is_object( $mandate ) ) {
			return;
		}

		// Update mandate.
		$old_mandate_id = $subscription->get_meta( 'mollie_mandate_id' );

		$subscription->set_meta( 'mollie_mandate_id', $mandate_id );

		if ( ! empty( $old_mandate_id ) && $old_mandate_id !== $mandate_id ) {
			$note = \sprintf(
			/* translators: 1: old mandate ID, 2: new mandate ID */
				\__( 'Mandate for subscription changed from "%1$s" to "%2$s".', 'pronamic_ideal' ),
				\esc_html( $old_mandate_id ),
				\esc_html( $mandate_id )
			);

			$subscription->add_note( $note );
		}

		// Update payment method.
		$old_method = $subscription->payment_method;
		$new_method = ( null === $payment_method && \property_exists( $mandate, 'method' ) ? Methods::transform_gateway_method( $mandate->method ) : $payment_method );

		// `Direct Debit` is not a recurring method, use `Direct Debit (mandate via ...)` instead.
		if ( PaymentMethods::DIRECT_DEBIT === $new_method ) {
			$new_method = PaymentMethods::DIRECT_DEBIT_IDEAL;

			// Use `Direct Debit (mandate via Bancontact)` if consumer account starts with `BE`.
			if ( \property_exists( $mandate, 'details' ) && 'BE' === \substr( $mandate->details->consumerAccount, 0, 2 ) ) {
				$new_method = PaymentMethods::DIRECT_DEBIT_BANCONTACT;
			}
		}

		if ( ! empty( $old_method ) && $old_method !== $new_method ) {
			$subscription->payment_method = $new_method;

			// Add note.
			$note = \sprintf(
				/* translators: 1: old payment method, 2: new payment method */
				\__( 'Payment method for subscription changed from "%1$s" to "%2$s".', 'pronamic_ideal' ),
				\esc_html( (string) PaymentMethods::get_name( $old_method ) ),
				\esc_html( (string) PaymentMethods::get_name( $new_method ) )
			);

			$subscription->add_note( $note );
		}

		$subscription->save();
	}

	/**
	 * Create refund.
	 *
	 * @param string $transaction_id Transaction ID.
	 * @param Money  $amount         Amount to refund.
	 * @param string $description    Refund reason.
	 * @return string
	 */
	public function create_refund( $transaction_id, Money $amount, $description = null ) {
		$request = new RefundRequest( AmountTransformer::transform( $amount ) );

		// Metadata payment ID.
		$payment = \get_pronamic_payment_by_transaction_id( $transaction_id );

		$payment_id = null;

		if ( null !== $payment ) {
			$payment_id = $payment->get_id();
		}

		$request->set_metadata(
			array(
				'pronamic_payment_id' => $payment_id,
			)
		);

		// Description.
		if ( ! empty( $description ) ) {
			$request->set_description( $description );
		}

		$refund = $this->client->create_refund( $transaction_id, $request );

		return $refund->get_id();
	}

	/**
	 * Get Mollie customer ID for payment.
	 *
	 * @param Payment $payment Payment.
	 * @return string|null
	 */
	public function get_customer_id_for_payment( Payment $payment ) {
		$customer_ids = $this->get_customer_ids_for_payment( $payment );

		$customer_id = $this->get_first_existing_customer_id( $customer_ids );

		return $customer_id;
	}

	/**
	 * Get Mollie customers for the specified payment.
	 *
	 * @param Payment $payment Payment.
	 * @return array<string>
	 */
	private function get_customer_ids_for_payment( Payment $payment ) {
		$customer_ids = array();

		// Customer ID from subscription meta.
		$subscription = $payment->get_subscription();

		if ( null !== $subscription ) {
			$customer_id = $this->get_customer_id_for_subscription( $subscription );

			if ( null !== $customer_id ) {
				$customer_ids[] = $customer_id;
			}
		}

		// Customer ID from WordPress user.
		$customer = $payment->get_customer();

		if ( null !== $customer ) {
			$user_id = $customer->get_user_id();

			if ( ! empty( $user_id ) ) {
				$user_customer_ids = $this->get_customer_ids_for_user( $user_id );

				$customer_ids = \array_merge( $customer_ids, $user_customer_ids );
			}
		}

		return $customer_ids;
	}

	/**
	 * Get Mollie customers for the specified WordPress user ID.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return array<string>
	 */
	public function get_customer_ids_for_user( $user_id ) {
		$customer_query = new CustomerQuery(
			array(
				'user_id' => $user_id,
			)
		);

		$customers = $customer_query->get_customers();

		$customer_ids = wp_list_pluck( $customers, 'mollie_id' );

		return $customer_ids;
	}

	/**
	 * Get customer ID for subscription.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return string|null
	 */
	private function get_customer_id_for_subscription( Subscription $subscription ) {
		$customer_id = $subscription->get_meta( 'mollie_customer_id' );

		if ( empty( $customer_id ) ) {
			// Try to get (legacy) customer ID from first payment.
			$first_payment = $subscription->get_first_payment();

			if ( null !== $first_payment ) {
				$customer_id = $first_payment->get_meta( 'mollie_customer_id' );
			}
		}

		if ( empty( $customer_id ) ) {
			return null;
		}

		return $customer_id;
	}

	/**
	 * Get first existing customer from customers list.
	 *
	 * @param array<string> $customer_ids Customers.
	 * @return string|null
	 * @throws Error Throws error on Mollie error.
	 */
	private function get_first_existing_customer_id( $customer_ids ) {
		$customer_ids = \array_filter( $customer_ids );

		$customer_ids = \array_unique( $customer_ids );

		foreach ( $customer_ids as $customer_id ) {
			try {
				$customer = $this->client->get_customer( $customer_id );
			} catch ( Error $error ) {
				// Check for status 410 ("Gone - The customer is no longer available").
				if ( 410 === $error->get_status() ) {
					continue;
				}

				throw $error;
			}

			if ( null !== $customer ) {
				return $customer_id;
			}
		}

		return null;
	}

	/**
	 * Create customer for payment.
	 *
	 * @param Payment $payment Payment.
	 * @return string|null
	 * @throws Error Throws Error when Mollie error occurs.
	 * @throws \Exception Throws exception when error in customer data store occurs.
	 */
	private function create_customer_for_payment( Payment $payment ) {
		$mollie_customer = new Customer();
		$mollie_customer->set_mode( $this->config->is_test_mode() ? 'test' : 'live' );
		$mollie_customer->set_email( $payment->get_email() );

		$pronamic_customer = $payment->get_customer();

		if ( null !== $pronamic_customer ) {
			// Name.
			$name = (string) $pronamic_customer->get_name();

			if ( '' !== $name ) {
				$mollie_customer->set_name( $name );
			}

			// Locale.
			$locale = $pronamic_customer->get_locale();

			if ( null !== $locale ) {
				$mollie_customer->set_locale( LocaleHelper::transform( $locale ) );
			}
		}

		// Try to get name from consumer bank details.
		$consumer_bank_details = $payment->get_consumer_bank_details();

		if ( null === $mollie_customer->get_name() && null !== $consumer_bank_details ) {
			$name = $consumer_bank_details->get_name();

			if ( null !== $name ) {
				$mollie_customer->set_name( $name );
			}
		}

		// Create customer.
		$mollie_customer = $this->client->create_customer( $mollie_customer );

		$customer_id = $this->customer_data_store->insert_customer( $mollie_customer );

		// Connect to user.
		if ( null !== $pronamic_customer ) {
			$user_id = $pronamic_customer->get_user_id();

			if ( null !== $user_id ) {
				$user = \get_user_by( 'id', $user_id );

				if ( false !== $user ) {
					$this->customer_data_store->connect_mollie_customer_to_wp_user( $mollie_customer, $user );
				}
			}
		}

		// Store customer ID in subscription meta.
		$subscription = $payment->get_subscription();

		if ( null !== $subscription ) {
			$subscription->set_meta( 'mollie_customer_id', $mollie_customer->get_id() );
		}

		return $mollie_customer->get_id();
	}

	/**
	 * Copy Mollie customer ID from subscription meta to WordPress user meta.
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 */
	public function copy_customer_id_to_wp_user( Payment $payment ) {
		if ( $this->config->id !== $payment->config_id ) {
			return;
		}

		// Subscription.
		$subscription = $payment->get_subscription();

		// Customer.
		$customer = $payment->get_customer();

		if ( null === $customer && null !== $subscription ) {
			$customer = $subscription->get_customer();
		}

		if ( null === $customer ) {
			return;
		}

		// WordPress user.
		$user_id = $customer->get_user_id();

		if ( null === $user_id ) {
			return;
		}

		$user = \get_user_by( 'id', $user_id );

		if ( false === $user ) {
			return;
		}

		// Customer IDs.
		$customer_ids = array();

		// Payment.
		$customer_ids[] = $payment->get_meta( 'mollie_customer_id' );

		// Subscription.
		if ( null !== $subscription ) {
			$customer_ids[] = $subscription->get_meta( 'mollie_customer_id' );
		}

		// Connect.
		$customer_ids = \array_filter( $customer_ids );
		$customer_ids = \array_unique( $customer_ids );

		foreach ( $customer_ids as $customer_id ) {
			$customer = new Customer( $customer_id );

			$this->customer_data_store->get_or_insert_customer( $customer );

			$this->customer_data_store->connect_mollie_customer_to_wp_user( $customer, $user );
		}
	}
}
