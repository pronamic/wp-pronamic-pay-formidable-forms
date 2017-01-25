<?php

/**
 * Title: Formidable Forms payment action
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_FormidableForms_PaymentAction extends FrmFormAction {
	/**
	 * Slug
	 *
	 * @var string
	 */
	const SLUG = 'pronamic_pay';

	/**
	 * Constructs and initializes an Formidable payment action.
	 *
	 * @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmFormAction.php#L58-L94
	 * @see https://github.com/wp-premium/formidable-paypal/blob/3.02/models/FrmPaymentAction.php
	 */
	public function __construct() {
		parent::__construct( self::SLUG, __( 'Pronamic Pay', 'pronamic_ideal' ), array(
			// @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/views/frm-form-actions/form_action.php#L14
			'classes'   => 'pronamic-pay-formidable-icon',
			'active'    => true,
			'event'     => array( 'create' ),
			'priority'  => 9, // trigger before emails are sent so they can be stopped
			'limit'     => 99,
		) );
	}

	/**
	 * Form.
	 *
	 * @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmFormAction.php#L31-L39
	 * @see https://github.com/wp-premium/formidable-paypal/blob/3.02/models/FrmPaymentAction.php#L37-L42
	 */
	public function form( $instance, $args = array() ) {
		$form_fields = $this->get_field_options( $args['form']->id );

		include dirname( __FILE__ ) . '/../views/payment-settings.php';
	}

	/**
	 * Get field options.
	 *
	 * @see https://github.com/wp-premium/formidable-paypal/blob/3.02/models/FrmPaymentAction.php#L37-L42
	 * @param int $form_id
	 * @return array
	 */
	private function get_field_options( $form_id ) {
		$form_fields = FrmField::getAll( array(
			'fi.form_id'  => absint( $form_id ),
			'fi.type not' => array( 'divider', 'end_divider', 'html', 'break', 'captcha', 'rte', 'form' ),
		), 'field_order' );

		return $form_fields;
	}
}
