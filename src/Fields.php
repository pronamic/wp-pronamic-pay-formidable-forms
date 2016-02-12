<?php

/**
 * Title: Formidable Forms fields
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_FormidableForms_Fields {
	/**
	 * Construct and initializes an Formidable Forms fields object.
	 */
	public function __construct() {
		// @see https://formidablepro.com/knowledgebase/add-a-new-field/
		// @see https://github.com/wp-premium/formidable/blob/2.0.21/classes/models/FrmField.php#L10-L23
		add_filter( 'frm_available_fields', array( $this, 'available_fields' ) );
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
		$fields['pronamic_pay_bank_select']            => __( 'Banks Dropdown', 'pronamic_ideal' );
		$fields['pronamic_pay_payment_methods_select'] => __( 'Payment Methods Dropdown', 'pronamic_ideal' );

		return $fields;
	}
}
