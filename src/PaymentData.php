<?php

/**
 * Title: WordPress pay Formidable payment data
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_FormidableForms_PaymentData extends Pronamic_WP_Pay_PaymentData {
	/**
	 * Entry ID
	 *
	 * @var string
	 */
	private $entry_id;

	/**
	 * Form ID
	 *
	 * @var string
	 */
	private $form_id;

	//////////////////////////////////////////////////

	/**
	 * Constructs and initializes an Formidable Forms payment data object.
	 *
	 * @param mixed $processor
	 */
	public function __construct( $entry_id, $form_id ) {
		parent::__construct();

		$this->entry_id = $entry_id;
		$this->form_id  = $form_id;
	}

	//////////////////////////////////////////////////

	/**
	 * Get source indicator
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_source()
	 * @return string
	 */
	public function get_source() {
		return 'formidable-forms';
	}

	public function get_source_id() {
		return $this->entry_id;
	}

	//////////////////////////////////////////////////

	public function get_title() {
		return sprintf( __( 'Formidable Forms entry %s', 'pronamic_ideal' ), $this->get_order_id() );
	}

	/**
	 * Get description
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_description()
	 * @return string
	 */
	public function get_description() {
		return sprintf( __( 'Formidable Forms entry %s', 'pronamic_ideal' ), $this->get_order_id() );
	}

	/**
	 * Get order ID
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_order_id()
	 * @return string
	 */
	public function get_order_id() {
		return $this->entry_id;
	}

	/**
	 * Get items
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_items()
	 * @return Pronamic_IDeal_Items
	 */
	public function get_items() {
		// Items
		$items = new Pronamic_IDeal_Items();

		// Item
		// We only add one total item, because iDEAL cant work with negative price items (discount)
		$item = new Pronamic_IDeal_Item();
		$item->setNumber( $this->get_order_id() );
		$item->setDescription( $this->get_description() );
		$item->setPrice( 50 );
		$item->setQuantity( 1 );

		$items->addItem( $item );

		return $items;
	}

	//////////////////////////////////////////////////
	// Currency
	//////////////////////////////////////////////////

	/**
	 * Get currency
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_currency_alphabetic_code()
	 * @return string
	 */
	public function get_currency_alphabetic_code() {
		return 'EUR';
	}

	//////////////////////////////////////////////////
	// Customer
	//////////////////////////////////////////////////

	public function get_email() {
		return '';
	}

	public function getCustomerName() {
		return '';
	}

	public function getOwnerAddress() {
		return '';
	}

	public function getOwnerCity() {
		return '';
	}

	public function getOwnerZip() {
		return '';
	}

	//////////////////////////////////////////////////
	// URL's
	//////////////////////////////////////////////////

	/**
	 * Get normal return URL.
	 *
	 * @return string
	 */
	public function get_normal_return_url() {
		return '';
	}

	public function get_cancel_url() {
		return '';
	}

	public function get_success_url() {
		return '';
	}

	public function get_error_url() {
		return '';
	}
}
