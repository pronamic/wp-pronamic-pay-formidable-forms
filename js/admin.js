/**
 * Prevent editing built-in payment methods values.
 */
function pronamicPayPreventEditingBuiltInPaymentMethods() {
	jQuery( '.edit_field_type_pronamic_payment_method_select .frm_ipe_field_option_key' ).each( function () {
		var option_key = jQuery( this );

		if ( option_key.text().match( '^pronamic\_pay' ) ) {
			option_key.addClass( 'pronamic-pay-formidable-eip-disable' );
		}
	} );

	jQuery( '.frm_ipe_field_option, .frm_ipe_field_option_key' ).on( 'mouseenter', function () {
		var option_key = jQuery( this );

		if ( option_key.hasClass( 'pronamic-pay-formidable-eip-disable' ) ) {
			option_key.unbind( '.editInPlace' );
			option_key.addClass( 'pronamic-pay-formidable-eip-disable' );

			return false;
		}
	} );
}

( function ( $ ) {
	/**
	 * Ready.
	 */
	$( document ).ready( function() {
		// Prevent editing built-in payment methods values.
		pronamicPayPreventEditingBuiltInPaymentMethods();

		$( document ).ajaxSuccess( function() {
			// Prevent editing built-in payment methods values.
			pronamicPayPreventEditingBuiltInPaymentMethods();
		} );
	} );
} )( jQuery );
