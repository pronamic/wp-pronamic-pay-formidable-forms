<?php

/**
 * Title: Formidable Forms payment action
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_FormidableForms_PaymentAction extends FrmFormAction {
	/**
	 * Constructs and initializes an Formidable payment action.
	 *
	 * @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmFormAction.php#L58-L94
	 * @see https://github.com/wp-premium/formidable-paypal/blob/3.02/models/FrmPaymentAction.php
	 */
	public function __construct() {
		parent::__construct( 'pronamic_pay', __( 'Pronamic Pay', 'pronamic_ideal' ), array(
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
		include dirname( __FILE__ ) . '/../views/payment-settings.php';
	}
}
