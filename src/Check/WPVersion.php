<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Check the current WP version.
 *
 * @package   wp-diagnostics
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

namespace Sematico\Diagnostics\Check;

use InvalidArgumentException;
use Traversable;
use WP_Error;

/**
 * Validate the current WP version.
 */
class WPVersion extends PHPVersion implements CheckInterface {

	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @var string
	 */
	protected $operator = '>=';

	/**
	 * Perform the check.
	 *
	 * @return bool|\WP_Error
	 */
	public function check() {
		if ( ! version_compare( get_bloginfo( 'version' ), $this->version, $this->operator ) ) {
			return new WP_Error( 'php-version', sprintf( $this->getRunner()->getMessage( 'wp_version' ), get_bloginfo( 'version' ), $this->operator, $this->version ) );
		}

		return true;
	}

}
