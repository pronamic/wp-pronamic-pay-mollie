<?php

/**
 * Title: Mollie payment request
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_PaymentRequest {
	public $amount;

	public $description;

	public $method;

	public $redirect_url;

	public $meta_data;

	public $locale;

	public $webhook_url;

	public $issuer;

	public $customerId;

	/////////////////////////////////////////////////

	public function __construct() {

	}

	/////////////////////////////////////////////////

	public function get_array() {
		$array = array(
			'amount'      => number_format( $this->amount, 2, '.', '' ),
			'description' => $this->description,
			'method'      => $this->method,
			'redirectUrl' => $this->redirect_url,
			'metadata'    => $this->meta_data,
			'locale'      => $this->locale,
			'webhookUrl'  => $this->webhook_url,
			'issuer'      => $this->issuer,
			'customerId'  => $this->customerId,
		);

		/*
		 * Array filter will remove values NULL, FALSE and empty strings ('')
		 */
		$array = array_filter( $array );

		return $array;
	}
}
