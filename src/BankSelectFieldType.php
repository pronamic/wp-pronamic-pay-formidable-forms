<?php

/**
 * Title: Formidable Forms bank select field type
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_FormidableForms_BankSelectFieldType {
	/**
	 * The unique ID of this field type.
	 *
	 * @var string
	 */
	const ID = 'pronamic_bank_select';

	/**
	 * Construct and initializes an Formidable Forms bank select field type.
	 *
	 * @see https://formidablepro.com/knowledgebase/add-a-new-field/
	 */
	public function __construct() {
		// @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmField.php#L10-L23
		add_filter( 'frm_available_fields', array( $this, 'available_fields' ) );

		// @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFieldsController.php#L74
		add_filter( 'frm_before_field_created', array( $this, 'before_field_created' ) );
	}

	/**
	 * Available fields.
	 *
	 * @see https://formidablepro.com/knowledgebase/add-a-new-field/
	 * @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmField.php#L10-L23
	 * @param array $fields
	 * @return $fields
	 */
	public function available_fields( $fields ) {
		$fields[ self::ID ] => __( 'Banks Dropdown', 'pronamic_ideal' );

		return $fields;
	}

	/**
	 * Before field created.
	 *
	 * @see https://formidablepro.com/knowledgebase/add-a-new-field/
	 * @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/controllers/FrmFieldsController.php#L74
	 * @param array $field_data
	 * @return array
	 */
	public function before_field_created( $field_data ) {
		if ( self::ID === $field_data['type'] ) {
			$field_data['name'] = __( 'Banks', 'pronamic_ideal' );
		}

		return $field_data;
	}
}
