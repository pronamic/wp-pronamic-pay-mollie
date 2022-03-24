<?php
/**
 * Mollie gateway.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
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
use Pronamic\WordPress\Pay\Gateways\Mollie\Payment as MolliePayment;
use Pronamic\WordPress\Pay\Payments\FailureReason;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus;

/**
 * Title: Mollie
 * Description:
 * Copyright: 2005-2022 Pronamic
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
		$this->config = $config;

		parent::__construct( $config );

		$this->set_method( self::METHOD_HTTP_REDIRECT );

		// Supported features.
		$this->supports = array(
			'payment_status_request',
			'recurring_apple_pay',
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

		$result = $this->client->get_issuers();

		$groups[] = array(
			'options' => $result,
		);

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
			$result = $this->client->get_payment_methods( $sequence_type );

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
	 * @see Core_Gateway::get_supported_payment_methods()
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
	 * @param Payment $payment Payment.
	 * @return string|null
	 */
	public function get_webhook_url( Payment $payment ) {
		$url = \rest_url( Integration::REST_ROUTE_NAMESPACE . '/webhook/' . (string) $payment->get_id() );

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
	 * @see Core_Gateway::start()
	 * @param Payment $payment Payment.
	 * @return void
	 * @throws Error Mollie error.
	 * @throws \Exception Throws exception on error creating Mollie customer for payment.
	 */
	public function start( Payment $payment ) {
		$description = (string) $payment->get_description();

		/**
		 * Filters the Mollie payment description.
		 * 
		 * The maximum length of the description field differs per payment
		 * method, with the absolute maximum being 255 characters.
		 *
		 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment#parameters
		 * @since 3.0.1
		 * @param string  $description Description.
		 * @param Payment $payment     Payment.
		 */
		$description = \apply_filters( 'pronamic_pay_mollie_payment_description', $description, $payment );

		$request = new PaymentRequest(
			AmountTransformer::transform( $payment->get_total_amount() ),
			$description
		);

		$request->redirect_url = $payment->get_return_url();
		$request->webhook_url  = $this->get_webhook_url( $payment );

		// Locale.
		$customer = $payment->get_customer();

		if ( null !== $customer ) {
			$request->locale = LocaleHelper::transform( $customer->get_locale() );
		}

		// Customer ID.
		$customer_id = $this->get_customer_id_for_payment( $payment );

		if ( null === $customer_id ) {
			$sequence_type = $payment->get_meta( 'mollie_sequence_type' );

			if ( 'recurring' !== $sequence_type ) {
				$customer_id = $this->create_customer_for_payment( $payment );
			}
		}

		if ( null !== $customer_id ) {
			$request->customer_id = $customer_id;

			// Set Mollie customer ID in subscription meta.
			foreach ( $payment->get_subscriptions() as $subscription ) {
				$mollie_customer_id = $subscription->get_meta( 'mollie_customer_id' );

				if ( empty( $mollie_customer_id ) ) {
					$subscription->set_meta( 'mollie_customer_id', $customer_id );

					$subscription->save();
				}
			}
		}

		/**
		 * Payment method.
		 *
		 * Leap of faith if the WordPress payment method could not transform to a Mollie method?
		 */
		$payment_method = $payment->get_payment_method();

		$request->set_method( Methods::transform( $payment_method, $payment_method ) );

		/**
		 * Sequence type.
		 *
		 * Recurring payments are created through the Payments API by providing a `sequenceType`.
		 */
		$subscriptions = $payment->get_subscriptions();

		if (
			\count( $subscriptions ) > 0
				||
			PaymentMethods::is_direct_debit_method( $payment_method )
		) {
			$request->set_sequence_type( 'first' );

			foreach ( $subscriptions as $subscription ) {
				$mandate_id = $subscription->get_meta( 'mollie_mandate_id' );

				if ( ! empty( $mandate_id ) ) {
					$request->set_mandate_id( $mandate_id );
				}
			}
		}

		$sequence_type = $payment->get_meta( 'mollie_sequence_type' );

		if ( ! empty( $sequence_type ) ) {
			$request->set_sequence_type( $sequence_type );
		}

		if ( 'recurring' === $request->get_sequence_type() ) {
			$request->set_method( null );
		}

		if ( 'first' === $request->get_sequence_type() ) {
			$first_method = PaymentMethods::get_first_payment_method( $payment_method );

			$request->set_method( Methods::transform( $first_method, $first_method ) );
		}

		/**
		 * Direct Debit.
		 *
		 * Check if one-off SEPA Direct Debit can be used, otherwise short circuit payment.
		 */
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
				$request->set_sequence_type( 'recurring' );
				$request->set_mandate_id( (string) $mandate_id );
			}
		}

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
			$request->issuer = $payment->get_meta( 'issuer' );
		}

		// Billing email.
		$billing_email = ( null === $customer ) ? null : $customer->get_email();

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
		$attempt = (int) $payment->get_meta( 'mollie_create_payment_attempt' );
		$attempt = empty( $attempt ) ? 1 : $attempt + 1;

		$payment->set_meta( 'mollie_create_payment_attempt', $attempt );

		try {
			$mollie_payment = $this->client->create_payment( $request );

			$payment->delete_meta( 'mollie_create_payment_attempt' );
		} catch ( Error $error ) {
			if ( 'recurring' !== $request->get_sequence_type() ) {
				throw $error;
			}

			if ( null === $request->get_mandate_id() ) {
				throw $error;
			}

			/**
			 * Only schedule retry for specific status codes.
			 *
			 * @link https://docs.mollie.com/overview/handling-errors
			 */
			if ( ! \in_array( $error->get_status(), array( 429, 502, 503 ), true ) ) {
				throw $error;
			}

			\as_schedule_single_action(
				\time() + $this->get_retry_seconds( $attempt ),
				'pronamic_pay_mollie_payment_start',
				array(
					'payment_id' => $payment->get_id(),
				),
				'pronamic-pay-mollie'
			);

			// Add note.
			$payment->add_note(
				\sprintf(
					'%s - %s - %s',
					$error->get_status(),
					$error->get_title(),
					$error->get_detail()
				)
			);

			$payment->save();

			return;
		}

		// Update payment from Mollie payment.
		$this->update_payment_from_mollie_payment( $payment, $mollie_payment );
	}

	/**
	 * Get retry seconds.
	 *
	 * @param int $attempt Number of attempts.
	 * @return int
	 */
	private function get_retry_seconds( $attempt ) {
		switch ( $attempt ) {
			case 1:
				return 5 * MINUTE_IN_SECONDS;
			case 2:
				return HOUR_IN_SECONDS;
			case 3:
				return 12 * HOUR_IN_SECONDS;
			case 4:
			default:
				return DAY_IN_SECONDS;
		}
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

		// Update payment from Mollie payment.
		$mollie_payment = $this->client->get_payment( $transaction_id );

		$this->update_payment_from_mollie_payment( $payment, $mollie_payment );
	}

	/**
	 * Update payment from Mollie payment.
	 *
	 * @param Payment       $payment        Payment.
	 * @param MolliePayment $mollie_payment Mollie payment.
	 * @return void
	 */
	public function update_payment_from_mollie_payment( Payment $payment, MolliePayment $mollie_payment ) {
		/**
		 * Transaction ID.
		 */
		$transaction_id = $mollie_payment->get_id();

		$payment->set_transaction_id( $transaction_id );

		/**
		 * Status.
		 */
		$status = Statuses::transform( $mollie_payment->get_status() );

		if ( null !== $status ) {
			$payment->set_status( $status );
		}

		/**
		 * Payment method.
		 */
		$method = $mollie_payment->get_method();

		if ( null !== $method ) {
			$payment_method = Methods::transform_gateway_method( $method );

			// Use wallet method as payment method.
			$mollie_payment_details = $mollie_payment->get_details();

			if ( null !== $mollie_payment_details && isset( $mollie_payment_details->wallet ) ) {
				$wallet_method = Methods::transform_gateway_method( $mollie_payment_details->wallet );

				if ( null !== $wallet_method ) {
					$payment_method = $wallet_method;
				}
			}

			if ( null !== $payment_method ) {
				$payment->set_payment_method( $payment_method );

				// Update subscription payment method.
				foreach ( $payment->get_subscriptions() as $subscription ) {
					if ( null === $subscription->get_payment_method() ) {
						$subscription->set_payment_method( $payment->get_payment_method() );

						$subscription->save();
					}
				}
			}
		}

		/**
		 * Expiry date.
		 */
		$expires_at = $mollie_payment->get_expires_at();

		if ( null !== $expires_at ) {
			$expiry_date = DateTime::create_from_interface( $expires_at );

			$payment->set_expiry_date( $expiry_date );
		}

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
		}

		/**
		 * Customer ID.
		 */
		$mollie_customer_id = $mollie_payment->get_customer_id();

		if ( null !== $mollie_customer_id ) {
			$customer_id = $payment->get_meta( 'mollie_customer_id' );

			if ( empty( $customer_id ) ) {
				$payment->set_meta( 'mollie_customer_id', $mollie_customer_id );
			}

			foreach ( $payment->get_subscriptions() as $subscription ) {
				$customer_id = $subscription->get_meta( 'mollie_customer_id' );

				if ( empty( $customer_id ) ) {
					$subscription->set_meta( 'mollie_customer_id', $mollie_customer_id );
				}
			}
		}

		/**
		 * Mandate ID.
		 */
		$mollie_mandate_id = $mollie_payment->get_mandate_id();

		if ( null !== $mollie_mandate_id ) {
			$mandate_id = $payment->get_meta( 'mollie_mandate_id' );

			if ( empty( $mandate_id ) ) {
				$payment->set_meta( 'mollie_mandate_id', $mollie_mandate_id );
			}

			$is_first_and_successful = ( 'first' === $mollie_payment->get_sequence_type() && PaymentStatus::SUCCESS === $payment->get_status() );

			foreach ( $payment->get_subscriptions() as $subscription ) {
				$mandate_id = $subscription->get_meta( 'mollie_mandate_id' );

				if ( empty( $mandate_id ) || $is_first_and_successful ) {
					$this->update_subscription_mandate( $subscription, $mollie_mandate_id );
				}
			}
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.

		/**
		 * Details.
		 */
		$mollie_payment_details = $mollie_payment->get_details();

		if ( null !== $mollie_payment_details ) {
			/**
			 * Consumer bank details.
			 */
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

			/**
			 * Bank transfer recipient details.
			 */
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

			if ( isset( $mollie_payment_details->bankName ) ) {
				/**
				 * Set `bankName` as bank details name, as result "Stichting Mollie Payments"
				 * is not the name of a bank, but the account holder name.
				 */
				$bank_details->set_name( $mollie_payment_details->bankName );
			}

			if ( isset( $mollie_payment_details->bankAccount ) ) {
				$bank_details->set_iban( $mollie_payment_details->bankAccount );
			}

			if ( isset( $mollie_payment_details->bankBic ) ) {
				$bank_details->set_bic( $mollie_payment_details->bankBic );
			}

			if ( isset( $mollie_payment_details->transferReference ) ) {
				$bank_transfer_recipient_details->set_reference( $mollie_payment_details->transferReference );
			}

			/*
			 * Failure reason.
			 */
			$failure_reason = $payment->get_failure_reason();

			if ( null === $failure_reason ) {
				$failure_reason = new FailureReason();
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

			$failure_code    = $failure_reason->get_code();
			$failure_message = $failure_reason->get_message();

			if ( ! empty( $failure_code ) || ! empty( $failure_message ) ) {
				$payment->set_failure_reason( $failure_reason );
			}
		}

		/**
		 * Links.
		 */
		$links = $mollie_payment->get_links();

		// Action URL.
		if ( \property_exists( $links, 'checkout' ) ) {
			$payment->set_action_url( $links->checkout->href );
		}

		// Change payment state URL.
		if ( \property_exists( $links, 'changePaymentState' ) ) {
			$payment->set_meta( 'mollie_change_payment_state_url', $links->changePaymentState->href );
		}

		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.

		/**
		 * Chargebacks.
		 */
		if ( $mollie_payment->has_chargebacks() ) {
			$mollie_chargebacks = $this->client->get_payment_chargebacks(
				$mollie_payment->get_id(),
				array( 'limit' => 1 )
			);

			$mollie_chargeback = \reset( $mollie_chargebacks );

			if ( false !== $mollie_chargeback ) {
				$subscriptions = array_filter(
					$payment->get_subscriptions(),
					function ( $subscription ) {
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
					}
				}
			}
		}

		/**
		 * Refunds.
		 */
		$amount_refunded = $mollie_payment->get_amount_refunded();

		if ( null !== $amount_refunded ) {
			$refunded_amount = new Money( $amount_refunded->get_value(), $amount_refunded->get_currency() );

			$payment->set_refunded_amount( $refunded_amount->get_value() > 0 ? $refunded_amount : null );
		}

		// Save.
		$payment->save();

		foreach ( $payment->get_subscriptions() as $subscription ) {
			$subscription->save();
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
		$old_method = $subscription->get_payment_method();
		$new_method = ( null === $payment_method && \property_exists( $mandate, 'method' ) ? Methods::transform_gateway_method( $mandate->method ) : $payment_method );

		if ( ! empty( $old_method ) && $old_method !== $new_method ) {
			$subscription->set_payment_method( $new_method );

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

		if ( null !== $payment ) {
			$request->set_metadata(
				array(
					'pronamic_payment_id' => $payment->get_id(),
				)
			);
		}

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
		$subscriptions = $payment->get_subscriptions();

		foreach ( $subscriptions as $subscription ) {
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
		$customer = $payment->get_customer();

		$mollie_customer = new Customer();
		$mollie_customer->set_mode( $this->config->is_test_mode() ? 'test' : 'live' );
		$mollie_customer->set_email( null === $customer ? null : $customer->get_email() );

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
		$subscriptions = $payment->get_subscriptions();

		foreach ( $subscriptions as $subscription ) {
			$subscription->set_meta( 'mollie_customer_id', $mollie_customer->get_id() );

			$subscription->save();
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

		// Subscriptions.
		$subscriptions = $payment->get_subscriptions();

		foreach ( $subscriptions as $subscription ) {
			// Customer.
			$customer = $payment->get_customer();

			if ( null === $customer ) {
				$customer = $subscription->get_customer();
			}

			if ( null === $customer ) {
				continue;
			}

			// WordPress user.
			$user_id = $customer->get_user_id();

			if ( null === $user_id ) {
				continue;
			}

			$user = \get_user_by( 'id', $user_id );

			if ( false === $user ) {
				continue;
			}

			// Customer IDs.
			$customer_ids = array(
				// Payment.
				$payment->get_meta( 'mollie_customer_id' ),

				// Subscription.
				$subscription->get_meta( 'mollie_customer_id' ),
			);

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

	/**
	 * Get mode.
	 * 
	 * @return string
	 */
	public function get_mode() {
		return $this->config->is_test_mode() ? 'test' : 'live';
	}
}
