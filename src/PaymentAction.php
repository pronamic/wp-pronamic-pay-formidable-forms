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
		parent::__construct( 'pronamic-pay', __( 'Pronamic Pay', 'pronamic_ideal' ), array(
			'classes'   => 'frm_paypal_icon frm_icon_font',
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
	 * @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmFormAction.php#L31-L39
	 * @see https://github.com/wp-premium/formidable-paypal/blob/3.02/views/settings/_payment_settings.php
	 */
	public function form( $instance, $args = array() ) {
		include dirname( __FILE__ ) . '/../views/payment-settings.php';
	}
}
