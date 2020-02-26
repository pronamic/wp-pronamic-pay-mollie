<?php
/**
 * Mollie gateway.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use DateInterval;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Banks\BankAccountDetails;
use Pronamic\WordPress\Pay\Banks\BankTransferDetails;
use Pronamic\WordPress\Pay\Core\Gateway as Core_Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Core\Recurring as Core_Recurring;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;

/**
 * Title: Mollie
 * Description:
 * Copyright: 2005-2020 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.9
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
			'webhook',
			'webhook_log',
			'webhook_no_config',
		);

		// Client.
		$this->client = new Client( \strval( $config->api_key ) );

		// Data Stores.
		$this->profile_data_store  = new ProfileDataStore();
		$this->customer_data_store = new CustomerDataStore();
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
	 * @return array<string>
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
			if ( PaymentMethods::is_recurring_method( $method ) ) {
				$payment_method = $method;
			} else {
				$payment_method = Methods::transform_gateway_method( $method );
			}

			if ( $payment_method ) {
				$payment_methods[] = $payment_method;
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
	 */
	public function start( Payment $payment ) {
		$request = new PaymentRequest(
			AmountTransformer::transform( $payment->get_total_amount() ),
			\strval( $payment->get_description() )
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
		$is_recurring_method = ( $payment->get_subscription() && PaymentMethods::is_recurring_method( $payment_method ) );

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

					$mandate_id = $mandate->id;
				}

				// Charge immediately on-demand.
				$request->sequence_type = Sequence::RECURRING;
				$request->mandate_id    = $mandate_id;

				$is_recurring_method = true;

				$payment->recurring = true;
			}
		}

		if ( false === $is_recurring_method ) {
			// Always use 'direct debit mandate via iDEAL/Bancontact/Sofort' payment methods as recurring method.
			$is_recurring_method = PaymentMethods::is_direct_debit_method( $payment_method );
		}

		if ( $is_recurring_method ) {
			$request->sequence_type = $payment->get_recurring() ? Sequence::RECURRING : Sequence::FIRST;

			if ( Sequence::FIRST === $request->sequence_type ) {
				$payment_method = PaymentMethods::get_first_payment_method( $payment_method );
			}

			if ( Sequence::RECURRING === $request->sequence_type ) {
				$payment->set_action_url( $payment->get_return_url() );
			}
		}

		// Leap of faith if the WordPress payment method could not transform to a Mollie method?
		$request->method = Methods::transform( $payment_method, $payment_method );

		// Issuer.
		if ( Methods::IDEAL === $request->method ) {
			$request->issuer = $payment->get_issuer();
		}

		// Due date.
		try {
			$due_date = new DateTime( sprintf( '+%s days', $this->config->due_date_days ) );
		} catch ( \Exception $e ) {
			$due_date = null;
		}

		$request->set_due_date( $due_date );

		// Create payment.
		$result = $this->client->create_payment( $request );

		// Set transaction ID.
		if ( isset( $result->id ) ) {
			$payment->set_transaction_id( $result->id );
		}

		// Set expiry date.
		/* phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase */
		if ( isset( $result->expiresAt ) ) {
			try {
				$expires_at = new DateTime( $result->expiresAt );
			} catch ( \Exception $e ) {
				$expires_at = null;
			}

			$payment->set_expiry_date( $expires_at );
		}
		/* phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase */

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

			/*
			 * @codingStandardsIgnoreStart
			 *
			 * Ignore coding standards because of sniff WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
			 */
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
			// @codingStandardsIgnoreEnd
		}

		// Set action URL.
		if ( isset( $result->_links ) ) {
			if ( isset( $result->_links->checkout->href ) ) {
				$payment->set_action_url( $result->_links->checkout->href );
			}
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

		$mollie_payment = $this->client->get_payment( $transaction_id );

		if ( isset( $mollie_payment->status ) ) {
			$payment->set_status( Statuses::transform( $mollie_payment->status ) );
		}

		/**
		 * Mollie profile.
		 */
		$mollie_profile = new Profile();
		$mollie_profile->set_id( $mollie_payment->profileId );

		$profile_id = $this->profile_data_store->save_profile( $mollie_profile );

		/**
		 * If the Mollie payment contains a customer ID we will try to connect
		 * this Mollie customer ID the WordPress user and subscription.
		 * This can be usefull in case when a WordPress user is created after
		 * a succesfull payment.
		 *
		 * @link https://www.gravityforms.com/add-ons/user-registration/
		 */
		if ( isset( $mollie_payment->customerId ) ) {
			$mollie_customer = new Customer();
			$mollie_customer->set_id( $mollie_payment->customerId );

			$mollie_customer_data = $this->customer_data_store->get_customer( $mollie_customer );

			if ( null === $mollie_customer_data ) {
				$this->customer_data_store->insert_customer(
					$mollie_customer,
					array(
						'profile_id' => $profile_id,
					),
					array(
						'profile_id' => '%s',
					)
				);
			}

			$customer = $payment->get_customer();

			if ( null !== $customer ) {
				// Connect to user.
				$user = \get_user_by( 'id', $customer->get_user_id() );

				$this->customer_data_store->connect_mollie_customer_to_wp_user( $mollie_customer, $user );
			}

			$subscription = $payment->get_subscription();

			if ( null !== $subscription ) {
				$customer_id = $subscription->get_meta( 'mollie_customer_id' );

				if ( empty( $customer_id ) ) {
					$subscription->set_meta( 'mollie_customer_id', $mollie_customer->get_id() );
				}
			}
		}

		if ( isset( $mollie_payment->details ) ) {
			$consumer_bank_details = $payment->get_consumer_bank_details();

			if ( null === $consumer_bank_details ) {
				$consumer_bank_details = new BankAccountDetails();

				$payment->set_consumer_bank_details( $consumer_bank_details );
			}

			$details = $mollie_payment->details;

			/*
			 * @codingStandardsIgnoreStart
			 *
			 * Ignore coding standards because of sniff WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
			 */
			if ( isset( $details->consumerName ) ) {
				$consumer_bank_details->set_name( $details->consumerName );
			}

			if ( isset( $details->cardHolder ) ) {
				$consumer_bank_details->set_name( $details->cardHolder );
			}

			if ( isset( $details->cardNumber ) ) {
				// The last four digits of the card number.
				$consumer_bank_details->set_account_number( $details->cardNumber );
			}

			if ( isset( $details->cardCountryCode ) ) {
				// The ISO 3166-1 alpha-2 country code of the country the card was issued in.
				$consumer_bank_details->set_country( $details->cardCountryCode );
			}

			if ( isset( $details->consumerAccount ) ) {
				switch ( $mollie_payment->method ) {
					case Methods::BELFIUS:
					case Methods::DIRECT_DEBIT:
					case Methods::IDEAL:
					case Methods::KBC:
					case Methods::SOFORT:
						$consumer_bank_details->set_iban( $details->consumerAccount );

						break;
					case Methods::BANCONTACT:
					case Methods::BANKTRANSFER:
					case Methods::PAYPAL:
					default:
						$consumer_bank_details->set_account_number( $details->consumerAccount );

						break;
				}
			}

			if ( isset( $details->consumerBic ) ) {
				$consumer_bank_details->set_bic( $details->consumerBic );
			}
			// @codingStandardsIgnoreEnd
		}
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
			$customer_id = $this->get_customer_id_for_subscription( $payment->get_subscription() );

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
	private function get_customer_ids_for_user( $user_id ) {
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
	 * @param array $customer_ids Customers.
	 * @return string|null
	 */
	private function get_first_existing_customer_id( $customer_ids ) {
		$customer_ids = \array_filter( $customer_ids );

		$customer_ids = \array_unique( $customer_ids );

		foreach ( $customer_ids as $customer_id ) {
			$customer = $this->client->get_customer( $customer_id );

			if ( null !== $customer ) {
				return $customer_id;
			}
		}

		return null;
	}

	/**
	 * Create customer for payment.
	 *
	 * @param Payment $payment
	 * @return string|null
	 * @throws Error Throws Error when Mollie error occurs.
	 */
	private function create_customer_for_payment( Payment $payment ) {
		$mollie_customer = new Customer();
		$mollie_customer->set_mode( $this->config->is_test_mode() ? 'test' : 'live' );
		$mollie_customer->set_email( $payment->get_email() );

		$pronamic_customer = $payment->get_customer();

		if ( null !== $pronamic_customer ) {
			$name = $pronamic_customer->get_name();

			if ( null !== $name ) {
				$mollie_customer->set_name( \strval( $name ) );
			}
		}

		// Create customer.
		$mollie_customer = $this->client->create_customer( $mollie_customer );

		$customer_id = $this->customer_data_store->insert_customer( $mollie_customer );

		// Connect to user.
		$user = \get_user_by( 'id', $pronamic_customer->get_user_id() );

		$this->customer_data_store->connect_mollie_customer_to_wp_user( $mollie_customer, $user );

		// Store customer ID in subscription meta.
		$subscription = $payment->get_subscription();

		if ( null !== $subscription ) {
			$subscription->set_meta( 'mollie_customer_id', $mollie_customer->get_id() );
		}

		return $mollie_customer->get_id();
	}
}
