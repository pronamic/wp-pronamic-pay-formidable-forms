<?php

/**
 * Title: Formidable Forms extension
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.2
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
	 * Send e-mail now
	 *
	 * @var bool
	 *
	 * @since unreleased
	 */
	static $send_email_now = false;

	/**
	 * Bootstrap
	 */
	public static function bootstrap() {
		new self();
	}

	/**
	 * Construct and initializes an Formidable Forms extension object.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );

		add_action( 'pronamic_payment_status_update_' . self::SLUG, array( $this, 'update_status' ), 10, 2 );
		add_filter( 'pronamic_payment_source_text_' . self::SLUG,   array( $this, 'source_text' ), 10, 2 );
		add_filter( 'pronamic_payment_source_description_' . self::SLUG,   array( $this, 'source_description' ), 10, 2 );
		add_filter( 'pronamic_payment_source_url_' . self::SLUG,   array( $this, 'source_url' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFormActionsController.php#L39-L57
		// @see https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentSettingsController.php#L11
		add_action( 'frm_registered_form_actions', array( $this, 'registered_form_actions' ) );

		// @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFormActionsController.php#L299-L308
		// @see https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L28-L29
		add_action( 'frm_trigger_pronamic_pay_create_action', array( $this, 'create_action' ), 10, 3 );

		// @see https://github.com/wp-premium/formidable-paypal/blob/3.06/controllers/FrmPaymentSettingsController.php#L15-L19
		add_filter( 'frm_action_triggers', array( $this, 'add_payment_trigger' ) );
		add_filter( 'frm_email_action_options', array( $this, 'add_trigger_to_action' ) );
		add_filter( 'frm_twilio_action_options', array( $this, 'add_trigger_to_action' ) );
		add_filter( 'frm_mailchimp_action_options', array( $this, 'add_trigger_to_action' ) );
		add_filter( 'frm_register_action_options', array( $this, 'add_payment_trigger_to_register_user_action' ) );

		// Field types
		$this->field_type_bank_select           = new Pronamic_WP_Pay_Extensions_FormidableForms_BankSelectFieldType();
		$this->field_type_payment_method_select = new Pronamic_WP_Pay_Extensions_FormidableForms_PaymentMethodSelectFieldType();
	}

	/**
	 * Initialize.
	 */
	public function init() {

	}

	/**
	 * Admin enqueue scripts.
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();

		if (
			'toplevel_page_formidable' === $screen->id
				&&
			'settings' === filter_input( INPUT_GET, 'frm_action', FILTER_SANITIZE_STRING )
		) {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_register_style(
				'pronamic-pay-formidable',
				plugins_url( 'css/admin' . $min . '.css', dirname( __FILE__ ) ),
				array(),
				'1.0.0'
			);

			wp_enqueue_style( 'pronamic-pay-formidable' );
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Update entry payment status of the specified payment
	 *
	 * @param Pronamic_Pay_Payment|string $payment
	 * @param bool $can_redirect
	 *
	 * @since unreleased
	 */
	public function update_status( Pronamic_Pay_Payment $payment, $can_redirect = false ) {
		$entry_id = $payment->get_source_id();
		$entry    = FrmEntry::getOne( $entry_id );

		$status = get_post_meta( $payment->get_id(), '_pronamic_pay_formidable_forms_status', true );

		// Return if status has not changed.
		if ( $status === $payment->status ) {
			return;
		}

		update_post_meta( $payment->get_id(), '_pronamic_pay_formidable_forms_status', $payment->status );

		switch ( $payment->status ) {
			case Pronamic_WP_Pay_Statuses::CANCELLED :
				FrmFormActionsController::trigger_actions( 'pronamic-pay-cancelled', $entry->form_id, $entry->id );
				break;
			case Pronamic_WP_Pay_Statuses::EXPIRED :
				FrmFormActionsController::trigger_actions( 'pronamic-pay-expired', $entry->form_id, $entry->id );
				break;
			case Pronamic_WP_Pay_Statuses::FAILURE :
				FrmFormActionsController::trigger_actions( 'pronamic-pay-failure', $entry->form_id, $entry->id );
				break;
			case Pronamic_WP_Pay_Statuses::SUCCESS :
				FrmFormActionsController::trigger_actions( 'pronamic-pay-success', $entry->form_id, $entry->id );

				// Send delayed notifications
				$form_actions = FrmFormAction::get_action_for_form( $entry->form_id );

				$action_id = get_post_meta( $payment->get_id(), '_pronamic_pay_formidable_forms_action_id', true );

				if ( isset( $form_actions[ $action_id ] ) ) {
					$action = $form_actions[ $action_id ];

					if ( isset( $action->post_content['pronamic_pay_delay_notifications'] ) ) {
						$this->send_email_now( $entry );
					}
				}

				break;
			case Pronamic_WP_Pay_Statuses::OPEN :
				FrmFormActionsController::trigger_actions( 'pronamic-pay-pending', $entry->form_id, $entry->id );
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Source column
	 */
	public static function source_text( $text, Pronamic_WP_Pay_Payment $payment ) {
		$text  = '';

		$text .= __( 'Formidable', 'pronamic_ideal' ) . '<br />';
		$text .= sprintf(
			'<a href="%s">%s</a>',
			add_query_arg( array(
				'page'       => 'formidable-entries',
				'frm_action' => 'show',
				'id'         => $payment->get_source_id(),
			), admin_url( 'admin.php' ) ),
			sprintf( __( 'Entry #%s', 'pronamic_ideal' ), $payment->get_source_id() )
		);

		return $text;
	}

	/**
	 * Source description.
	 */
	public function source_description( $description, Pronamic_Pay_Payment $payment ) {
		$description = __( 'Formidable Forms Entry', 'pronamic_ideal' );

		return $description;
	}

	/**
	 * Source URL.
	 */
	public function source_url( $url, Pronamic_Pay_Payment $payment ) {
		$url = add_query_arg( array(
			'page'       => 'formidable-entries',
			'frm_action' => 'show',
			'id'         => $payment->get_source_id(),
		), admin_url( 'admin.php' ) );

		return $url;
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
		$this->action = $action;

		// @see https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L268-L269
		// @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmEntry.php#L698-L711
		add_action( 'frm_after_create_entry', array( $this, 'redirect_for_payment' ), 50, 2 );

		// Delay notifications
		if ( ! self::$send_email_now && 'on' === $action->post_content['pronamic_pay_delay_notifications'] ) {
			remove_action( 'frm_trigger_email_action', 'FrmNotification::trigger_email', 10, 3 );
			add_filter( 'frm_to_email', '__return_empty_array', 20 );
			add_filter( 'frm_send_new_user_notification', array( __CLASS__, 'stop_registration_email' ), 10, 3 );
		}
	}

	/**
	 * Redirect for payment.
	 *
	 * @see https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L274-L311
	 */
	public function redirect_for_payment( $entry_id, $form_id ) {
		$config_id = get_option( 'pronamic_pay_config_id' );

		$gateway = Pronamic_WP_Pay_Plugin::get_gateway( $config_id );

		if ( $gateway ) {
			$data = new Pronamic_WP_Pay_Extensions_FormidableForms_PaymentData( $entry_id, $form_id, $this->action );

			$payment = Pronamic_WP_Pay_Plugin::start( $config_id, $gateway, $data, Pronamic_WP_Pay_PaymentMethods::IDEAL );

			// Save form action ID for reference on status update.
			update_post_meta( $payment->get_id(), '_pronamic_pay_formidable_forms_action_id', $this->action->ID );

			$error = $gateway->get_error();

			if ( ! is_wp_error( $error ) ) {
				// Redirect
				$gateway->redirect( $payment );
			}
		}
	}

	/**
	 * Stop registration email.
	 *
	 * @param $send_it
	 * @param $form
	 * @param $entry_id
	 *
	 * @return bool
	 *
	 * @since unreleased
	 */
	public static function stop_registration_email( $send_it, $form, $entry_id ) {
		if ( ! is_callable( 'FrmRegAppController::send_paid_user_notification' ) ) {
			// Don't stop the registration email unless the function
			// from the Formidable Registration Add-On exists to send it later

			return $send_it;
		}

		return false;
	}

	/**
	 * Send email now.
	 *
	 * @param $entry
	 *
	 * @since unreleased
	 */
	public static function send_email_now( $entry ) {
		self::$send_email_now = true;

		// Trigger email action
		if ( is_callable( 'FrmFormActionsController::trigger_actions' ) ) {
			// Formidable Forms >= 2.0
			FrmFormActionsController::trigger_actions( 'create', $entry->form_id, $entry->id, 'email' );
		} elseif ( is_callable( 'FrmProNotification::entry_created' ) ) {
			// Formidable Forms < 2.0
			FrmProNotification::entry_created( $entry->id, $entry->form_id );
		}

		// Trigger registration email
		if ( is_callable( 'FrmRegNotification::send_paid_user_notification' ) ) {
			FrmRegNotification::send_paid_user_notification( $entry );
		} elseif ( is_callable( 'FrmRegAppController::send_paid_user_notification' ) ) {
			FrmRegAppController::send_paid_user_notification( $entry );
		}
	}

	/**
	 * Add payment trigger.
	 *
	 * @param $triggers
	 *
	 * @return array
	 *
	 * @since unreleased
	 */
	public static function add_payment_trigger( $triggers ) {
		$triggers['pronamic-pay-pending']   = __( 'Pronamic payment pending', 'pronamic_ideal' );
		$triggers['pronamic-pay-success']   = __( 'Pronamic payment success', 'pronamic_ideal' );
		$triggers['pronamic-pay-cancelled'] = __( 'Pronamic payment cancelled', 'pronamic_ideal' );
		$triggers['pronamic-pay-expired']   = __( 'Pronamic payment expired', 'pronamic_ideal' );
		$triggers['pronamic-pay-failed']    = __( 'Pronamic payment failed', 'pronamic_ideal' );

		return $triggers;
	}

	/**
	 * Add trigger to action.
	 *
	 * @param $options
	 *
	 * @return array
	 *
	 * @since unreleased
	 */
	public static function add_trigger_to_action( $options ) {
		$options['event'][] = 'pronamic-pay-pending';
		$options['event'][] = 'pronamic-pay-success';
		$options['event'][] = 'pronamic-pay-cancelled';
		$options['event'][] = 'pronamic-pay-expired';
		$options['event'][] = 'pronamic-pay-failed';

		return $options;
	}

	/**
	 * Add payment trigger to registration 2.0+
	 *
	 * @param array $options
	 *
	 * @return array
	 *
	 * @since unreleased
	 */
	public static function add_payment_trigger_to_register_user_action( $options ) {
		if ( is_callable( 'FrmRegUserController::register_user' ) ) {
			$options['event'][] = 'pronamic-pay-success';
		}

		return $options;
	}
}
