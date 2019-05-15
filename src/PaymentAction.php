<?php

namespace Pronamic\WordPress\Pay\Extensions\FormidableForms;

use FrmField;
use FrmFormAction;

/**
 * Title: Formidable Forms payment action
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class PaymentAction extends FrmFormAction {
	/**
	 * Slug
	 *
	 * @var string
	 */
	const SLUG = 'pronamic_pay';

	/**
	 * Constructs and initializes an Formidable payment action.
	 *
	 * @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmFormAction.php#L58-L94
	 * @link https://github.com/wp-premium/formidable-paypal/blob/3.02/models/FrmPaymentAction.php
	 */
	public function __construct() {
		parent::__construct(
			self::SLUG,
			__( 'Pronamic Pay', 'pronamic_ideal' ),
			array(
				// @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/views/frm-form-actions/form_action.php#L14
				'classes'  => 'pronamic-pay-formidable-icon',
				'active'   => true,
				'event'    => array( 'create' ),
				'priority' => 9, // trigger before emails are sent so they can be stopped.
				'limit'    => 99,
			)
		);
	}

	/**
	 * Form.
	 *
	 * @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmFormAction.php#L31-L39
	 * @link https://github.com/wp-premium/formidable-paypal/blob/3.02/models/FrmPaymentAction.php#L37-L42
	 *
	 * @param array $instance Current settings.
	 * @param array $args     Arguments.
	 */
	public function form( $instance, $args = array() ) {
		$form_fields = $this->get_field_options( $args['form']->id );

		include dirname( __FILE__ ) . '/../views/payment-settings.php';
	}

	/**
	 * Get field options.
	 *
	 * @link https://github.com/wp-premium/formidable-paypal/blob/3.02/models/FrmPaymentAction.php#L37-L42
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return array
	 */
	private function get_field_options( $form_id ) {
		$form_fields = FrmField::getAll(
			array(
				'fi.form_id'  => absint( $form_id ),
				'fi.type not' => array( 'divider', 'end_divider', 'html', 'break', 'captcha', 'rte', 'form' ),
			),
			'field_order'
		);

		return $form_fields;
	}
}
