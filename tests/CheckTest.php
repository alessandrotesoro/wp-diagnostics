<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Backyard redirects test.
 *
 * @package   backyard-foundation
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

namespace Sematico\Diagnostics\Tests;

use InvalidArgumentException;
use RuntimeException;
use Sematico\Diagnostics\Check\Callback;
use Sematico\Diagnostics\Check\ClassExists;
use Sematico\Diagnostics\Check\ExtensionLoaded;
use Sematico\Diagnostics\Check\PHPVersion;
use Sematico\Diagnostics\Check\WPVersion;
use Sematico\Diagnostics\Runner;
use stdClass;
use WP_Error;

class CheckTest extends \WP_UnitTestCase {

	/**
	 * @var Runner
	 */
	protected $runner;

	public function setUp(): void {
		$this->runner = new Runner( __FILE__ );
	}

	public function testLabels(): void {
		$label = md5( rand() );
		$check = new Callback(
			function() {
				return true;
			}
		);
		$check->setLabel( $label );
		self::assertEquals( $label, $check->getLabel() );
	}

	public function testClassExists() {

		$check = new ClassExists( __CLASS__ );
		self::assertTrue( $check->check() );

		$check = new ClassExists( 'nonexistingname' );
		$check->setRunner( $this->runner );
		self::assertInstanceOf( WP_Error::class, $check->check() );

		$check = new ClassExists( [ __CLASS__, WP_Error::class ] );
		$check->setRunner( $this->runner );

		self::assertTrue( $check->check() );

	}

	public function testPHPVersion() {

		$check = new PHPVersion( PHP_VERSION );
		self::assertTrue( $check->check() );

		$check = new PHPVersion( PHP_VERSION, '=' );
		self::assertTrue( $check->check() );

		$check = new PHPVersion( PHP_VERSION, '<' );
		$check->setRunner( $this->runner );
		self::assertInstanceOf( WP_Error::class, $check->check() );

	}

	public function testCallback(): void {
		$check1 = new Callback(
			function() {
				return true;
			}
		);

		self::assertTrue( $check1->check() );

		$check2 = new Callback(
			function() {
				return new WP_Error( 'example', 'Failed' );
			}
		);

		$check2->setRunner( $this->runner );

		self::assertInstanceOf( WP_Error::class, $check2->check() );

	}

	public function testExtensionLoaded() {
		$allExtensions = get_loaded_extensions();
		$ext1          = $allExtensions[ array_rand( $allExtensions ) ];

		$check = new ExtensionLoaded( $ext1 );
		self::assertTrue( $check->check() );

		$check = new ExtensionLoaded( 'fakeextension' );
		$check->setRunner( $this->runner );
		self::assertInstanceOf( WP_Error::class, $check->check() );
	}

	public function testWPVersion() {

		$check = new WPVersion( '5.7' );
		self::assertTrue( $check->check() );

		$check = new WPVersion( '5.6', '<' );
		$check->setRunner( $this->runner );
		self::assertInstanceOf( WP_Error::class, $check->check() );

	}

}
