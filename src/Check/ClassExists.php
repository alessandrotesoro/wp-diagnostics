<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Check the existance of classes.
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
 * Determine if one or more classes exist.
 */
class ClassExists extends AbstractCheck implements CheckInterface {

	/**
	 * An array of classes to check
	 *
	 * @var array|Traversable
	 */
	protected $classes;

	/**
	 * Setup the check.
	 *
	 * @param  string|array|Traversable $classNames Class name or an array of classes
	 * @throws InvalidArgumentException When the check isn't properly configured.
	 */
	public function __construct( $classNames ) {
		if ( is_object( $classNames ) && ! $classNames instanceof Traversable ) {
			throw new InvalidArgumentException(
				'Expected a class name (string) , an array or Traversable of strings, got ' . get_class( $classNames )
			);
		}

		if ( ! is_object( $classNames ) && ! is_array( $classNames ) && ! is_string( $classNames ) ) {
			throw new InvalidArgumentException( 'Expected a class name (string) or an array of strings' );
		}

		if ( is_string( $classNames ) ) {
			$this->classes = [ $classNames ];
		} else {
			$this->classes = $classNames;
		}
	}

	/**
	 * Perform the check.
	 *
	 * @return bool|\WP_Error
	 */
	public function check() {

		$missing = [];

		foreach ( $this->classes as $class ) {
			if ( ! class_exists( $class ) ) {
				$missing[] = $class;
			}
		}

		if ( count( $missing ) > 1 ) {
			return new \WP_Error( 'classes-missing', sprintf( $this->getRunner()->getMessage( 'class_exists_plural' ), join( ', ', $missing ) ) );
		} elseif ( count( $missing ) == 1 ) {
			return new \WP_Error( sanitize_title( current( $missing ) ), sprintf( $this->getRunner()->getMessage( 'class_exists' ), current( $missing ) ) );
		}

		return true;
	}

}
