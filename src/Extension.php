<?php

namespace Pronamic\WordPress\Pay\Extensions\FormidableForms;

use FrmEntry;
use FrmForm;
use FrmFormAction;
use FrmFormActionsController;
use FrmFormsHelper;
use FrmProNotification;
use FrmRegAppController;
use FrmRegNotification;
use Pronamic\WordPress\Money\Currency;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\AbstractPluginIntegration;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;

/**
 * Title: Formidable Forms extension
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.2.0
 * @since   1.0.0
 */
class Extension extends AbstractPluginIntegration {
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
	private static $send_email_now = false;

	/**
	 * Construct and initializes an Formidable Forms extension object.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'name' => __( 'Formidable Forms', 'pronamic_ideal' ),
			)
		);

		// Dependencies.
		$dependencies = $this->get_dependencies();

		$dependencies->add( new FormidableFormsDependency() );
	}

	/**
	 * Setup plugin integration.
	 *
	 * @return void
	 */
	public function setup() {
		add_filter( 'pronamic_payment_source_text_' . self::SLUG, array( $this, 'source_text' ), 10, 2 );
		add_filter( 'pronamic_payment_source_description_' . self::SLUG, array( $this, 'source_description' ), 10, 2 );

		// Check if dependencies are met and integration is active.
		if ( ! $this->is_active() ) {
			return;
		}

		add_action( 'pronamic_payment_status_update_' . self::SLUG, array( $this, 'update_status' ), 10, 2 );
		add_filter( 'pronamic_payment_source_url_' . self::SLUG, array( $this, 'source_url' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFormActionsController.php#L39-L57
		// @link https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentSettingsController.php#L11
		add_action( 'frm_registered_form_actions', array( $this, 'registered_form_actions' ) );

		// @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFormActionsController.php#L299-L308
		// @link https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L28-L29
		add_action( 'frm_trigger_pronamic_pay_create_action', array( $this, 'create_action' ), 10, 3 );

		// @link https://github.com/wp-premium/formidable-paypal/blob/3.06/controllers/FrmPaymentSettingsController.php#L15-L19
		add_filter( 'frm_action_triggers', array( $this, 'add_payment_trigger' ) );
		add_filter( 'frm_email_action_options', array( $this, 'add_trigger_to_action' ) );
		add_filter( 'frm_twilio_action_options', array( $this, 'add_trigger_to_action' ) );
		add_filter( 'frm_mailchimp_action_options', array( $this, 'add_trigger_to_action' ) );
		add_filter( 'frm_register_action_options', array( $this, 'add_payment_trigger_to_register_user_action' ) );

		// Field types.
		$this->field_type_bank_select = new BankSelectFieldType();

		if ( FormidableForms::version_compare( '3.0.0', '>' ) ) {
			$this->field_type_payment_method_select = new PaymentMethodSelectFieldType();
		}
	}

	/**
	 * Admin enqueue scripts.
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();

		$in_form_editor = ( 'toplevel_page_formidable' === $screen->id && 'edit' === filter_input( INPUT_GET, 'frm_action', FILTER_SANITIZE_STRING ) );
		$in_settings    = ( 'toplevel_page_formidable' === $screen->id && 'settings' === filter_input( INPUT_GET, 'frm_action', FILTER_SANITIZE_STRING ) );

		if ( ! $in_form_editor && ! $in_settings ) {
			return;
		}

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_style(
			'pronamic-pay-formidable-forms',
			plugins_url( 'css/admin' . $min . '.css', dirname( __FILE__ ) ),
			array(),
			'1.0.0'
		);

		wp_register_script(
			'pronamic-pay-formidable-forms',
			plugins_url( 'js/admin' . $min . '.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_enqueue_style( 'pronamic-pay-formidable-forms' );

		wp_enqueue_script( 'pronamic-pay-formidable-forms' );
	}

	/**
	 * Update entry payment status of the specified payment
	 *
	 * @param Payment $payment      Payment.
	 * @param bool    $can_redirect Whether or not to redirect.
	 *
	 * @since unreleased
	 */
	public function update_status( Payment $payment, $can_redirect = false ) {
		$entry_id = $payment->get_source_id();
		$entry    = FrmEntry::getOne( $entry_id );

		$status = get_post_meta( $payment->get_id(), '_pronamic_pay_formidable_forms_status', true );

		// Return if status has not changed.
		if ( $status === $payment->status ) {
			return;
		}

		update_post_meta( $payment->get_id(), '_pronamic_pay_formidable_forms_status', $payment->status );

		switch ( $payment->status ) {
			case PaymentStatus::CANCELLED:
				FrmFormActionsController::trigger_actions( 'pronamic-pay-cancelled', $entry->form_id, $entry->id );
				break;
			case PaymentStatus::EXPIRED:
				FrmFormActionsController::trigger_actions( 'pronamic-pay-expired', $entry->form_id, $entry->id );
				break;
			case PaymentStatus::FAILURE:
				FrmFormActionsController::trigger_actions( 'pronamic-pay-failure', $entry->form_id, $entry->id );
				break;
			case PaymentStatus::SUCCESS:
				FrmFormActionsController::trigger_actions( 'pronamic-pay-success', $entry->form_id, $entry->id );

				// Send delayed notifications.
				$form_actions = FrmFormAction::get_action_for_form( $entry->form_id );

				$action_id = get_post_meta( $payment->get_id(), '_pronamic_pay_formidable_forms_action_id', true );

				if ( isset( $form_actions[ $action_id ] ) ) {
					$action = $form_actions[ $action_id ];

					if ( isset( $action->post_content['pronamic_pay_delay_notifications'] ) ) {
						$this->send_email_now( $entry );
					}
				}

				break;
			case PaymentStatus::OPEN:
				FrmFormActionsController::trigger_actions( 'pronamic-pay-pending', $entry->form_id, $entry->id );
		}
	}

	/**
	 * Redirect URL.
	 *
	 * @param string  $url     Redirect URL.
	 * @param Payment $payment Payment.
	 * @return string
	 * @since 2.2.0
	 */
	public function redirect_url( $url, Payment $payment ) {
		// Check payment status.
		if ( PaymentStatus::SUCCESS !== $payment->get_status() ) {
			return $url;
		}

		// Get entry and form.
		$entry_id = $payment->get_source_id();

		$entry = FrmEntry::getOne( $entry_id );

		$form = FrmForm::getOne( $entry->form_id );

		// Check if redirect success URL should be used.
		if ( 'redirect' === $form->options['success_action'] ) {
			$success_url = \trim( $form->options['success_url'] );

			$success_url = \apply_filters( 'frm_content', $success_url, $form, $entry_id );

			$success_url = \do_shortcode( $success_url );

			$success_url = \filter_var( $success_url, \FILTER_SANITIZE_URL );

			// Return success URL from settings.
			if ( ! empty( $success_url ) ) {
				return $success_url;
			}
		}

		// Return default URL.
		return $url;
	}

	/**
	 * Source text.
	 *
	 * @param string  $text    Source text.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public static function source_text( $text, Payment $payment ) {
		$text = __( 'Formidable Forms', 'pronamic_ideal' ) . '<br />';

		$text .= sprintf(
			'<a href="%s">%s</a>',
			add_query_arg(
				array(
					'page'       => 'formidable-entries',
					'frm_action' => 'show',
					'id'         => $payment->get_source_id(),
				),
				admin_url( 'admin.php' )
			),
			sprintf(
				/* translators: %s: source id */
				__( 'Entry #%s', 'pronamic_ideal' ),
				$payment->get_source_id()
			)
		);

		return $text;
	}

	/**
	 * Source description.
	 *
	 * @param string  $description Source description.
	 * @param Payment $payment     Payment.
	 *
	 * @return string|void
	 */
	public function source_description( $description, Payment $payment ) {
		return __( 'Formidable Forms Entry', 'pronamic_ideal' );
	}

	/**
	 * Source URL.
	 *
	 * @param string  $url     Source URL.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public function source_url( $url, Payment $payment ) {
		$url = add_query_arg(
			array(
				'page'       => 'formidable-entries',
				'frm_action' => 'show',
				'id'         => $payment->get_source_id(),
			),
			admin_url( 'admin.php' )
		);

		return $url;
	}

	/**
	 * Registered form actions.
	 *
	 * @link https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentSettingsController.php#L125-L128
	 * @link https://github.com/wp-premium/formidable-paypal/blob/3.02/models/FrmPaymentAction.php
	 *
	 * @param array $actions Formidable Forms form actions.
	 *
	 * @return array
	 */
	public function registered_form_actions( $actions ) {
		$actions['pronamic_pay'] = __NAMESPACE__ . '\PaymentAction';

		return $actions;
	}

	/**
	 * Create action.
	 *
	 * @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFormActionsController.php#L299-L308
	 * @link https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L186-L193
	 *
	 * @param FrmFormAction $action Action.
	 * @param FrmEntry      $entry  Entry.
	 * @param FrmForm       $form   Form.
	 */
	public function create_action( $action, $entry, $form ) {
		/*
		 * Save config ID in object var for use building redirect url.
		 *
		 * @link https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L264-L266
		 */
		$this->action = $action;

		// @link https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L268-L269
		// @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmEntry.php#L698-L711
		add_action( 'frm_after_create_entry', array( $this, 'redirect_for_payment' ), 50, 2 );

		// Delay notifications.
		if ( ! self::$send_email_now && isset( $action->post_content['pronamic_pay_delay_notifications'] ) && 'on' === $action->post_content['pronamic_pay_delay_notifications'] ) {
			remove_action( 'frm_trigger_email_action', 'FrmNotification::trigger_email', 10 );
			add_filter( 'frm_to_email', '__return_empty_array', 20 );
			add_filter( 'frm_send_new_user_notification', array( __CLASS__, 'stop_registration_email' ), 10, 3 );
		}
	}

	/**
	 * Redirect for payment.
	 *
	 * @link https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L274-L311
	 *
	 * @param int $entry_id Entry ID.
	 * @param int $form_id  Form ID.
	 */
	public function redirect_for_payment( $entry_id, $form_id ) {
		$config_id = FormidableFormsHelper::get_config_id( $this->action );

		$gateway = Plugin::get_gateway( $config_id );

		if ( ! $gateway ) {
			return;
		}

		$entry = \FrmEntry::getOne( $entry_id, true );

		/**
		 * Build payment.
		 */
		$payment = new Payment();

		$payment->source    = 'formidable-forms';
		$payment->source_id = $entry_id;
		$payment->order_id  = $entry_id;

		$description = FormidableFormsHelper::get_description( $this->action, $form_id, $entry, $entry_id );

		if ( empty( $description ) ) {
			$description = \sprintf(
				'%s #%s',
				__( 'Submission', 'pronamic_ideal' ),
				$payment->source_id
			);
		}

		$payment->set_description( $description );

		$payment->title = \sprintf(
			/* translators: %s: payment data title */
			__( 'Payment for %s', 'pronamic_ideal' ),
			\sprintf(
				/* translators: %s: Formidable Forms entry ID */
				__( 'Formidable entry %s', 'pronamic_ideal' ),
				$entry_id
			)
		);

		// Amount.
		$payment->set_total_amount( FormidableFormsHelper::get_amount_from_field( $this->action, $entry ) );

		// Payment method.
		$payment_method = FormidableFormsHelper::get_payment_method_from_action_entry( $this->action, $entry );

		$payment->set_payment_method( $payment_method );

		// Only start payments for known/active payment methods.
		if ( null !== $payment_method && ! PaymentMethods::is_active( $payment_method ) ) {
			return;
		}

		// Check issuer in form entry.
		if ( empty( $payment_method ) && null !== FormidableFormsHelper::get_issuer_from_form_entry( $form_id, $entry ) ) {
			$payment->set_payment_method( PaymentMethods::IDEAL );
		}

		// Check if gateway requires payment method.
		if ( empty( $payment_method ) && $gateway->payment_method_is_required() ) {
			$payment->set_payment_method( PaymentMethods::IDEAL );
		}

		// Issuer.
		$payment->set_meta( 'issuer', FormidableFormsHelper::get_issuer_from_form_entry( $form_id, $entry ) );

		// Origin.
		$payment->set_origin_id( FormidableFormsHelper::get_origin_id_from_entry( $entry ) );

		// Configuration.
		$payment->config_id = $config_id;

		try {
			$payment = Plugin::start_payment( $payment );

			// Save form action ID for reference on status update.
			update_post_meta( $payment->get_id(), '_pronamic_pay_formidable_forms_action_id', $this->action->ID );

			if ( wp_doing_ajax() ) {
				// Do not use `wp_send_json_success()` as Formidable Forms doesn't properly handle the content type.
				echo wp_json_encode(
					array(
						'redirect' => $payment->get_pay_redirect_url(),
					)
				);

				exit;
			}

			// Redirect.
			$gateway->redirect( $payment );
		} catch ( \Exception $e ) {
			add_filter(
				'frm_main_feedback',
				function ( $message, $form, $entry_id ) use ( $e ) {
					$atts = array(
						'class'    => FrmFormsHelper::form_error_class(),
						'form'     => $form,
						'entry_id' => $entry_id,
						'message'  => sprintf(
							'%s<br>%s',
							\esc_html( Plugin::get_default_error_message() ),
							\esc_html( sprintf( '%s: %s', $e->getCode(), $e->getMessage() ) )
						),
					);

					return FrmFormsHelper::get_success_message( $atts );
				},
				10,
				3
			);

			return;
		}
	}

	/**
	 * Stop registration email.
	 *
	 * @param bool    $send_it  Whether or not to send email.
	 * @param FrmForm $form     Form.
	 * @param int     $entry_id Entry ID.
	 *
	 * @return bool
	 *
	 * @since unreleased
	 */
	public static function stop_registration_email( $send_it, $form, $entry_id ) {
		if ( ! is_callable( 'FrmRegAppController::send_paid_user_notification' ) ) {
			// Don't stop the registration email unless the function
			// from the Formidable Registration Add-On exists to send it later.
			return $send_it;
		}

		return false;
	}

	/**
	 * Send email now.
	 *
	 * @param FrmEntry $entry Entry.
	 *
	 * @since unreleased
	 */
	public static function send_email_now( $entry ) {
		self::$send_email_now = true;

		// Trigger email action.
		if ( is_callable( 'FrmFormActionsController::trigger_actions' ) ) {
			// Formidable Forms >= 2.0.
			FrmFormActionsController::trigger_actions( 'create', $entry->form_id, $entry->id, 'email' );
		} elseif ( is_callable( 'FrmProNotification::entry_created' ) ) {
			// Formidable Forms < 2.0.
			FrmProNotification::entry_created( $entry->id, $entry->form_id );
		}

		// Trigger registration email.
		if ( is_callable( 'FrmRegNotification::send_paid_user_notification' ) ) {
			FrmRegNotification::send_paid_user_notification( $entry );
		} elseif ( is_callable( 'FrmRegAppController::send_paid_user_notification' ) ) {
			FrmRegAppController::send_paid_user_notification( $entry );
		}
	}

	/**
	 * Add payment trigger.
	 *
	 * @param array $triggers Triggers.
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
	 * @param array $options Options.
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
	 * @param array $options Options.
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
