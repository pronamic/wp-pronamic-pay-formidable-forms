<?php

namespace Pronamic\WordPress\Pay\Extensions\FormidableForms;

use FrmEntry;
use FrmField;
use FrmFieldsHelper;
use Pronamic\WordPress\Pay\Payments\PaymentData as Pay_PaymentData;
use Pronamic\WordPress\Pay\Payments\Item;
use Pronamic\WordPress\Pay\Payments\Items;
use WP_Post;

/**
 * Title: WordPress pay Formidable payment data
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.1
 * @since   1.0.0
 */
class PaymentData extends Pay_PaymentData {
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

	/**
	 * Action
	 *
	 * @var WP_Post
	 */
	private $action;

	/**
	 * Constructs and initializes an Formidable Forms payment data object.
	 *
	 * @param string  $entry_id Entry ID.
	 * @param string  $form_id  Form ID.
	 * @param WP_Post $action   Form action.
	 */
	public function __construct( $entry_id, $form_id, $action ) {
		parent::__construct();

		$this->entry_id = $entry_id;
		$this->form_id  = $form_id;
		$this->action   = $action;

		// @link https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L285
		$this->entry = FrmEntry::getOne( $this->entry_id, true );
	}

	/**
	 * Get source indicator
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_source()
	 * @return string
	 */
	public function get_source() {
		return 'formidable-forms';
	}

	/**
	 * Get source ID.
	 *
	 * @return string
	 */
	public function get_source_id() {
		return $this->entry_id;
	}

	/**
	 * Get title.
	 *
	 * @return string
	 */
	public function get_title() {
		/* translators: %s order id */
		return sprintf( __( 'Formidable entry %s', 'pronamic_ideal' ), $this->get_order_id() );
	}

	/**
	 * Get description
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_description()
	 * @return string
	 */
	public function get_description() {
		// Description template.
		$description_template = $this->action->post_content['pronamic_pay_transaction_description'];

		/*
		 * Find shortcode.
		 *
		 * @link https://github.com/wp-premium/formidable/blob/2.0.22/classes/helpers/FrmFieldsHelper.php#L684-L696
		 */
		$shortcodes = FrmFieldsHelper::get_shortcodes( $description_template, $this->form_id );

		/*
		 * Replace shortcodes.
		 *
		 * @link https://github.com/wp-premium/formidable/blob/2.0.22/classes/helpers/FrmFieldsHelper.php#L715-L821
		 */
		$description = FrmFieldsHelper::replace_content_shortcodes( $description_template, $this->entry, $shortcodes );

		// Check if there was a replacement to make sure the description has a dynamic part.
		if ( $description_template === $description ) {
			$description .= $this->entry_id;
		}

		return $description;
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
	 * @return Items
	 */
	public function get_items() {
		// Items.
		$items = new Items();

		// Item
		// We only add one total item, because iDEAL cant work with negative price items (discount).
		$item = new Item();
		$item->set_number( $this->get_order_id() );
		$item->set_description( $this->get_description() );
		$item->set_price( $this->get_amount_from_field() );
		$item->set_quantity( 1 );

		$items->addItem( $item );

		return $items;
	}

	/**
	 * Get amount
	 *
	 * @link https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L345-L383
	 * @return float
	 */
	private function get_amount_from_field() {
		$amount = 0;

		$amount_field = $this->action->post_content['pronamic_pay_amount_field'];

		if ( ! empty( $amount_field ) && isset( $this->entry->metas[ $amount_field ] ) ) {
			$amount = $this->entry->metas[ $amount_field ];
		}

		return $amount;
	}

	/**
	 * Get currency
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_currency_alphabetic_code()
	 * @return string
	 */
	public function get_currency_alphabetic_code() {
		return 'EUR';
	}

	/**
	 * Get email.
	 *
	 * @return string
	 */
	public function get_email() {
		return '';
	}

	/**
	 * Get customer name.
	 *
	 * @return string
	 */
	public function get_customer_name() {
		return '';
	}

	/**
	 * Get address.
	 *
	 * @return null|string
	 */
	public function get_address() {
		return '';
	}

	/**
	 * Get city.
	 *
	 * @return null|string
	 */
	public function get_city() {
		return '';
	}

	/**
	 * Get ZIP.
	 *
	 * @return null|string
	 */
	public function get_zip() {
		return '';
	}

	/**
	 * Get normal return URL.
	 *
	 * @return string
	 */
	public function get_normal_return_url() {
		return '';
	}

	/**
	 * Get cancel URL.
	 *
	 * @return string
	 */
	public function get_cancel_url() {
		return '';
	}

	/**
	 * Get success URL.
	 *
	 * @return string
	 */
	public function get_success_url() {
		return '';
	}

	/**
	 * Get error URL.
	 *
	 * @return string
	 */
	public function get_error_url() {
		return '';
	}

	/**
	 * Get payment method.
	 *
	 * @return string|null
	 */
	public function get_payment_method() {
		$payment_method = null;

		$payment_method_field = $this->action->post_content['pronamic_pay_payment_method_field'];

		if ( ! empty( $payment_method_field ) && isset( $this->entry->metas[ $payment_method_field ] ) ) {
			$payment_method = $this->entry->metas[ $payment_method_field ];

			$replacements = array(
				'pronamic_pay_' => '',
				'pronamic_pay'  => '',
			);

			$payment_method = strtr( $payment_method, $replacements );

			if ( empty( $payment_method ) ) {
				$payment_method = null;
			}
		}

		return $payment_method;
	}

	/**
	 * Get issuer ID.
	 *
	 * @link https://github.com/wp-pay-extensions/gravityforms/blob/1.4.2/src/PaymentData.php#L336-L358
	 * @return string
	 */
	public function get_issuer_id() {
		$bank = null;

		$bank_fields = FrmField::get_all_types_in_form( $this->form_id, 'pronamic_bank_select' );

		$bank_field = reset( $bank_fields );

		if ( $bank_field && isset( $this->entry->metas[ $bank_field->id ] ) ) {
			$bank = $this->entry->metas[ $bank_field->id ];
		}

		return $bank;
	}
}
