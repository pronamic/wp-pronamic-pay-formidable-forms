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

	//////////////////////////////////////////////////

	/**
	 * Bootstrap
	 */
	public static function bootstrap() {
		new self();
	}

	/**
	 * Construct and initializes an Charitable extension object.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );

	}

	//////////////////////////////////////////////////

	/**
	 * Initialize
	 */
	public function init() {

	}
}
