<?php

namespace Pronamic\WordPress\Pay\Extensions\FormidableForms;

use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Fields\IDealIssuerSelectField;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Util;

/**
 * Title: Formidable Forms bank select field type
 * Description:
 * Copyright: 2005-2024 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.4
 * @since   1.0.0
 */
class BankSelectFieldType {
	/**
	 * The unique ID of this field type.
	 *
	 * @var string
	 */
	const ID = 'pronamic_bank_select';

	/**
	 * Construct and initializes an Formidable Forms bank select field type.
	 *
	 * @link https://formidableforms.com/knowledgebase/add-a-new-field/
	 */
	public function __construct() {
		// @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmField.php#L10-L23
		add_filter( 'frm_available_fields', [ $this, 'available_fields' ] );

		// @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFieldsController.php#L74
		add_filter( 'frm_before_field_created', [ $this, 'before_field_created' ] );

		// @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/views/frm-fields/show-build.php#L64
		add_action( 'frm_display_added_fields', [ $this, 'display_added_fields' ] );

		// @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/views/frm-fields/input.php#L171
		add_action( 'frm_form_fields', [ $this, 'form_fields' ] );
	}

	/**
	 * Available fields.
	 *
	 * @see    https://formidableforms.com/knowledgebase/add-a-new-field/
	 * @see    https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmField.php#L10-L23
	 *
	 * @param array $fields Fields.
	 *
	 * @return $fields
	 */
	public function available_fields( $fields ) {
		$fields[ self::ID ] = __( 'Banks', 'pronamic_ideal' );

		return $fields;
	}

	/**
	 * Before field created.
	 *
	 * @link https://formidableforms.com/knowledgebase/add-a-new-field/
	 * @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFieldsController.php#L74
	 *
	 * @param array $field_data Field data.
	 *
	 * @return array
	 */
	public function before_field_created( $field_data ) {
		if ( self::ID === $field_data['type'] ) {
			$field_data['name'] = __( 'Choose a bank for iDEAL payment', 'pronamic_ideal' );
		}

		return $field_data;
	}

	/**
	 * Display added fields.
	 *
	 * @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/views/frm-fields/show-build.php#L64
	 *
	 * @param array $field Field.
	 */
	public function display_added_fields( $field ) {
		if ( self::ID === $field['type'] ) {
			$this->render_admin_field( $field );
		}
	}

	/**
	 * Render admin field.
	 *
	 * @param array $field Field.
	 */
	private function render_admin_field( $field ) {
		$this->render_field( $field );
	}

	/**
	 * Form fields.
	 *
	 * @link https://formidableforms.com/knowledgebase/add-a-new-field/
	 * @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/views/frm-fields/input.php#L171
	 *
	 * @param array $field Field.
	 */
	public function form_fields( $field ) {
		if ( self::ID === $field['type'] ) {
			$this->render_field( $field );
		}
	}

	/**
	 * Render field.
	 *
	 * @param array $field Field.
	 */
	private function render_field( $field ) {
		$config_id = get_option( 'pronamic_pay_config_id' );

		$gateway = Plugin::get_gateway( $config_id );

		if ( ! $gateway ) {
			return;
		}

		$issuer_field = $gateway->first_payment_method_field( PaymentMethods::IDEAL, IDealIssuerSelectField::class );

		if ( null === $issuer_field ) {
			return;
		}

		try {
			printf(
				'<select name="%s" id="%s">',
				esc_attr( sprintf( 'item_meta[%s]', $field['id'] ) ),
				esc_attr( sprintf( 'field_%s', $field['field_key'] ) )
			);

			foreach ( $issuer_field->get_options() as $option ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $option->get_element()->render();
			}

			echo '</select>';
		} catch ( \Exception $e ) {
			printf(
				'%s<br /><em>%s</em>',
				esc_html( Plugin::get_default_error_message() ),
				esc_html( $e->getMessage() )
			);
		}
	}
}
