<?php

use Pronamic\WordPress\Pay\Extensions\FormidableForms\PaymentMethodSelectFieldType;
use Pronamic\WordPress\Pay\Plugin;

/**
 * Title: Formidable Forms payment settings
 * Description:
 * Copyright: 2005-2023 Pronamic
 * Company: Pronamic
 *
 * @link https://github.com/wp-premium/formidable-paypal/blob/3.02/views/settings/_payment_settings.php
 * @link https://github.com/wp-pay-extensions/gravityforms/blob/1.4.1/views/html-admin-meta-box-config.php
 * @author Remco Tolsma
 * @version 2.1.2
 * @since 1.0.0
 */

$callback_text_field = function ( $field ) use ( $instance ) {
	$id = $field['id'];

	$current = '';

	if ( \array_key_exists( $id, $instance->post_content ) ) {
		$current = $instance->post_content[ $id ];
	}

	printf(
		'<input type="text" name="%s" value="%s" class="large-text frm_help" title="" data-original-title="%s" />',
		esc_attr( $this->get_field_name( $id ) ),
		esc_attr( $current ),
		esc_attr( $field['description'] )
	);
};

$fields = [
	[
		'id'       => 'pronamic_pay_amount_field',
		'label'    => __( 'Amount', 'pronamic_ideal' ),
		'callback' => function ( $field ) use ( $form_fields, $instance ) {
			$id = $field['id'];

			$current = '';

			if ( \array_key_exists( $id, $instance->post_content ) ) {
				$current = $instance->post_content[ $id ];
			}

			printf(
				'<select name="%s">',
				esc_attr( $this->get_field_name( $id ) )
			);

			$options = [
				'' => __( '— Select Field —', 'pronamic_ideal' ),
			];

			foreach ( $form_fields as $form_field ) {
				$options[ $form_field->id ] = FrmAppHelper::truncate( $form_field->name, 50, 1 );
			}

			foreach ( $options as $value => $label ) {
				printf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $value ),
					selected( $current, $value, false ),
					esc_html( $label )
				);
			}

			echo '</select>';
		},
	],
	[
		'id'       => 'pronamic_pay_payment_method_field',
		'label'    => __( 'Payment method', 'pronamic_ideal' ),
		'callback' => function ( $field ) use ( $form_fields, $instance ) {
			$id = $field['id'];

			$current = '';

			if ( \array_key_exists( $id, $instance->post_content ) ) {
				$current = $instance->post_content[ $id ];
			}

			printf(
				'<select name="%s">',
				esc_attr( $this->get_field_name( $id ) )
			);

			$options = [
				'' => __( '— Select Field —', 'pronamic_ideal' ),
			];

			foreach ( $form_fields as $form_field ) {
				if ( PaymentMethodSelectFieldType::ID !== $form_field->type ) {
					continue;
				}

				$options[ $form_field->id ] = FrmAppHelper::truncate( $form_field->name, 50, 1 );
			}

			foreach ( $options as $value => $label ) {
				printf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $value ),
					selected( $current, $value, false ),
					esc_html( $label )
				);
			}

			echo '</select>';
		},
	],
	[
		'id'       => 'pronamic_pay_config_id',
		'label'    => __( 'Payment Gateway Configuration', 'pronamic_ideal' ),
		'callback' => function ( $field ) use ( $instance ) {
			$id = $field['id'];

			$current = '';

			if ( \array_key_exists( $id, $instance->post_content ) ) {
				$current = $instance->post_content[ $id ];
			}

			\printf(
				'<select name="%s">',
				esc_attr( $this->get_field_name( $id ) )
			);

			$options = Plugin::get_config_select_options();

			$options[0] = __( '– Default Gateway –', 'pronamic_ideal' );

			foreach ( $options as $value => $label ) {
				\printf(
					'<option value="%s" %s>%s</option>',
					\esc_attr( $value ),
					\selected( $current, $value, false ),
					\esc_html( $label )
				);
			}

			echo '</select>';
		},
	],
	[
		'id'          => 'pronamic_pay_order_id',
		'label'       => __( 'Order ID', 'pronamic_ideal' ),
		'description' => __( 'Enter an order ID, you can use Formidable Forms shortcodes.', 'pronamic_ideal' ),
		'callback'    => $callback_text_field,
	],
	[
		'id'          => 'pronamic_pay_transaction_description',
		'label'       => __( 'Transaction Description', 'pronamic_ideal' ),
		'description' => __( 'Enter a transaction description, you can use Formidable Forms shortcodes.', 'pronamic_ideal' ),
		'callback'    => $callback_text_field,
	],
	[
		'id'       => 'pronamic_pay_delay_notifications',
		'label'    => __( 'Notifications', 'pronamic_ideal' ),
		'callback' => function ( $field ) use ( $instance ) {
			$id = $field['id'];

			$current = '';

			if ( \array_key_exists( $id, $instance->post_content ) ) {
				$current = $instance->post_content[ $id ];
			}

			printf(
				'<input type="checkbox" name="%s" title="" %s /> %s',
				esc_attr( $this->get_field_name( $id ) ),
				checked( $current, 'on', false ),
				esc_attr__( 'Delay email notifications until payment has been received.', 'pronamic_ideal' )
			);
		},
	],
];

?>
<table class="form-table">

	<?php foreach ( $fields as $field ) : ?>

		<tr>
			<th scope="row">
				<?php echo esc_html( $field['label'] ); ?>
			</th>
			<td>
				<?php

				call_user_func( $field['callback'], $field );

				?>
			</td>
		</tr>

	<?php endforeach; ?>

</table>
