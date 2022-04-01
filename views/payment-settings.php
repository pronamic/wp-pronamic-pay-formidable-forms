<?php

use Pronamic\WordPress\Pay\Extensions\FormidableForms\PaymentMethodSelectFieldType;
use Pronamic\WordPress\Pay\Plugin;

/**
 * Title: Formidable Forms payment settings
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @link https://github.com/wp-premium/formidable-paypal/blob/3.02/views/settings/_payment_settings.php
 * @link https://github.com/wp-pay-extensions/gravityforms/blob/1.4.1/views/html-admin-meta-box-config.php
 * @author Remco Tolsma
 * @version 2.1.2
 * @since 1.0.0
 */

?>
<table class="form-table">
	<tr>
		<th scope="col">
			<?php esc_html_e( 'Amount', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			$current = $instance->post_content['pronamic_pay_amount_field'];

			printf(
				'<select name="%s">',
				esc_attr( $this->get_field_name( 'pronamic_pay_amount_field' ) )
			);

			$options = array(
				'' => __( '— Select Field —', 'pronamic_ideal' ),
			);

			foreach ( $form_fields as $field ) {
				$options[ $field->id ] = FrmAppHelper::truncate( $field->name, 50, 1 );
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

			?>
		</td>
	</tr>
	<tr>
		<th scope="col">
			<?php esc_html_e( 'Payment method', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			$current = $instance->post_content['pronamic_pay_payment_method_field'];

			printf(
				'<select name="%s">',
				esc_attr( $this->get_field_name( 'pronamic_pay_payment_method_field' ) )
			);

			$options = array(
				'' => __( '— Select Field —', 'pronamic_ideal' ),
			);

			foreach ( $form_fields as $field ) {
				if ( PaymentMethodSelectFieldType::ID !== $field->type ) {
					continue;
				}

				$options[ $field->id ] = FrmAppHelper::truncate( $field->name, 50, 1 );
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

			?>
		</td>
	</tr>
	<tr>
		<th scope="col">
			<?php \esc_html_e( 'Payment Gateway Configuration', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			\printf(
				'<select name="%s">',
				esc_attr( $this->get_field_name( 'pronamic_pay_config_id' ) )
			);

			$options = Plugin::get_config_select_options();

			$options[0] = __( '– Default Gateway –', 'pronamic_ideal' );

			foreach ( $options as $value => $label ) {
				\printf(
					'<option value="%s" %s>%s</option>',
					\esc_attr( $value ),
					\selected( $instance->post_content['pronamic_pay_config_id'], $value, false ),
					\esc_html( $label )
				);
			}

			echo '</select>';

			?>
		</td>
	</tr>
	<tr>
		<th scope="col">
			<?php esc_html_e( 'Transaction Description', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			printf(
				'<input type="text" name="%s" value="%s" class="large-text frm_help" title="" data-original-title="%s" />',
				esc_attr( $this->get_field_name( 'pronamic_pay_transaction_description' ) ),
				esc_attr( $instance->post_content['pronamic_pay_transaction_description'] ),
				esc_attr__( 'Enter a transaction description, you can use Formidable Forms shortcodes.', 'pronamic_ideal' )
			);

			?>

		</td>
	</tr>
	<tr>
		<th scope="col">
			<?php esc_html_e( 'Notifications', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			printf(
				'<input type="checkbox" name="%s" title="" %s /> %s',
				esc_attr( $this->get_field_name( 'pronamic_pay_delay_notifications' ) ),
				checked( $instance->post_content['pronamic_pay_delay_notifications'], 'on', false ),
				esc_attr__( 'Delay email notifications until payment has been received.', 'pronamic_ideal' )
			);

			?>

		</td>
	</tr>
</table>
