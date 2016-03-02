<?php

/**
 * Title: WordPress pay Formidable Forms test
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_FormidableForms_FormidableFormsTest extends PHPUnit_Framework_TestCase {
	/**
	 * Test.
	 */
	public function test_class() {
		$this->assertTrue( class_exists( 'Pronamic_WP_Pay_Extensions_FormidableForms_FormidableForms' ) );
	}
}
