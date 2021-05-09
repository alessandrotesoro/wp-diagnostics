<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Check the existance of php extensions.
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

/**
 * Determine if one or more php extensions are available.
 */
class ExtensionLoaded extends AbstractCheck implements CheckInterface {

	/**
	 * @var array|Traversable
	 */
	protected $extensions;

	/**
	 * Setup the check.
	 *
	 * @param  string|array|Traversable $extensionName PHP extension name or an array of names
	 * @throws \InvalidArgumentException When the check is not properly configured.
	 */
	public function __construct( $extensionName ) {
		if ( is_object( $extensionName ) && ! $extensionName instanceof Traversable ) {
			throw new InvalidArgumentException(
				'Expected a module name (string) , an array or Traversable of strings, got ' . get_class( $extensionName )
			);
		}

		if ( ! is_object( $extensionName ) && ! is_array( $extensionName ) && ! is_string( $extensionName ) ) {
			throw new InvalidArgumentException( 'Expected a module name (string) or an array of strings' );
		}

		if ( is_string( $extensionName ) ) {
			$this->extensions = [ $extensionName ];
		} else {
			$this->extensions = $extensionName;
		}
	}

	/**
	 * Perform the check.
	 *
	 * @return bool|\WP_Error
	 */
	public function check() {

		$missing = [];

		foreach ( $this->extensions as $ext ) {
			if ( ! extension_loaded( $ext ) ) {
				$missing[] = $ext;
			}
		}

		if ( count( $missing ) ) {
			if ( count( $missing ) > 1 ) {
				return new \WP_Error( 'extensions-missing', sprintf( $this->getRunner()->getMessage( 'extension_loaded_plural' ), join( ', ', $missing ) ) );
			} else {
				return new \WP_Error( sanitize_title( current( $missing ) ), sprintf( $this->getRunner()->getMessage( 'extension_loaded' ), join( '', $missing ) ) );
			}
		}

		return true;
	}

}
