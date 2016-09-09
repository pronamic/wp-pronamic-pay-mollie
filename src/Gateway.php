<?php

/**
 * Title: Mollie
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.6
 * @since 1.1.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_Gateway extends Pronamic_WP_Pay_Gateway {
	/**
	 * Slug of this gateway
	 *
	 * @var string
	 */
	const SLUG = 'mollie';

	/////////////////////////////////////////////////

	/**
	 * Constructs and initializes an Mollie gateway
	 *
	 * @param Pronamic_WP_Pay_Gateways_Mollie_Config $config
	 */
	public function __construct( Pronamic_WP_Pay_Gateways_Mollie_Config $config ) {
		parent::__construct( $config );

		$this->set_method( Pronamic_WP_Pay_Gateway::METHOD_HTTP_REDIRECT );
		$this->set_has_feedback( true );
		$this->set_amount_minimum( 1.20 );
		$this->set_slug( self::SLUG );

		$this->client = new Pronamic_WP_Pay_Gateways_Mollie_Client( $config->api_key );
		$this->client->set_mode( $config->mode );
	}

	/////////////////////////////////////////////////

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

	/////////////////////////////////////////////////

	public function get_issuer_field() {
		$meta_key = sprintf( '_pronamic_pay_mollie_customer_id_%s', $this->config->mode );

		if ( 1 ) {
			$customer_id = get_user_meta( get_current_user_id(), $meta_key, true );

			/* Mandates */
			$mandates = $this->client->get_mandates( $customer_id );

			if ( $mandates ) {
				echo '<h3>Mandates</h3>';

				foreach ( $mandates->data as $mandate ) {
					printf(
						'<code>%s</code> %s (%s)<br>',
						esc_html( $mandate->id ),
						esc_html( $mandate->method ),
						esc_html( $mandate->status )
					);
				}
			}

			/* Subscriptions */
			$subscriptions = $this->client->get_subscriptions( $customer_id );

			if ( $subscriptions ) {
				echo '<h3>Subscriptions</h3>';

				foreach ( $subscriptions->data as $subscription ) {
					printf(
						'<code>%s</code> %s (%s)<br>',
						esc_html( $subscription->id ),
						esc_html( $subscription->description ),
						esc_html( $subscription->status )
					);
				}
			}

			echo '<hr>';
		}

		if ( Pronamic_WP_Pay_PaymentMethods::IDEAL === $this->get_payment_method() ) {
			$customer_id = get_user_meta( get_current_user_id(), $meta_key, true );

			if ( $this->client->has_valid_mandate( $customer_id ) ) {
				return array();
			}

			return array(
				'id'       => 'pronamic_ideal_issuer_id',
				'name'     => 'pronamic_ideal_issuer_id',
				'label'    => __( 'Choose your bank', 'pronamic_ideal' ),
				'required' => true,
				'type'     => 'select',
				'choices'  => $this->get_transient_issuers(),
			);
		}
	}

	/////////////////////////////////////////////////

	/**
	 * Get payment methods
	 *
	 * @see Pronamic_WP_Pay_Gateway::get_payment_methods()
	 */
	public function get_payment_methods() {
		$groups = array();

		$result = $this->client->get_payment_methods();

		if ( ! $result ) {
			$this->error = $this->client->get_error();

			return $groups;
		}

		$groups[] = array(
			'options' => $result,
		);

		return $groups;
	}

	/////////////////////////////////////////////////

	/**
	 * Get supported payment methods
	 *
	 * @see Pronamic_WP_Pay_Gateway::get_supported_payment_methods()
	 */
	public function get_supported_payment_methods() {
		return array(
			Pronamic_WP_Pay_PaymentMethods::IDEAL,
			Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD,
			Pronamic_WP_Pay_PaymentMethods::BANCONTACT,
			Pronamic_WP_Pay_PaymentMethods::PAYPAL,
			Pronamic_WP_Pay_PaymentMethods::SOFORT,
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_IDEAL,
		);
	}

	/////////////////////////////////////////////////

	/**
	 * Get webhook URL for Mollie.
	 *
	 * @return string
	 */
	private function get_webhook_url() {
		$url = home_url( '/' );

		$host = parse_url( $url, PHP_URL_HOST );

		if ( 'localhost' === $host ) {
			// Mollie doesn't allow localhost
			return null;
		} elseif ( '.dev' === substr( $host, -4 ) ) {
			// Mollie doesn't allow the TLD .dev
			return null;
		}

		$url = add_query_arg( 'mollie_webhook', '', $url );

		return $url;
	}

	/////////////////////////////////////////////////

	/**
	 * Start
	 *
	 * @see Pronamic_WP_Pay_Gateway::start()
	 */
	public function start( Pronamic_Pay_Payment $payment ) {
		$request = new Pronamic_WP_Pay_Gateways_Mollie_PaymentRequest();

		$payment_method = $payment->get_method();

		$customer_id = $this->client->get_customer_id( $payment->get_customer_name() );

		$payment->set_meta( 'mollie_customer_id', $customer_id );

		$request->amount       = $payment->get_amount();
		$request->description  = $payment->get_description();
		$request->redirect_url = $payment->get_return_url();
		$request->webhook_url  = $this->get_webhook_url();
		$request->locale       = Pronamic_WP_Pay_Mollie_LocaleHelper::transform( $payment->get_language() );
		$request->customer_id  = $customer_id;
		$request->method       = Pronamic_WP_Pay_Mollie_Methods::transform( $payment_method );

		if ( empty( $request->method ) && ! empty( $payment_method ) ) {
			// Leap of faith if the WordPress payment method could not transform to a Mollie method?
			$request->method = $payment_method;
		}

		// Subscriptions
		$subscription = $payment->get_subscription();

		if ( $subscription && Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_IDEAL === $payment_method ) {
			if ( is_object( $this->client->get_error() ) ) {
				// Set error if customer could not be created
				$this->error = $this->client->get_error();

				// Prevent subscription payment from being created without customer
				return;
			}

			$request->recurring_type = Pronamic_WP_Pay_Mollie_Recurring::RECURRING;
			$request->method         = Pronamic_WP_Pay_Mollie_Methods::DIRECT_DEBIT;
			$has_payment             = $subscription->has_valid_payment();
			$user_interaction        = in_array( $payment->get_source(), array( 'gravityformsideal' ), true );

			if ( ! $has_payment || ( $user_interaction && ! $this->client->has_valid_mandate( $customer_id ) ) ) {
				// First payment or if user interaction is possible and no valid mandates are found

				if ( $this->client->has_valid_mandate( $customer_id ) ) {
					$this->create_subscription( $payment );
				} else {
					$request->recurring_type = Pronamic_WP_Pay_Mollie_Recurring::FIRST;
					$request->method         = Pronamic_WP_Pay_Mollie_Methods::IDEAL;
				}
			}

			if ( Pronamic_WP_Pay_Mollie_Recurring::RECURRING === $request->recurring_type ) {
				// Recurring payment
				$first = $subscription->get_first_payment();

				if ( '' !== $first->get_meta( 'mollie_customer_id' ) ) {
					$payment->set_meta( 'mollie_customer_id', $customer_id );
				}

				$payment->set_action_url( $payment->get_return_url() );

				if ( $has_payment ) {
					$payment->amount = $subscription->get_amount();

					if ( '' === $subscription->get_transaction_id() ) {
						$this->create_subscription( $payment );
					}

					return;
				}
			}
		}

		if ( Pronamic_WP_Pay_PaymentMethods::IDEAL === $payment_method ) {
			// If payment method is iDEAL we set the user chosen issuer ID.
			$request->issuer = $payment->get_issuer();
		}

		$result = $this->client->create_payment( $request );

		if ( ! $result ) {
			$this->error = $this->client->get_error();

			return;
		}

		$payment->set_transaction_id( $result->id );

		if ( '' === $payment->get_action_url() ) {
			$payment->set_action_url( $result->links->paymentUrl );
		}
	}

	/////////////////////////////////////////////////

	/**
	 * Update status of the specified payment
	 *
	 * @param Pronamic_Pay_Payment $payment
	 */
	public function payment( Pronamic_Pay_Payment $payment ) {
		if ( ! $payment->get_subscription() ) {
			return;
		}

		$subscription = $payment->get_subscription();

		if ( Pronamic_WP_Pay_Statuses::SUCCESS === $subscription->get_status() ) {
			Pronamic_WP_Pay_Plugin::update_payment( $payment, false );
		}
	}

	/////////////////////////////////////////////////

	/**
	 * Update status of the specified payment
	 *
	 * @param Pronamic_Pay_Payment $payment
	 */
	public function update_status( Pronamic_Pay_Payment $payment ) {
		$subscription = $payment->get_subscription();

		if ( $subscription && '' !== $subscription->get_transaction_id() ) {
			// Payment for existing Mollie subscription

			$first               = $subscription->get_first_payment();
			$customer_id         = $first->get_meta( 'mollie_customer_id' );
			$subscription_id     = $subscription->get_transaction_id();
			$mollie_subscription = $this->client->get_subscription( $customer_id, $subscription_id );

			if ( ! $mollie_subscription ) {
				$this->error = $this->client->get_error();

				$payment->set_status( Pronamic_WP_Pay_Statuses::FAILURE );
				$subscription->set_status( Pronamic_WP_Pay_Statuses::FAILURE );

				$this->update_subscription_payment_note( 'update_status', $subscription, $first, $this->error );

				return;
			}

			$status = Pronamic_WP_Pay_Mollie_Statuses::transform( $mollie_subscription->status );

			$payment->set_status( $status );
			$subscription->set_status( $status );

			$this->update_subscription_payment_note( 'update_status', $subscription, $first, (array) $mollie_subscription );

			if ( '' === $payment->get_transaction_id() ) {
				// Auto recurring payment, use Mollie subscription status for payment

				$status = Pronamic_WP_Pay_Statuses::FAILURE;

				if ( $this->client->has_valid_mandate( $customer_id ) ) {
					$status = Pronamic_WP_Pay_Mollie_Statuses::transform( $mollie_subscription->status );
				}

				$payment->set_status( $status );
				$subscription->set_status( $status );

				return;
			}
		}

		$mollie_payment = $this->client->get_payment( $payment->get_transaction_id() );

		$payment->add_note( print_r( $mollie_payment, true ) );

		if ( ! $mollie_payment ) {
			$this->error = $this->client->get_error();

			return;
		}

		if ( ! isset( $status ) ) {
			$status = Pronamic_WP_Pay_Mollie_Statuses::transform( $mollie_payment->status );

			$payment->set_status( $status );
		}

		if ( $subscription && '' === $subscription->get_transaction_id() ) {
			// First payment, use payment status for subscription too
			$subscription->set_status( $status );
		}

		if ( isset( $mollie_payment->details ) ) {
			$details = $mollie_payment->details;

			if ( isset( $details->consumerName ) ) {
				$payment->set_consumer_name( $details->consumerName );
			}

			if ( isset( $details->consumerAccount ) ) {
				$payment->set_consumer_iban( $details->consumerAccount );
			}
		}
	}

	/////////////////////////////////////////////////

	/**
	 * Create subscription.
	 *
	 * @param Pronamic_Pay_Payment $payment
	 */
	public function create_subscription( Pronamic_Pay_Payment $payment ) {
		$subscription = $payment->get_subscription();

		$data = $this->client->create_subscription(
			$payment->meta['mollie_customer_id'], // customer_id
			$subscription->get_amount(), // amount
			$subscription->get_frequency(), // times
			sprintf( // interval
				'%s %s',
				$subscription->get_interval(),
				$subscription->get_interval_period()
			),
			$subscription->get_description(), // description
			$this->get_webhook_url()
		);

		$this->update_subscription_payment_note( 'create_subscription', $subscription, $payment, (array) $data );

		if ( is_wp_error( $this->client->get_error() ) ) {
			// Set error if subscription could not be created
			$this->error = $this->client->get_error();

			return false;
		}

		$subscription->set_transaction_id( $data->id );
		$subscription->set_status( Pronamic_WP_Pay_Mollie_Statuses::transform( $data->status ) );

		return $data;
	}

	/**
	 * Cancel subscription.
	 *
	 * @param Pronamic_Pay_Payment $payment
	 */
	public function cancel_subscription( Pronamic_Pay_Subscription $subscription ) {
		$payment = $subscription->get_first_payment();

		if ( '' === $subscription->get_transaction_id() ) {
			$status = Pronamic_WP_Pay_Statuses::CANCELLED;
		}

		if ( '' !== $subscription->get_transaction_id() ) {
			$response = $this->client->cancel_subscription(
				$payment->get_meta( 'mollie_customer_id' ),
				$subscription->get_transaction_id()
			);

			if ( ! $response ) {
				$this->error = $this->client->get_error();

				return;
			}

			$status = Pronamic_WP_Pay_Mollie_Statuses::transform( $response->status );

			$this->update_subscription_payment_note( 'cancel_subscription', $subscription, $payment, (array) $response );
		}

		$subscription->set_status( $status );

		pronamic_wp_pay_update_subscription( $subscription );
	}

	/**
	 * Update subscription payment note.
	 *
	 * @param Pronamic_Pay_Payment $payment
	 * @param array $data
	 */
	private function update_subscription_payment_note( $src, Pronamic_Pay_Subscription $subscription, Pronamic_Pay_Payment $payment, $data ) {
		$note = '<pre>' . $src . ' - ';

		if ( is_wp_error( $data ) ) {
			$note .= print_r( $data, true );
			$note .= '</pre>';

			$payment->add_note( $note );
			$subscription->add_note( $note );

			return;
		}

		$labels = array(
			'resource'          => __( 'Resource', 'pronamic_ideal' ),
			'id'                => __( 'Subscription ID', 'pronamic_ideal' ),
			'customerId'        => __( 'Customer ID', 'pronamic_ideal' ),
			'mode'              => __( 'Mode', 'pronamic_ideal' ),
			'createdDatetime'   => __( 'Created date', 'pronamic_ideal' ),
			'status'            => __( 'Status', 'pronamic_ideal' ),
			'amount'            => __( 'Amount', 'pronamic_ideal' ),
			'times'             => __( 'Times', 'pronamic_ideal' ),
			'interval'          => __( 'Interval', 'pronamic_ideal' ),
			'description'       => __( 'Description', 'pronamic_ideal' ),
			'method'            => __( 'Method', 'pronamic_ideal' ),
			'cancelledDatetime' => __( 'Cancelled date', 'pronamic_ideal' ),
		);

		$note .= __( 'Mollie subscription data in response message:', 'pronamic_ideal' );
		$note .= '</p>';

		$note .= '<dl>';

		foreach ( $labels as $key => $label ) {
			if ( isset( $data[ $key ] ) && '' !== $data[ $key ] ) {
				$note .= sprintf( '<dt>%s</dt>', esc_html( $label ) );
				$note .= sprintf( '<dd>%s</dd>', esc_html( $data[ $key ] ) );
			}
		}

		$note .= '</dl>';

		$payment->add_note( $note );
		$subscription->add_note( $note );
	}
}
