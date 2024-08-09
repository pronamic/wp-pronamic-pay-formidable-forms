<?php

namespace Pronamic\WordPress\Pay\Extensions\FormidableForms;

use FrmAppHelper;
use FrmField;
use FrmFieldDefault;
use FrmFieldFactory;
use FrmFieldSelect;
use FrmFieldsHelper;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Plugin;

/**
 * Title: Formidable Forms payment method select field type
 * Description:
 * Copyright: 2005-2024 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.4
 * @since   1.0.0
 */
class PaymentMethodSelectFieldType {
	/**
	 * The unique ID of this field type.
	 *
	 * @var string
	 */
	const ID = 'pronamic_payment_method_select';

	/**
	 * Indicator to track if we're processing from field options.
	 *
	 * @var bool
	 */
	protected $in_field_options = false;

	/**
	 * Construct and initializes an Formidable Forms payment method select field type.
	 *
	 * @link https://formidableforms.com/knowledgebase/add-a-new-field/
	 */
	public function __construct() {
		// @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmField.php#L10-L23
		add_filter( 'frm_available_fields', [ $this, 'available_fields' ] );

		// @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFieldsController.php#L74
		add_filter( 'frm_before_field_created', [ $this, 'before_field_created' ] );

		// @link https://formidableforms.com/knowledgebase/add-a-new-field/#kb-save-field-options
		add_filter( 'frm_update_field_options', [ $this, 'update_field_options' ], 10, 3 );

		// @link https://formidableforms.com/knowledgebase/frm_setup_edit_fields_vars/
		add_filter( 'frm_setup_edit_fields_vars', [ $this, 'edit_fields_vars' ], 10, 1 );

		add_filter( 'frm_switch_field_types', [ $this, 'switch_field_types' ], 10, 2 );

		// @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/views/frm-fields/show-build.php#L64
		add_action( 'frm_display_added_fields', [ $this, 'display_added_fields' ] );

		// @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/views/frm-fields/input.php#L171
		add_action( 'frm_form_fields', [ $this, 'form_fields' ] );

		// @link https://formidableforms.com/knowledgebase/add-a-new-field/#kb-modify-value-displayed-when-viewing-entry
		add_filter( 'frm_display_' . self::ID . '_value_custom', [ $this, 'display_field_value' ] );

		add_filter( 'frm_bulk_field_choices', [ $this, 'bulk_field_choices' ] );

		add_action( 'wp_ajax_frm_import_options', [ $this, 'import_options' ], 9 );
	}

	/**
	 * Available fields.
	 *
	 * @see    https://formidableforms.com/knowledgebase/add-a-new-field/
	 * @see    https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmField.php#L10-L23
	 *
	 * @param array $fields Available fields.
	 *
	 * @return $fields
	 */
	public function available_fields( $fields ) {
		$fields[ self::ID ] = __( 'Payment Methods', 'pronamic_ideal' );

		if ( FormidableForms::version_compare( '3.0.0', '>' ) ) {
			$icon = 'frm_credit-card-alt_icon';

			if ( FormidableForms::version_compare( '4.0.0', '>' ) ) {
				$icon = 'frm_credit_card_alt_icon';
			}

			// Add icon in Formidable Forms 3.0+.
			$fields[ self::ID ] = [
				'name' => __( 'Payment Method', 'pronamic_ideal' ),
				'icon' => 'frm_icon_font ' . $icon,
			];
		}

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
			$field_data['name'] = __( 'Choose a payment method', 'pronamic_ideal' );

			$defaults = [
				'separate_value' => 1,
			];

			$field_data['field_options'] = array_merge( $field_data['field_options'], $defaults );
		}

		return $field_data;
	}

	/**
	 * Setup field editor variables.
	 *
	 * @param array $field Field as array.
	 *
	 * @return array
	 */
	public function edit_fields_vars( $field ) {
		// Check original field type.
		if ( self::ID !== $field['original_type'] ) {
			return $field;
		}

		// Set field type 'select'.
		$field['type'] = 'select';

		if ( FormidableForms::version_compare( '4.0.0', '>' ) ) {
			$this->in_field_options = true;

			$field['original_type'] = 'select';

			if ( empty( $field['options'] ) ) {
				$field['options'] = $this->get_payment_methods();
			}
		}

		return $field;
	}

	/**
	 * Filter available field types to switch to in field options.
	 *
	 * @param array  $field_types Array of field types that can be switched to.
	 * @param string $type        Field type to get switch options for.
	 *
	 * @return array
	 */
	public function switch_field_types( $field_types, $type ) {
		if ( ! $this->in_field_options ) {
			return $field_types;
		}

		$this->in_field_options = false;

		return [
			self::ID => [
				'name' => __( 'Payment Methods', 'pronamic_ideal' ),
				'icon' => 'frm_icon_font frm_',
			],
		];
	}

	/**
	 * Update field options.
	 *
	 * @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/views/frm-fields/show-build.php#L64https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmForm.php#L256
	 *
	 * @param array        $field_options Field options.
	 * @param FrmFieldType $field         Field object.
	 * @param array        $values        Field values.
	 *
	 * @return array
	 */
	public function update_field_options( $field_options, $field, $values ) {
		if ( self::ID !== $field->type ) {
			return $field_options;
		}

		$field_options['separate_value'] = 1;

		return $field_options;
	}

	/**
	 * Display added fields.
	 *
	 * @link https://github.com/wp-premium/formidable/blob/2.0.21/classes/views/frm-fields/show-build.php#L64
	 *
	 * @param array $field Field.
	 */
	public function display_added_fields( $field ) {
		if ( self::ID !== $field['original_type'] ) {
			return;
		}

		$this->in_field_options = true;

		$payment_method_select = FrmFieldFactory::get_field_type( self::ID );

		$display = $payment_method_select->display_field_settings();

		$field_name         = sprintf( 'item_meta[%s]', $field['id'] );
		$html_id            = sprintf( 'field_%s', $field['field_key'] );
		$field['html_name'] = $field_name;
		$field['html_id']   = $html_id;

		if ( FormidableForms::version_compare( '4.0.0', '<' ) ) {
			// Set options for field editor.
			$options = $field['options'];

			if ( empty( $options ) ) {
				$options = $this->get_payment_methods();
			}

			$field['options'] = $options;
		}

		// Temporarily change field type.
		$field['type'] = 'select';

		require \FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/dropdown-field.php';
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
		if ( self::ID !== $field['type'] ) {
			return;
		}

		$field_name         = sprintf( 'item_meta[%s]', $field['id'] );
		$html_id            = sprintf( 'field_%s', $field['field_key'] );
		$field['html_name'] = $field_name;
		$field['html_id']   = $html_id;

		// Set front-end options.
		if ( empty( $field['options'] ) ) {
			$field['options'] = $this->get_payment_methods();
		}

		// Read only.
		$read_only = isset( $field['read_only'] ) ? $field['read_only'] : false;

		require \FrmAppHelper::plugin_path() . '/classes/views/frm-fields/front-end/dropdown-field.php';
	}

	/**
	 * Display value for field.
	 *
	 * @param string $value Field value.
	 *
	 * @return string
	 */
	public function display_field_value( $value ) {
		if ( empty( $value ) ) {
			return $value;
		}

		$payment_methods = $this->get_payment_methods();

		foreach ( $payment_methods as $payment_method ) {
			if ( $value !== $payment_method['value'] ) {
				continue;
			}

			$value = $payment_method['label'];
		}

		return $value;
	}

	/**
	 * Bulk field choices.
	 *
	 * @param array $choices Bulk editor choices.
	 *
	 * @return array
	 */
	public function bulk_field_choices( $choices ) {
		$methods = [];

		$payment_methods = $this->get_payment_methods();

		foreach ( $payment_methods as $payment_method ) {
			$methods[] = sprintf(
				'%1$s|%2$s',
				$payment_method['label'],
				$payment_method['value']
			);
		}

		$choices[ __( 'Payment Methods', 'pronamic_ideal' ) ] = $methods;

		return $choices;
	}

	/**
	 * Import options from bulk editor.
	 */
	public function import_options() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		check_ajax_referer( 'frm_ajax', 'nonce' );

		if ( ! is_admin() || ! current_user_can( 'frm_edit_forms' ) ) {
			return;
		}

		if ( ! \array_key_exists( 'field_id', $_POST ) ) {
			return;
		}

		$field_id = \sanitize_text_field( \wp_unslash( $_POST['field_id'] ) );

		$field = FrmField::getOne( $field_id );

		if ( self::ID !== $field->type ) {
			return;
		}

		$field = FrmFieldsHelper::setup_edit_vars( $field );

		$options = FrmAppHelper::get_param( 'opts', '', 'post', 'wp_kses_post' );
		$options = explode( "\n", rtrim( $options, "\n" ) );
		$options = array_map( 'trim', $options );

		foreach ( $options as $option_key => $option ) {
			if ( false === strpos( $option, '|' ) ) {
				continue;
			}

			$values = explode( '|', $option );

			$label = trim( $values[0] );
			$value = trim( $values[1] );

			if ( $label !== $value ) {
				$options[ $option_key ] = [
					'label' => $label,
					'value' => $value,
				];
			}
		}

		$field['options'] = $options;

		FrmFieldsHelper::show_single_option( $field );

		wp_die();
	}

	/**
	 * Get payment method options.
	 */
	public function get_payment_methods() {
		$config_id = get_option( 'pronamic_pay_config_id' );

		$gateway = Plugin::get_gateway( $config_id );

		if ( null === $gateway ) {
			return [];
		}

		try {
			$payment_methods = $gateway->get_payment_methods(
				[
					'status' => [ '', 'active' ],
				]
			);

			$result = [];

			foreach ( $payment_methods as $payment_method ) {
				$value = sprintf( 'pronamic_pay_%s', $payment_method->get_id() );

				$result[] = [
					'label' => $payment_method->get_name(),
					'value' => $value,
				];
			}

			return $result;
		} catch ( \Exception $e ) {
			return [];
		}
	}
}
