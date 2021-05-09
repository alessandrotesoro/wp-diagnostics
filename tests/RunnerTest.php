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
use Sematico\Diagnostics\Runner;
use stdClass;
use WP_Error;

class RunnerTest extends \WP_UnitTestCase {

	/**
	 * @var Runner
	 */
	protected $runner;

	public function setUp(): void {
		$this->runner = new Runner( __FILE__ );
	}

	public function testCanAddChecks() {

		$check1 = new Callback(
			function() {
				return true;
			}
		);

		$check2 = new Callback(
			function() {
				return new WP_Error( 'example', 'Failed' );
			}
		);

		$this->runner->addCheck( $check1 );
		$this->runner->addCheck( $check2 );

		self::assertContains( $check1, $this->runner->getChecks() );
		self::assertContains( $check2, $this->runner->getChecks() );

	}

	public function testCanAddCheckWithAlias() {

		$check1 = new Callback(
			function() {
				return true;
			}
		);

		$this->runner->addCheck( $check1, 'example' );

		self::assertSame( $check1, $this->runner->getCheck( 'example' ) );

	}

	public function testExceptionOnNonExistingCheck() {

		$this->expectException( RuntimeException::class );

		$this->runner->getCheck( 'non-existing' );

	}

	public function testRunnerInitializationWithChecks() {

		$check1 = new Callback(
			function() {
				return true;
			}
		);

		$check2 = new Callback(
			function() {
				return new WP_Error( 'example', 'Failed' );
			}
		);

		$this->runner = new Runner( __FILE__, null, [ $check1, $check2 ] );

		$this->assertInstanceOf( Runner::class, $this->runner );
		$this->assertCount( 2, $this->runner->getChecks() );
		$this->assertContains( $check1, $this->runner->getChecks() );
		$this->assertContains( $check2, $this->runner->getChecks() );

	}

	public function testExceptionOnInvalidCheck() {
		$this->expectException( InvalidArgumentException::class );
		$this->runner->addChecks( [ new stdClass() ] );

		$this->expectException( InvalidArgumentException::class );
		$this->runner->addChecks( 'foo' );
	}

	public function testCanSetTitle() {
		$this->runner->setTitle( 'my title' );
		$this->assertSame( 'my title', $this->runner->getTitle() );
	}

	public function testCanSetBreak() {
		$this->runner->setBreakOnFailure( true );
		$this->assertTrue( $this->runner->getBreakOnFailure() );
	}

	public function testCanSetupStrings() {

		$runner = new Runner( __FILE__ );

		$this->assertSame( 'The &#8220;%1$s&#8221; plugin could not be activated.', $runner->getMessage( 'title' ) );

		$runner->setMessages( [ 'title' => 'test' ] );

		$this->assertSame( 'test', $runner->getMessage( 'title' ) );

	}

	public function testCanRunChecks() {

		$check1 = new Callback(
			function() {
				return true;
			}
		);

		$runner = new Runner( __FILE__ );
		$runner->addCheck( $check1 );

		$this->assertTrue( $runner->satisfied() );

		$check2 = new Callback(
			function() {
				return new WP_Error( 'example', 'Failed' );
			}
		);

		$runner->addCheck( $check2, 'test' );

		$this->assertFalse( $runner->satisfied() );
		$this->assertCount( 1, $runner->getResults() );

	}

}
