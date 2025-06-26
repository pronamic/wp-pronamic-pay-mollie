<?php
/**
 * Mollie gateway.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Banks\BankAccountDetails;
use Pronamic\WordPress\Pay\Banks\BankTransferDetails;
use Pronamic\WordPress\Pay\Core\Gateway as Core_Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethod;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Core\PaymentMethodsCollection;
use Pronamic\WordPress\Pay\Fields\TextField;
use Pronamic\WordPress\Pay\Payments\FailureReason;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Refunds\Refund;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus;
use Pronamic\WordPress\Mollie\AmountTransformer;
use Pronamic\WordPress\Mollie\Client;
use Pronamic\WordPress\Mollie\Customer;
use Pronamic\WordPress\Mollie\Error;
use Pronamic\WordPress\Mollie\Methods;
use Pronamic\WordPress\Mollie\Payment as MolliePayment;
use Pronamic\WordPress\Mollie\PaymentRequest;
use Pronamic\WordPress\Mollie\Profile;
use Pronamic\WordPress\Mollie\RefundRequest;

/**
 * Gateway class
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
	 * Constructs and initializes a Mollie gateway
	 *
	 * @param Config $config Config.
	 */
	public function __construct( Config $config ) {
		parent::__construct();

		$this->config = $config;

		$this->set_method( self::METHOD_HTTP_REDIRECT );

		// Supported features.
		$this->supports = [
			'payment_status_request',
			'recurring',
			'refunds',
			'webhook',
			'webhook_log',
			'webhook_no_config',
		];

		// Client.
		$this->client = new Client( (string) $config->api_key );

		// Data Stores.
		$this->profile_data_store  = new ProfileDataStore();
		$this->customer_data_store = new CustomerDataStore();

		// Actions.
		add_action( 'pronamic_payment_status_update', $this->copy_customer_id_to_wp_user( ... ), 99, 1 );

		// Fields.
		$field_consumer_name = new TextField( 'pronamic_pay_consumer_bank_details_name' );
		$field_consumer_name->set_label( __( 'Account holder name', 'pronamic_ideal' ) );
		$field_consumer_name->set_required( true );
		$field_consumer_name->meta_key = 'consumer_bank_details_name';

		$field_consumer_iban = new TextField( 'pronamic_pay_consumer_bank_details_iban' );
		$field_consumer_iban->set_label( __( 'Account number (IBAN)', 'pronamic_ideal' ) );
		$field_consumer_iban->set_required( true );
		$field_consumer_iban->meta_key = 'consumer_bank_details_iban';

		$field_mollie_card           = new CardField( 'pronamic_pay_mollie_card_token', $this );
		$field_mollie_card->meta_key = 'mollie_card_token';

		// Apple Pay.
		$payment_method_apple_pay = new PaymentMethod( PaymentMethods::APPLE_PAY );
		$payment_method_apple_pay->add_support( 'recurring' );

		$this->register_payment_method( $payment_method_apple_pay );

		// Bancontact.
		$payment_method_bancontact = new PaymentMethod( PaymentMethods::BANCONTACT );
		$payment_method_bancontact->add_support( 'recurring' );

		$this->register_payment_method( $payment_method_bancontact );

		// Bank transfer.
		$this->register_payment_method( new PaymentMethod( PaymentMethods::BANK_TRANSFER ) );

		// Belfius.
		$payment_method_belfius = new PaymentMethod( PaymentMethods::BELFIUS );
		$payment_method_belfius->add_support( 'recurring' );

		$this->register_payment_method( $payment_method_belfius );

		// Billie.
		$this->register_payment_method( new PaymentMethod( PaymentMethods::BILLIE ) );

		// Card..
		$payment_method_card = new PaymentMethod( PaymentMethods::CARD );
		$payment_method_card->add_support( 'recurring' );
		$payment_method_card->add_field( $field_mollie_card );

		$this->register_payment_method( $payment_method_card );

		// Payment method credit card.
		$payment_method_credit_card = new PaymentMethod( PaymentMethods::CREDIT_CARD );
		$payment_method_credit_card->add_support( 'recurring' );
		$payment_method_credit_card->add_field( $field_mollie_card );

		$this->register_payment_method( $payment_method_credit_card );

		// Payment method direct debit.
		$payment_method_direct_debit = new PaymentMethod( PaymentMethods::DIRECT_DEBIT );
		$payment_method_direct_debit->add_support( 'recurring' );
		$payment_method_direct_debit->add_field( $field_consumer_name );
		$payment_method_direct_debit->add_field( $field_consumer_iban );

		$this->register_payment_method( $payment_method_direct_debit );

		// Payment method direct debit and Bancontact.
		$payment_method_direct_debit_bancontact = new PaymentMethod( PaymentMethods::DIRECT_DEBIT_BANCONTACT );
		$payment_method_direct_debit_bancontact->add_support( 'recurring' );

		$this->register_payment_method( $payment_method_direct_debit_bancontact );

		// Payment method direct debit and iDEAL.
		$payment_method_direct_debit_ideal = new PaymentMethod( PaymentMethods::DIRECT_DEBIT_IDEAL );
		$payment_method_direct_debit_ideal->add_support( 'recurring' );

		$this->register_payment_method( $payment_method_direct_debit_ideal );

		// Payment method direct debit and SOFORT.
		$payment_method_direct_debit_sofort = new PaymentMethod( PaymentMethods::DIRECT_DEBIT_SOFORT );
		$payment_method_direct_debit_sofort->add_support( 'recurring' );

		$this->register_payment_method( $payment_method_direct_debit_sofort );

		// EPS.
		$payment_method_eps = new PaymentMethod( PaymentMethods::EPS );
		$payment_method_eps->add_support( 'recurring' );

		$this->register_payment_method( $payment_method_eps );

		// Giropay.
		$payment_method_giropay = new PaymentMethod( PaymentMethods::GIROPAY );
		$payment_method_giropay->add_support( 'recurring' );

		$this->register_payment_method( $payment_method_giropay );

		// Payment method iDEAL.
		$payment_method_ideal = new PaymentMethod( PaymentMethods::IDEAL );
		$payment_method_ideal->add_support( 'recurring' );

		$this->register_payment_method( $payment_method_ideal );

		// IN3.
		$this->register_payment_method( new PaymentMethod( PaymentMethods::IN3 ) );

		// KBC.
		$payment_method_kbc = new PaymentMethod( PaymentMethods::KBC );
		$payment_method_kbc->add_support( 'recurring' );

		$this->register_payment_method( $payment_method_kbc );

		// Klarna.
		$this->register_payment_method( new PaymentMethod( PaymentMethods::KLARNA ) );
		$this->register_payment_method( new PaymentMethod( PaymentMethods::KLARNA_PAY_LATER ) );
		$this->register_payment_method( new PaymentMethod( PaymentMethods::KLARNA_PAY_NOW ) );
		$this->register_payment_method( new PaymentMethod( PaymentMethods::KLARNA_PAY_OVER_TIME ) );

		// Pay by Bank.
		$payment_method_pay_by_bank = new PaymentMethod( PaymentMethods::PAY_BY_BANK );
		$payment_method_pay_by_bank->add_support( 'recurring' );

		$this->register_payment_method( $payment_method_pay_by_bank );

		// PayPal.
		$payment_method_paypal = new PaymentMethod( PaymentMethods::PAYPAL );
		$payment_method_paypal->add_support( 'recurring' );

		$this->register_payment_method( $payment_method_paypal );

		// Przelewy24.
		$this->register_payment_method( new PaymentMethod( PaymentMethods::PRZELEWY24 ) );

		// Sofort.
		$payment_method_sofort = new PaymentMethod( PaymentMethods::SOFORT );
		$payment_method_sofort->add_support( 'recurring' );

		$this->register_payment_method( $payment_method_sofort );

		// TWINT.
		$this->register_payment_method( new PaymentMethod( PaymentMethods::TWINT ) );
	}

	/**
	 * Get profile ID.
	 *
	 * @return string|null
	 */
	public function get_profile_id() {
		$profile_id = $this->config->profile_id;

		if ( '' === $profile_id ) {
			$current_profile = $this->client->get_current_profile();

			$profile = Profile::from_object( $current_profile );

			$profile_id = $profile->get_id();

			\update_post_meta( $this->config->id, '_pronamic_gateway_mollie_profile_id', $profile_id );

			$this->config->profile_id = $profile_id;
		}

		return $profile_id;
	}

	/**
	 * Get payment methods.
	 *
	 * @param array<string, string> $args Query arguments.
	 * @return PaymentMethodsCollection
	 */
	public function get_payment_methods( array $args = [] ): PaymentMethodsCollection {
		try {
			$this->maybe_enrich_payment_methods();
		} catch ( \Exception ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch -- No problem.
			// No problem.
		}

		return parent::get_payment_methods( $args );
	}

	/**
	 * Maybe enrich payment methods.
	 *
	 * @return void
	 */
	private function maybe_enrich_payment_methods() {
		$cache_key = 'pronamic_pay_mollie_payment_methods_' . \md5( (string) \wp_json_encode( $this->config ) );

		$mollie_payment_methods = \get_transient( $cache_key );

		if ( false === $mollie_payment_methods ) {
			$mollie_payment_methods = $this->client->get_all_payment_methods();

			\set_transient( $cache_key, $mollie_payment_methods, \DAY_IN_SECONDS );
		}

		// All payment methods are inactive by default.
		foreach ( $this->payment_methods as $payment_method ) {
			$payment_method->set_status( 'inactive' );
		}

		$method_transformer = new MethodTransformer();

		foreach ( $mollie_payment_methods->_embedded->methods as $mollie_payment_method ) {
			$core_payment_method_ids = $method_transformer->from_mollie_to_pronamic( $mollie_payment_method->id );

			foreach ( $core_payment_method_ids as $core_payment_method_id ) {
				$core_payment_method = $this->get_payment_method( $core_payment_method_id );

				if ( null !== $core_payment_method ) {
					switch ( $mollie_payment_method->status ) {
						case 'activated':
							$core_payment_method->set_status( 'active' );

							break;
						case 'pending-boarding':
						case 'pending-review':
						case 'pending-external':
							$core_payment_method->set_status( $this->config->is_test_mode() ? 'active' : 'inactive' );

							break;
						case 'rejected':
						case null:
							$core_payment_method->set_status( 'inactive' );

							break;
					}
				}
			}
		}

		// Update `Direct Debit (mandate via ...)` payment method statuses.
		$payment_method_direct_debit = $this->get_payment_method( PaymentMethods::DIRECT_DEBIT );

		if ( null !== $payment_method_direct_debit ) {
			$map = [
				PaymentMethods::BANCONTACT => PaymentMethods::DIRECT_DEBIT_BANCONTACT,
				PaymentMethods::IDEAL      => PaymentMethods::DIRECT_DEBIT_IDEAL,
				PaymentMethods::SOFORT     => PaymentMethods::DIRECT_DEBIT_SOFORT,
			];

			foreach ( $map as $a => $b ) {
				$method_a = $this->get_payment_method( $a );
				$method_b = $this->get_payment_method( $b );

				if ( null === $method_a || null === $method_b ) {
					continue;
				}

				switch ( $payment_method_direct_debit->get_status() ) {
					case 'active':
						$method_b->set_status( $method_a->get_status() );

						break;
					case 'inactive':
						$method_b->set_status( 'inactive' );

						break;
				}
			}
		}
	}

	/**
	 * Get webhook URL for Mollie.
	 *
	 * @param Payment $payment Payment.
	 * @return string|null
	 * @throws \Exception Throws exception when resource to use for payment is unknown.
	 */
	public function get_webhook_url( Payment $payment ) {
		$path = \strtr(
			'<namespace>/payments/webhook/<payment_id>',
			[
				'<namespace>'  => Integration::REST_ROUTE_NAMESPACE,
				'<payment_id>' => $payment->get_id(),
			]
		);

		$url = \rest_url( $path );

		$host = \wp_parse_url( $url, PHP_URL_HOST );

		if ( ! \is_string( $host ) ) {
			// Parsing failure.
			$host = '';
		}

		if ( 'localhost' === $host ) {
			// Mollie doesn't allow localhost.
			return null;
		} elseif ( \str_ends_with( $host, '.dev' ) ) {
			// Mollie doesn't allow the .dev TLD.
			return null;
		} elseif ( \str_ends_with( $host, '.local' ) ) {
			// Mollie doesn't allow the .local TLD.
			return null;
		} elseif ( \str_ends_with( $host, '.test' ) ) {
			// Mollie doesn't allow the .test TLD.
			return null;
		}

		return $url;
	}

	/**
	 * Start
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 * @throws Error Throws exception on error creating Mollie customer for payment.
	 * @see Core_Gateway::start()
	 */
	public function start( Payment $payment ) {
		$request = $this->get_payment_request( $payment );

		// Create payment.
		$attempt = (int) $payment->get_meta( 'mollie_create_payment_attempt' );
		$attempt = empty( $attempt ) ? 1 : $attempt + 1;

		$payment->set_meta( 'mollie_create_payment_attempt', $attempt );

		try {
			$mollie_payment = $this->client->create_payment( $request );

			$payment->delete_meta( 'mollie_create_payment_attempt' );
		} catch ( Error $error ) {
			if ( 'recurring' !== $request->sequence_type ) {
				throw $error;
			}

			if ( null === $request->mandate_id ) {
				throw $error;
			}

			/**
			 * Only schedule retry for specific status codes.
			 *
			 * @link https://docs.mollie.com/overview/handling-errors
			 */
			if ( ! \in_array( $error->get_status(), [ 429, 502, 503 ], true ) ) {
				throw $error;
			}

			\as_schedule_single_action(
				\time() + $this->get_retry_seconds( $attempt ),
				'pronamic_pay_mollie_payment_start',
				[
					'payment_id' => $payment->get_id(),
				],
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

		$this->update_payment_from_mollie_payment( $payment, $mollie_payment );
	}

	/**
	 * Get Mollie payment request for the specified payment.
	 *
	 * @param Payment $payment Payment.
	 * @return PaymentRequest
	 */
	private function get_payment_request( Payment $payment ) {
		$description = (string) $payment->get_description();

		/**
		 * Filters the Mollie payment description.
		 *
		 * The maximum length of the description field differs per payment
		 * method, with the absolute maximum being 255 characters.
		 *
		 * @link  https://docs.mollie.com/reference/v2/payments-api/create-payment#parameters
		 * @since 3.0.1
		 * @param string  $description Description.
		 * @param Payment $payment     Payment.
		 */
		$description = \apply_filters( 'pronamic_pay_mollie_payment_description', $description, $payment );

		$amount_transformer = new AmountTransformer();

		$request = new PaymentRequest(
			$amount_transformer->transform_wp_to_mollie( $payment->get_total_amount() ),
			$description
		);

		$request->redirect_url = $payment->get_return_url();
		$request->webhook_url  = $this->get_webhook_url( $payment );

		// Locale.
		$customer = $payment->get_customer();

		if ( null !== $customer ) {
			$locale_transformer = new LocaleTransformer();

			$request->locale = $locale_transformer->transform_wp_to_mollie( $customer->get_locale() );
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

		$method_transformer = new MethodTransformer();

		$request->method = $method_transformer->transform_wp_to_mollie( $payment_method, $payment_method );

		/**
		 * Sequence type.
		 *
		 * Recurring payments are created through the Payments API by providing a `sequenceType`.
		 */
		$subscriptions = $payment->get_subscriptions();

		$sequence_type = $payment->get_meta( 'mollie_sequence_type' );

		if (
			'' !== $sequence_type
				&&
			(
				\count( $subscriptions ) > 0
					||
				\in_array(
					$payment_method,
					[
						PaymentMethods::DIRECT_DEBIT_BANCONTACT,
						PaymentMethods::DIRECT_DEBIT_IDEAL,
						PaymentMethods::DIRECT_DEBIT_SOFORT,
					],
					true
				)
			)
		) {
			$request->sequence_type = 'first';

			foreach ( $subscriptions as $subscription ) {
				$mandate_id = $subscription->get_meta( 'mollie_mandate_id' );

				if ( ! empty( $mandate_id ) ) {
					$request->mandate_id = $mandate_id;
				}
			}
		}

		if ( ! empty( $sequence_type ) ) {
			$request->sequence_type = $sequence_type;
		}

		if ( 'recurring' === $request->sequence_type ) {
			$request->method = null;
		}

		if ( 'first' === $request->sequence_type ) {
			$first_method = $payment_method;

			switch ( $payment_method ) {
				case PaymentMethods::DIRECT_DEBIT_BANCONTACT:
					$first_method = PaymentMethods::BANCONTACT;
					break;
				case PaymentMethods::DIRECT_DEBIT_IDEAL:
					$first_method = PaymentMethods::IDEAL;
					break;
				case PaymentMethods::DIRECT_DEBIT_SOFORT:
					$first_method = PaymentMethods::SOFORT;
					break;
			}

			$request->method = $method_transformer->transform_wp_to_mollie( $first_method, $first_method );
		}

		/**
		 * Addresses.
		 */
		$address_transformer = new AddressTransformer();

		$billing_address = $payment->get_billing_address();

		if ( null !== $billing_address ) {
			$request->billing_address = $address_transformer->transform_wp_to_mollie( $billing_address );
		}

		$shipping_address = $payment->get_shipping_address();

		if ( null !== $shipping_address ) {
			$request->shipping_address = $address_transformer->transform_wp_to_mollie( $shipping_address );
		}

		/**
		 * Lines.
		 */
		$lines_transformer = new LinesTransformer();

		$lines = $payment->get_lines();

		if ( null !== $lines ) {
			$request->lines = $lines_transformer->transform_wp_to_mollie( $lines );
		}

		/**
		 * Direct Debit.
		 */
		$this->process_direct_debit_mandate_from_bank_details( $payment, $request );

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
		 * @param mixed   $metadata Metadata.
		 * @param Payment $payment  Payment.
		 * @since 2.2.0
		 */
		$metadata = \apply_filters( 'pronamic_pay_mollie_payment_metadata', $metadata, $payment );

		$request->metadata = $metadata;

		// Card token.
		if ( Methods::CREDITCARD === $request->method ) {
			$card_token = $payment->get_meta( 'mollie_card_token' );

			if ( ! empty( $card_token ) ) {
				$request->card_token = $card_token;
			}
		}

		// Due date.
		if ( ! empty( $this->config->due_date_days ) ) {
			try {
				$due_date = new DateTime( sprintf( '+%s days', $this->config->due_date_days ) );
			} catch ( \Exception ) {
				$due_date = null;
			}

			$request->due_date = $due_date;
		}

		return $request;
	}

	/**
	 * Process direct debit mandate from bank details.
	 *
	 * Check if one-off SEPA Direct Debit can be used, otherwise short circuit payment.
	 *
	 * @param Payment        $payment Payment.
	 * @param PaymentRequest $request Request.
	 * @return void
	 */
	private function process_direct_debit_mandate_from_bank_details( Payment $payment, PaymentRequest $request ) {
		// Process only when method is direct debit.
		if ( Methods::DIRECT_DEBIT !== $request->method ) {
			return;
		}

		// Process only when customer is known.
		$customer_id = $request->customer_id;

		if ( null === $customer_id ) {
			return;
		}

		// Process only when mandate is unknown.
		if ( null !== $request->mandate_id ) {
			return;
		}

		// Process only when bank details are known.
		$consumer_bank_details = $payment->get_consumer_bank_details();

		if ( null === $consumer_bank_details ) {
			return;
		}

		$consumer_name = $consumer_bank_details->get_name();
		$consumer_iban = $consumer_bank_details->get_iban();

		$request->consumer_name    = $consumer_name;
		$request->consumer_account = $consumer_iban;

		// Check if one-off SEPA Direct Debit can be used, otherwise short circuit payment.
		// Find or create mandate.
		$mandate_id = $this->has_valid_mandate( $customer_id, PaymentMethods::DIRECT_DEBIT, $consumer_iban );

		if ( false === $mandate_id ) {
			$mandate = $this->client->create_mandate(
				$customer_id,
				[
					'method'          => Methods::DIRECT_DEBIT,
					'consumerName'    => (string) $consumer_name,
					'consumerAccount' => (string) $consumer_iban,
				]
			);

			$mandate_id = $mandate->get_id();
		}

		// Charge immediately on-demand.
		$request->sequence_type = 'recurring';
		$request->mandate_id    = (string) $mandate_id;
	}

	/**
	 * Is there a valid mandate for customer?
	 *
	 * @param string      $customer_id    Mollie customer ID.
	 * @param string|null $payment_method Payment method to find mandates for.
	 * @param string|null $search         Search.
	 *
	 * @return string|bool
	 * @throws \Exception Throws exception for mandates on failed request or invalid response.
	 */
	private function has_valid_mandate( $customer_id, $payment_method = null, $search = null ) {
		$mandates = $this->client->get_mandates( $customer_id );

		$method_transformer = new MethodTransformer();

		$mollie_method = $method_transformer->transform_wp_to_mollie( $payment_method );

		if ( ! isset( $mandates->_embedded ) ) {
			throw new \Exception( 'No embedded data in Mollie response.' );
		}

		foreach ( $mandates->_embedded->mandates as $mandate ) {
			if ( null !== $mollie_method && $mollie_method !== $mandate->method ) {
				continue;
			}

			// Search consumer account or card number.
			if ( null !== $search ) {
				switch ( $mollie_method ) {
					case Methods::DIRECT_DEBIT:
					case Methods::PAYPAL:
						if ( $search !== $mandate->details->consumerAccount ) {
							continue 2;
						}

						break;
					case Methods::CREDITCARD:
						if ( $search !== $mandate->details->cardNumber ) {
							continue 2;
						}

						break;
				}
			}

			if ( 'valid' === $mandate->status ) {
				return $mandate->id;
			}
		}

		return false;
	}

	/**
	 * Get retry seconds.
	 *
	 * @param int $attempt Number of attempts.
	 * @return int
	 */
	private function get_retry_seconds( $attempt ) {
		return match ( $attempt ) {
			1 => 5 * MINUTE_IN_SECONDS,
			2 => HOUR_IN_SECONDS,
			3 => 12 * HOUR_IN_SECONDS,
			default => DAY_IN_SECONDS,
		};
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
		$status_transformer = new StatusTransformer();

		$status = $status_transformer->transform_mollie_to_wp( $mollie_payment->get_status() );

		if ( null !== $status ) {
			$payment->set_status( $status );
		}

		/**
		 * Payment method.
		 */
		$method = $mollie_payment->get_method();

		if ( null !== $method ) {
			$method_transformer = new MethodTransformer();

			$payment_method = $method_transformer->transform_mollie_to_wp( $method );

			// Use wallet method as payment method.
			$mollie_payment_details = $mollie_payment->get_details();

			if ( null !== $mollie_payment_details && $mollie_payment_details->has_property( 'wallet' ) ) {
				$wallet_method = $method_transformer->transform_mollie_to_wp( $mollie_payment_details->get_property( 'wallet' ) );

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
				[
					'profile_id' => $profile_internal_id,
				],
				[
					'profile_id' => '%s',
				]
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

			if ( $mollie_payment_details->has_property( 'consumerName' ) ) {
				$consumer_bank_details->set_name( $mollie_payment_details->get_property( 'consumerName' ) );
			}

			if ( $mollie_payment_details->has_property( 'cardHolder' ) ) {
				$consumer_bank_details->set_name( $mollie_payment_details->get_property( 'cardHolder' ) );
			}

			if ( $mollie_payment_details->has_property( 'cardNumber' ) ) {
				// The last four digits of the card number.
				$consumer_bank_details->set_account_number( $mollie_payment_details->get_property( 'cardNumber' ) );
			}

			if ( $mollie_payment_details->has_property( 'cardCountryCode' ) ) {
				// The ISO 3166-1 alpha-2 country code of the country the card was issued in.
				$consumer_bank_details->set_country( $mollie_payment_details->get_property( 'cardCountryCode' ) );
			}

			if ( $mollie_payment_details->has_property( 'consumerAccount' ) ) {
				match ( $mollie_payment->get_method() ) {
					Methods::BELFIUS, Methods::DIRECT_DEBIT, Methods::IDEAL, Methods::KBC, Methods::SOFORT => $consumer_bank_details->set_iban( $mollie_payment_details->get_property( 'consumerAccount' ) ),
					default => $consumer_bank_details->set_account_number( $mollie_payment_details->get_property( 'consumerAccount' ) ),
				};
			}

			if ( $mollie_payment_details->has_property( 'consumerBic' ) ) {
				$consumer_bank_details->set_bic( $mollie_payment_details->get_property( 'consumerBic' ) );
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

			if ( $mollie_payment_details->has_property( 'bankName' ) ) {
				/**
				 * Set `bankName` as bank details name, as result "Stichting Mollie Payments"
				 * is not the name of a bank, but the account holder name.
				 */
				$bank_details->set_name( $mollie_payment_details->get_property( 'bankName' ) );
			}

			if ( $mollie_payment_details->has_property( 'bankAccount' ) ) {
				$bank_details->set_iban( $mollie_payment_details->get_property( 'bankAccount' ) );
			}

			if ( $mollie_payment_details->has_property( 'bankBic' ) ) {
				$bank_details->set_bic( $mollie_payment_details->get_property( 'bankBic' ) );
			}

			if ( $mollie_payment_details->has_property( 'transferReference' ) ) {
				$bank_transfer_recipient_details->set_reference( $mollie_payment_details->get_property( 'transferReference' ) );
			}

			$failure_reason = $payment->get_failure_reason();

			if ( null === $failure_reason ) {
				$failure_reason = new FailureReason();
			}

			// SEPA Direct Debit.
			if ( $mollie_payment_details->has_property( 'bankReasonCode' ) ) {
				$failure_reason->set_code( $mollie_payment_details->get_property( 'bankReasonCode' ) );
			}

			if ( $mollie_payment_details->has_property( 'bankReason' ) ) {
				$failure_reason->set_message( $mollie_payment_details->get_property( 'bankReason' ) );
			}

			// Credit card.
			if ( $mollie_payment_details->has_property( 'failureReason' ) ) {
				$failure_reason->set_code( $mollie_payment_details->get_property( 'failureReason' ) );
			}

			if ( $mollie_payment_details->has_property( 'failureMessage' ) ) {
				$failure_reason->set_message( $mollie_payment_details->get_property( 'failureMessage' ) );
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

		if (
			null === $payment->get_action_url()
			&&
			'' === $payment->get_meta( 'mollie_sequence_type' )
			&&
			PaymentMethods::DIRECT_DEBIT === $payment->get_payment_method()
		) {
			$payment->set_action_url( $payment->get_return_redirect_url() );
		}

		// Change payment state URL.
		if ( \property_exists( $links, 'changePaymentState' ) ) {
			$payment->set_meta( 'mollie_change_payment_state_url', $links->changePaymentState->href );
		}

		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.

		/**
		 * Chargebacks.
		 */
		$amount_charged_back = $mollie_payment->get_amount_charged_back();

		if ( null !== $amount_charged_back ) {
			$charged_back_amount = new Money( $amount_charged_back->value, $amount_charged_back->currency );

			$payment->set_charged_back_amount( $charged_back_amount->get_value() > 0 ? $charged_back_amount : null );
		}

		if ( $mollie_payment->has_chargebacks() ) {
			$mollie_chargebacks = $this->client->get_payment_chargebacks(
				$mollie_payment->get_id(),
				[ 'limit' => 1 ]
			);

			$mollie_chargeback = \reset( $mollie_chargebacks );

			if ( false !== $mollie_chargeback ) {
				$subscriptions = array_filter(
					$payment->get_subscriptions(),
					fn( $subscription ) => SubscriptionStatus::ACTIVE === $subscription->get_status()
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
		if ( $mollie_payment->has_refunds() ) {
			$refund_transformer = new RefundTransformer();

			$mollie_refunds = $this->client->get_payment_refunds(
				$mollie_payment->get_id(),
				[]
			);

			$map = [];

			foreach ( $payment->refunds as $refund ) {
				$map[ $refund->psp_id ] = $refund;
			}

			foreach ( $mollie_refunds as $mollie_refund ) {
				$id = $mollie_refund->get_id();

				if ( \array_key_exists( $id, $map ) ) {
					$pronamic_refund = $map[ $id ];

					$refund_transformer->update_mollie_to_pronamic( $mollie_refund, $pronamic_refund );
				} else {
					$payment->refunds[] = $refund_transformer->transform_mollie_to_pronamic( $mollie_refund, $payment );
				}
			}

			$amount_refunded = $mollie_payment->get_amount_refunded();

			if ( null !== $amount_refunded ) {
				$amount_transformer = new AmountTransformer();

				$payment->set_refunded_amount( $amount_transformer->transform_mollie_to_wp( $amount_refunded ) );
			}
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
		// Update mandate.
		$old_mandate_id = (string) $subscription->get_meta( 'mollie_mandate_id' );

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

		/*
		 * Update payment method.
		 *
		 * A mandate might not be available immediately after starting a payment. Therefore,
		 * we catch exceptions and rely on the payment method being updated during a later payment status update.
		 *
		 * @link https://github.com/pronamic/wp-pronamic-pay-mollie/issues/64
		 */
		try {
			$customer_id = (string) $subscription->get_meta( 'mollie_customer_id' );

			$mandate = $this->client->get_mandate( $mandate_id, $customer_id );

			if ( \is_object( $mandate ) ) {
				$method_transformer = new MethodTransformer();

				$old_method = $subscription->get_payment_method();
				$new_method = $payment_method;

				if ( null === $payment_method && \property_exists( $mandate, 'method' ) ) {
					$pronamic_methods = $method_transformer->from_mollie_to_pronamic( $mandate->method );

					$new_method = in_array( $old_method, $pronamic_methods, true ) ? $old_method : null;

					$first_pronamic_method = reset( $pronamic_methods );

					if ( null === $new_method && false !== $first_pronamic_method ) {
						$new_method = $first_pronamic_method;
					}
				}

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
			}
		} catch ( \Exception ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Nothing to do.
		}

		$subscription->save();
	}

	/**
	 * Create refund.
	 *
	 * @param Refund $refund Refund.
	 * @return void
	 * @throws \Exception Throws exception on unknown resource type.
	 */
	public function create_refund( Refund $refund ) {
		$payment = $refund->get_payment();

		$amount_transformer = new AmountTransformer();

		$amount = $amount_transformer->transform_wp_to_mollie( $refund->get_amount() );

		$request = new RefundRequest( $amount );

		// Metadata payment ID.
		$payment_id = $payment->get_id();

		if ( null !== $payment_id ) {
			$request->set_metadata(
				[
					'pronamic_payment_id' => $payment_id,
				]
			);
		}

		// Description.
		$description = $refund->get_description();

		if ( ! empty( $description ) ) {
			$request->set_description( $description );
		}

		$transaction_id = $payment->get_transaction_id();

		if ( null === $transaction_id ) {
			throw new \Exception( 'Unable to create payment refund without Mollie payment ID.' );
		}

		$mollie_refund = $this->client->create_refund( $transaction_id, $request );

		$refund->psp_id = $mollie_refund->get_id();
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
		$customer_ids = [];

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
			[
				'user_id' => $user_id,
			]
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
				$locale_transformer = new LocaleTransformer();

				$mollie_customer->set_locale( $locale_transformer->transform_wp_to_mollie( $locale ) );
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
			$customer_ids = [
				// Payment.
				$payment->get_meta( 'mollie_customer_id' ),

				// Subscription.
				$subscription->get_meta( 'mollie_customer_id' ),
			];

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
