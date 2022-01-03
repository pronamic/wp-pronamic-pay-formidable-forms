<?php
/**
 * Formidable Forms Dependency
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\FormidableForms
 */

namespace Pronamic\WordPress\Pay\Extensions\FormidableForms;

use Pronamic\WordPress\Pay\Dependencies\Dependency;

/**
 * Formidable Forms Dependency
 *
 * @author  Reüel van der Steege
 * @version 2.1.0
 * @since   2.1.0
 */
class FormidableFormsDependency extends Dependency {
	/**
	 * Is met.
	 *
	 * @link
	 * @return bool True if dependency is met, false otherwise.
	 */
	public function is_met() {
		if ( ! \class_exists( '\FrmAppHelper' ) ) {
			return false;
		}

		return true;
	}
}
