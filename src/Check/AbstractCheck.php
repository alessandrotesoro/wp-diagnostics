<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Check abstract class.
 *
 * @package   wp-diagnostics
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

namespace Sematico\Diagnostics\Check;

use Sematico\Diagnostics\Runner;

/**
 * Base Check class.
 * Defines common methods used by all checks.
 */
abstract class AbstractCheck implements CheckInterface {

	/**
	 * The label of the check.
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * The runner instance using the check.
	 *
	 * @var Runner
	 */
	protected $runner;

	/**
	 * Get the label of the check.
	 *
	 * @return string
	 */
	public function getLabel() {
		if ( $this->label !== null ) {
			return $this->label;
		}

		$class = get_class( $this );
		$class = substr( $class, strrpos( $class, '\\' ) + 1 );
		$class = preg_replace( '/([A-Z])/', ' $1', $class );

		return trim( $class );
	}

	/**
	 * Set label for the check.
	 *
	 * @param string $label
	 */
	public function setLabel( $label ) {
		$this->label = $label;
	}

	/**
	 * Assign a runner to the check.
	 *
	 * @param Runner $runner
	 * @return void
	 */
	public function setRunner( Runner $runner ) {
		$this->runner = $runner;
	}

	/**
	 * Get the runner associated with the check.
	 *
	 * @return Runner
	 */
	public function getRunner() {
		return $this->runner;
	}
}
