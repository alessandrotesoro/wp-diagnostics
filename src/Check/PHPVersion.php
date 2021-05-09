<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Check the current php version.
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
 * Validate the current php version.
 */
class PHPVersion extends AbstractCheck implements CheckInterface {

	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @var string
	 */
	protected $operator = '>=';

	/**
	 *
	 * @param  string|array|Traversable $expectedVersion The expected version
	 * @param  string                   $operator        One of: <, lt, <=, le, >, gt, >=, ge, ==, =, eq, !=, <>, ne
	 * @throws InvalidArgumentException When the check is not properly configured.
	 */
	public function __construct( $expectedVersion, $operator = '>=' ) {
		$this->version = $expectedVersion;

		if ( ! is_scalar( $operator ) ) {
			throw new InvalidArgumentException(
				'Expected comparison operator as a string, got ' . gettype( $operator )
			);
		}

		if ( ! in_array(
			$operator,
			[
				'<',
				'lt',
				'<=',
				'le',
				'>',
				'gt',
				'>=',
				'ge',
				'==',
				'=',
				'eq',
				'!=',
				'<>',
				'ne',
			]
		) ) {
			throw new InvalidArgumentException(
				'Unknown comparison operator ' . $operator
			);
		}

		$this->operator = $operator;
	}

	/**
	 * Perform the check.
	 *
	 * @return bool|\WP_Error
	 */
	public function check() {
		if ( ! version_compare( PHP_VERSION, $this->version, $this->operator ) ) {
			return new WP_Error( 'php-version', sprintf( $this->getRunner()->getMessage( 'php_version' ), PHP_VERSION, $this->operator, $this->version ) );
		}

		return true;
	}

}
