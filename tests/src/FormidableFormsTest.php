<?php

namespace Pronamic\WordPress\Pay\Extensions\FormidableForms;

use PHPUnit_Framework_TestCase;

/**
 * Title: WordPress pay Formidable Forms test
 * Description:
 * Copyright: 2005-2021 Pronamic
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 * @since 1.0.0
 */
class FormidableFormsTest extends PHPUnit_Framework_TestCase {
	/**
	 * Test.
	 */
	public function test_class() {
		$this->assertTrue( class_exists( __NAMESPACE__ . '\FormidableForms' ) );
	}
}
