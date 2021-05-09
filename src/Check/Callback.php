<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Run callbacks as checks.
 *
 * @package   wp-diagnostics
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

namespace Sematico\Diagnostics\Check;

use InvalidArgumentException;

/**
 * Run a function (callback) and use its return value as the result.
 */
class Callback extends AbstractCheck implements CheckInterface {

	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * @var array
	 */
	protected $params = [];

	/**
	 * Setup the callback check.
	 *
	 * @param  callable $callback
	 * @param  array    $params
	 * @throws \InvalidArgumentException When no valid callback is provided.
	 */
	public function __construct( $callback, $params = [] ) {
		if ( ! is_callable( $callback ) ) {
			throw new InvalidArgumentException( 'Invalid callback provided; not callable' );
		}

		$this->callback = $callback;
		$this->params   = $params;
	}

	/**
	 * Perform the check.
	 *
	 * @return bool|\WP_Error
	 */
	public function check() {
		return call_user_func_array( $this->callback, $this->params );
	}

}
