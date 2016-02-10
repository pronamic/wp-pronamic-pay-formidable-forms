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

		// @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFormActionsController.php#L299-L308
		// @see https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L28-L29
		add_action( 'frm_trigger_pronamic_pay_create_action', array( $this, 'create_action' ), 10, 3 );
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

		return $actions;
	}

	/**
	 * Create action.
	 *
	 * @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFormActionsController.php#L299-L308
	 * @see https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L186-L193
	 */
	public function create_action( $action, $entry, $form ) {
		// save config ID in object var for use building redirect url
		// @see https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L264-L266
		$this->config_id = $action->post_content['pronamic_pay_config_id'];

		// @see https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L268-L269
		// @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmEntry.php#L698-L711
		add_action( 'frm_after_create_entry', array( $this, 'redirect_for_payment' ), 50, 2 );
	}

	/**
	 * Redirect for payment.
	 *
	 * @see https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L274-L311
	 */
	public function redirect_for_payment( $entry_id, $form_id ) {
		$gateway = Pronamic_WP_Pay_Plugin::get_gateway( $this->config_id );

		if ( $gateway ) {
			$data = new Pronamic_WP_Pay_Extensions_FormidableForms_PaymentData( $entry_id, $form_id );

			$payment = Pronamic_WP_Pay_Plugin::start( $config_id, $gateway, $data );

			$error = $gateway->get_error();

			if ( ! is_wp_error( $error ) ) {
				// Redirect
				$gateway->redirect( $payment );
			}
		}
	}
}
