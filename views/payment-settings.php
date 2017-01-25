<?php

/**
 * Title: Formidable Forms payment settings
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @see https://github.com/wp-premium/formidable-paypal/blob/3.02/views/settings/_payment_settings.php
 * @see https://github.com/wp-pay-extensions/gravityforms/blob/1.4.1/views/html-admin-meta-box-config.php
 * @author Remco Tolsma
 * @version 1.0.0
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
			<?php esc_html_e( 'Transaction Description', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			printf(
				'<input type="text" name="%s" value="%s" class="large-text frm_help" title="" data-original-title="%s" />',
				esc_attr( $this->get_field_name( 'pronamic_pay_transaction_description' ) ),
				esc_attr( $instance->post_content['pronamic_pay_transaction_description'] ),
				esc_attr__( 'Enter a transactaction description, you can use Formidable Forms shortcodes.', 'pronamic_ideal' )
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
				esc_attr__( 'Hold email notifications until payment is received.', 'pronamic_ideal' )
			);

			?>

		</td>
	</tr>
</table>
