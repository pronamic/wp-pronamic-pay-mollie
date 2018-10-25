<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\Gateway as Core_Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Core\Recurring as Core_Recurring;
use Pronamic\WordPress\Pay\Core\Statuses as Core_Statuses;
use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Title: Mollie
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.4
 * @since   1.1.0
 */
class Gateway extends Core_Gateway {
	/**
	 * Slug of this gateway
	 *
	 * @var string
	 */
	const SLUG = 'mollie';

	/**
	 * Client.
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 * Meta key for customer ID.
	 *
	 * @var string
	 */
	private $meta_key_customer_id = '_pronamic_pay_mollie_customer_id';

	/**
	 * Constructs and initializes an Mollie gateway
	 *
	 * @param Config $config Config.
	 */
	public function __construct( Config $config ) {
		parent::__construct( $config );

		$this->supports = array(
			'payment_status_request',
			'recurring_direct_debit',
			'recurring_credit_card',
			'recurring',
		);

		$this->set_method( self::METHOD_HTTP_REDIRECT );
		$this->set_slug( self::SLUG );

		$this->client = new Client( $config->api_key );
		$this->client->set_mode( $config->mode );

		if ( self::MODE_TEST === $config->mode ) {
			$this->meta_key_customer_id = '_pronamic_pay_mollie_customer_id_test';
		}

		// Actions.
		add_action( 'pronamic_payment_status_update', array( $this, 'copy_customer_id_to_wp_user' ), 99, 1 );
	}

	/**
	 * Get issuers
	 *
	 * @see Pronamic_WP_Pay_Gateway::get_issuers()
	 */
	public function get_issuers() {
		$groups = array();

		$result = $this->client->get_issuers();

		if ( ! $result ) {
			$this->error = $this->client->get_error();

			return $groups;
		}

		$groups[] = array(
			'options' => $result,
		);

		return $groups;
	}

	/**
	 * Get available payment methods.
	 *
	 * @see Core_Gateway::get_available_payment_methods()
	 */
	public function get_available_payment_methods() {
		$payment_methods = array();

		// Set recurring types to get payment methods for.
		$recurring_types = array( null, Recurring::RECURRING, Recurring::FIRST );

		$results = array();

		foreach ( $recurring_types as $recurring_type ) {
			// Get active payment methods for Mollie account.
			$result = $this->client->get_payment_methods( $recurring_type );

			if ( ! $result ) {
				$this->error = $this->client->get_error();

				break;
			}

			if ( Recurring::FIRST === $recurring_type ) {
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

			$results = array_merge( $results, $result );
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
	 */
	public function get_supported_payment_methods() {
		return array(
			PaymentMethods::BANCONTACT,
			PaymentMethods::BANK_TRANSFER,
			PaymentMethods::BELFIUS,
			PaymentMethods::BITCOIN,
			PaymentMethods::CREDIT_CARD,
			PaymentMethods::DIRECT_DEBIT,
			PaymentMethods::DIRECT_DEBIT_BANCONTACT,
			PaymentMethods::DIRECT_DEBIT_IDEAL,
			PaymentMethods::DIRECT_DEBIT_SOFORT,
			PaymentMethods::IDEAL,
			PaymentMethods::KBC,
			PaymentMethods::PAYPAL,
			PaymentMethods::SOFORT,
		);
	}

	/**
	 * Get webhook URL for Mollie.
	 *
	 * @return string
	 */
	public function get_webhook_url() {
		$url = home_url( '/' );

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
		}

		$url = add_query_arg( 'mollie_webhook', '', $url );

		return $url;
	}

	/**
	 * Start
	 *
	 * @see Pronamic_WP_Pay_Gateway::start()
	 *
	 * @param Payment $payment Payment.
	 */
	public function start( Payment $payment ) {
		$request = new PaymentRequest();

		$request->amount       = $payment->get_total_amount()->get_amount();
		$request->description  = $payment->get_description();
		$request->redirect_url = $payment->get_return_url();
		$request->webhook_url  = $this->get_webhook_url();
		$request->locale       = LocaleHelper::transform( $payment->get_language() );

		// Customer ID.
		$customer_id = $this->get_customer_id_for_payment( $payment );

		if ( is_string( $customer_id ) && ! empty( $customer_id ) ) {
			$request->customer_id = $customer_id;
		}

		// Payment method.
		$payment_method = $payment->get_method();

		// Subscription.
		$subscription = $payment->get_subscription();

		if ( $subscription && PaymentMethods::is_recurring_method( $payment_method ) ) {
			$request->recurring_type = $payment->get_recurring() ? Recurring::RECURRING : Recurring::FIRST;

			if ( Recurring::FIRST === $request->recurring_type ) {
				$payment_method = PaymentMethods::get_first_payment_method( $payment_method );
			}

			if ( Recurring::RECURRING === $request->recurring_type ) {
				$payment->set_action_url( $payment->get_return_url() );
			}
		}

		// Leap of faith if the WordPress payment method could not transform to a Mollie method?
		$request->method = Methods::transform( $payment_method, $payment_method );

		// Issuer.
		if ( Methods::IDEAL === $request->method ) {
			// If payment method is iDEAL we set the user chosen issuer ID.
			$request->issuer = $payment->get_issuer();
		}

		// Create payment.
		$result = $this->client->create_payment( $request );

		if ( ! $result ) {
			$this->error = $this->client->get_error();

			return;
		}

		// Set transaction ID.
		if ( isset( $result->id ) ) {
			$payment->set_transaction_id( $result->id );
		}

		// Set status.
		if ( isset( $result->status ) ) {
			$payment->set_status( Statuses::transform( $result->status ) );
		}

		// Set action URL.
		if ( isset( $result->links, $result->links->paymentUrl ) ) {
			$payment->set_action_url( $result->links->paymentUrl );
		}
	}

	/**
	 * Update status of the specified payment
	 *
	 * @param Payment $payment Payment.
	 *
	 * @return void
	 */
	public function update_status( Payment $payment ) {
		$mollie_payment = $this->client->get_payment( $payment->get_transaction_id() );

		if ( ! $mollie_payment ) {
			$payment->set_status( Core_Statuses::FAILURE );

			$this->error = $this->client->get_error();

			return;
		}

		$payment->set_status( Statuses::transform( $mollie_payment->status ) );

		if ( isset( $mollie_payment->details ) ) {
			$details = $mollie_payment->details;

			/*
			 * @codingStandardsIgnoreStart
			 *
			 * Ignore coding standards because of sniff WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
			 */
			if ( isset( $details->consumerName ) ) {
				$payment->set_consumer_name( $details->consumerName );
			}

			if ( isset( $details->cardHolder ) ) {
				$payment->set_consumer_name( $details->cardHolder );
			}

			if ( isset( $details->consumerAccount ) ) {
				$payment->set_consumer_iban( $details->consumerAccount );
			}

			if ( isset( $details->consumerBic ) ) {
				$payment->set_consumer_bic( $details->consumerBic );
			}
			// @codingStandardsIgnoreEnd
		}
	}

	/**
	 * Get Mollie customer ID for payment.
	 *
	 * @param Payment $payment Payment.
	 *
	 * @return bool|string
	 */
	public function get_customer_id_for_payment( Payment $payment ) {
		// Get Mollie customer ID from user meta.
		$customer_id = $this->get_customer_id_by_wp_user_id( $payment->user_id );

		$subscription = $payment->get_subscription();

		// Get customer ID from subscription meta.
		if ( $subscription ) {
			$subscription_customer_id = $subscription->get_meta( 'mollie_customer_id' );

			// Try to get (legacy) customer ID from first payment.
			if ( empty( $subscription_customer_id ) && $subscription->get_first_payment() ) {
				$first_payment = $subscription->get_first_payment();

				$subscription_customer_id = $first_payment->get_meta( 'mollie_customer_id' );
			}

			if ( ! empty( $subscription_customer_id ) ) {
				$customer_id = $subscription_customer_id;
			}
		}

		// Create new customer if the customer does not exist at Mollie.
		if ( ( empty( $customer_id ) || ! $this->client->get_customer( $customer_id ) ) && Core_Recurring::RECURRING !== $payment->recurring_type ) {
			$customer_id = $this->client->create_customer( $payment->get_email(), $payment->get_customer_name() );

			$this->update_wp_user_customer_id( $payment->user_id, $customer_id );
		}

		// Store customer ID in subscription meta.
		if ( $subscription && empty( $subscription_customer_id ) && ! empty( $customer_id ) ) {
			$subscription->set_meta( 'mollie_customer_id', $customer_id );
		}

		// Copy customer ID from subscription to user meta.
		$this->copy_customer_id_to_wp_user( $payment );

		return $customer_id;
	}

	/**
	 * Get Mollie customer ID by the specified WordPress user ID.
	 *
	 * @param int $user_id WordPress user ID.
	 *
	 * @return string|bool
	 */
	public function get_customer_id_by_wp_user_id( $user_id ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		return get_user_meta( $user_id, $this->meta_key_customer_id, true );
	}

	/**
	 * Update Mollie customer ID meta for WordPress user.
	 *
	 * @param int    $user_id     WordPress user ID.
	 * @param string $customer_id Mollie Customer ID.
	 *
	 * @return bool
	 */
	private function update_wp_user_customer_id( $user_id, $customer_id ) {
		if ( empty( $user_id ) || is_bool( $user_id ) ) {
			return false;
		}

		if ( ! is_string( $customer_id ) || empty( $customer_id ) || 1 === strlen( $customer_id ) ) {
			return false;
		}

		update_user_meta( $user_id, $this->meta_key_customer_id, $customer_id );
	}

	/**
	 * Copy Mollie customer ID from subscription meta to WordPress user meta.
	 *
	 * @param Payment $payment Payment.
	 *
	 * @return void
	 */
	public function copy_customer_id_to_wp_user( Payment $payment ) {
		if ( $this->config->id !== $payment->config_id ) {
			return;
		}

		$subscription = $payment->get_subscription();

		if ( ! $subscription || empty( $subscription->user_id ) ) {
			return;
		}

		// Get customer ID from subscription meta.
		$customer_id = $subscription->get_meta( 'mollie_customer_id' );

		$user_customer_id = $this->get_customer_id_by_wp_user_id( $subscription->user_id );

		if ( empty( $user_customer_id ) ) {
			// Set customer ID as user meta.
			$this->update_wp_user_customer_id( $subscription->user_id, $customer_id );
		}
	}
}
