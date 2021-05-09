<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Individual check interface.
 *
 * @package   wp-diagnostics
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

namespace Sematico\Diagnostics\Check;

use Sematico\Diagnostics\Runner;

interface CheckInterface {

	/**
	 * Perform the actual check and return a ResultInterface
	 *
	 * @return bool|\WP_Error
	 */
	public function check();

	/**
	 * Return a label describing this test instance.
	 *
	 * @return string
	 */
	public function getLabel();

	/**
	 * Assign a runner to the check.
	 *
	 * @param Runner $runner
	 * @return void
	 */
	public function setRunner( Runner $runner );

	/**
	 * Get the runner associated with the check.
	 *
	 * @return Runner
	 */
	public function getRunner();
}
