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
	 * Construct and initializes an Formidable Forms extension object.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );

		add_filter( 'pronamic_payment_source_text_' . self::SLUG,   array( $this, 'source_text' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) ); 

		// @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFormActionsController.php#L39-L57
		// @see https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentSettingsController.php#L11
		add_action( 'frm_registered_form_actions', array( $this, 'registered_form_actions' ) );

		// @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFormActionsController.php#L299-L308
		// @see https://github.com/wp-premium/formidable-paypal/blob/3.02/controllers/FrmPaymentsController.php#L28-L29
		add_action( 'frm_trigger_pronamic_pay_create_action', array( $this, 'create_action' ), 10, 3 );

		// Field types
		$this->field_type_bank_select           = new Pronamic_WP_Pay_Extensions_FormidableForms_BankSelectFieldType();
		$this->field_type_payment_method_select = new Pronamic_WP_Pay_Extensions_FormidableForms_PaymentMethodSelectFieldType();
	}

	/**
	 * Initialize
	 */
	public function init() {

	}

	public function admin_enqueue_scripts() {
		$screen = get_current_screen();

		if ( 'toplevel_page_formidable' === $screen->id ) {
			?>
			<style type="text/css">
				.pronamic-pay-formidable-icon {
					background: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMjRweCIgaGVpZ2h0PSIyNHB4IiB2aWV3Qm94PSIwIDAgMTAwMCAxMDAwIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCAxMDAwIDEwMDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPHBhdGggZD0iTTUxNi4wOTQsNjguMDg4YzQyMS4wNCwwLDQ4My45MDYsMjY5Ljk5Nyw0ODMuOTA2LDQzMC45MDRjMCwyNzkuMTc2LTE3MS44NDIsNDMyLjkyLTQ4My45MDYsNDMyLjkyDQoJYzAsMC01MDUuOTAxLDAtNTE2LjA5NCwwQzAsOTIxLjY3NiwwLDc4LjI5NiwwLDY4LjA4OEMxMC4xOTMsNjguMDg4LDUxNi4wOTQsNjguMDg4LDUxNi4wOTQsNjguMDg4IE00MC42MywxMDguNzAydjc4Mi41ODFoNDc1LjQ2Mw0KCWMyODcuMTA0LDAsNDQzLjI4OS0xMzMuNzQ0LDQ0My4yODktMzkyLjI5YzAtMjY1LjY5OS0xNjkuMjk5LTM5MC4yOTEtNDQzLjI4OS0zOTAuMjkxSDQwLjYzeiIvPg0KPHJlY3QgeD0iMTA0LjQxOCIgeT0iNTU2LjUzMyIgd2lkdGg9IjE1Mi4yMDgiIGhlaWdodD0iMjcwLjk1NyIvPg0KPHBhdGggZD0iTTI3NS4wOTUsNDIwLjA1OGMwLDUyLjI0Ni00Mi4zMzMsOTQuNTk4LTk0LjU4NSw5NC41OThjLTUyLjIzLDAtOTQuNjA1LTQyLjM1Mi05NC42MDUtOTQuNTk4DQoJYzAtNTIuMjA4LDQyLjM3NS05NC41OTUsOTQuNjA1LTk0LjU5NUMyMzIuNzYzLDMyNS40NjMsMjc1LjA5NSwzNjcuODUxLDI3NS4wOTUsNDIwLjA1OCIvPg0KPHBhdGggZmlsbD0iI0NDMDA2NiIgZD0iTTU3My43NDQsNDY0LjMyNHY0My44NDRINDY1LjI4MXYtMTc2aDEwNC45NTV2NDMuODI2YzAsMC0zNy4zNTQsMC02MS4xMDUsMGMwLDYuMDQsMCwxMi45NzIsMCwyMC40MDINCgloNTcuNzk4djQzLjgxNEg1MDkuMTNjMCw4Ljc5OSwwLDE3LjA5MywwLDI0LjExNUM1MzMuNTc1LDQ2NC4zMjQsNTczLjc0NCw0NjQuMzI0LDU3My43NDQsNDY0LjMyNCBNNTk0LjgwNCw1MDguMjI2bDUzLjA4OC0xNzYuMTMyDQoJaDYyLjM3N2w1My4wNzYsMTc2LjEzMkg3MTcuNzFsLTkuOTUyLTM0LjA4MWgtNTcuMzU0bC05Ljk3MywzNC4wODFINTk0LjgwNHogTTY2My4yMDgsNDMwLjM0MWgzMS43MzFsLTE0LjUwNi00OS43NjloLTIuNjI5DQoJTDY2My4yMDgsNDMwLjM0MXogTTc4NS4zODEsMzMyLjEwNmg0My44MjljMCwwLDAsOTkuOTU5LDAsMTMyLjIxOGM5LjcyOCwwLDM2LjQzLDAsNjQuOTc1LDANCgljLTE3LjgyMi0yMzkuOTE2LTIwNi41NS0yOTEuNzUxLTM3OC4wNTgtMjkxLjc1MUgzMzMuMjcxdjE1OS42MjNoMjcuMDczYzQ5LjM0LDAsODAuMDAxLDMzLjQ2LDgwLjAwMSw4Ny4yODcNCgljMCw1NS41MzYtMjkuOTIsODguNjg1LTgwLjAwMSw4OC42ODVoLTI3LjA3M3YzMTkuNDA1aDE4Mi44NTVjMjc4LjgzNCwwLDM3NS44OC0xMjkuNTAzLDM3OS4zMDQtMzE5LjQwNWgtMTEwLjA1VjMzMi4xMDZ6DQoJIE0zMzMuMjQyLDM3Ni4wMmMwLDI0LjQ3OCwwLDYzLjg0OSwwLDg4LjMwNGMxMy40MDgsMCwyNy4xMDMsMCwyNy4xMDMsMGMxOC43NzYsMCwzNi4xMjMtNS40MjIsMzYuMTIzLTQ0Ljg0MQ0KCWMwLTM4LjUyMy0xOS4zMDMtNDMuNDYzLTM2LjEyMy00My40NjNDMzYwLjM0NSwzNzYuMDIsMzQ2LjY1LDM3Ni4wMiwzMzMuMjQyLDM3Ni4wMnoiLz4NCjwvc3ZnPg==") no-repeat;

					display: inline-block;

					width: 24px;
					height: 24px;

					margin: 0 5px;

					vertical-align: middle;
				}
			</style>
			<?php
		}
	}

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
