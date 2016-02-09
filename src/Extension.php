<?php

/**
 * Title: Formidable Forms extension
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_FormidableForms_Extension {
	/**
	 * Slug
	 *
	 * @var string
	 */
	const SLUG = 'formidable-forms';

	/**
	 * Bootstrap
	 */
	public static function bootstrap() {
		new self();
	}

	/**
	 * Construct and initializes an Charitable extension object.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );

		// @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFormActionsController.php#L39-L57
		// @see https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentSettingsController.php#L11
		add_action( 'frm_registered_form_actions', array( $this, 'registered_form_actions' ) );
	}

	/**
	 * Initialize
	 */
	public function init() {

	}

	/**
	 * Registered form actions.
	 *
	 * @see https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentSettingsController.php#L125-L128
	 * @see https://github.com/wp-premium/formidable-paypal/blob/3.02/models/FrmPaymentAction.php
	 */
	public function registered_form_actions( $actions ) {
		$actions['pronamic_pay'] = 'Pronamic_WP_Pay_Extensions_FormidableForms_PaymentAction';
	}
}
