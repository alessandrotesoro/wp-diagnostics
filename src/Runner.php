<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Diagnostics checks runner.
 *
 * @package   wp-diagnostics
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

namespace Sematico\Diagnostics;

use ArrayObject;
use ErrorException;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Traversable;
use WP_Error;
use Sematico\Diagnostics\Check\CheckInterface;

/**
 * Diagnostics checks runner.
 * Attach one or more checks to verify them in sequence.
 */
class Runner {

	/**
	 * An array of Checks to run.
	 *
	 * @var ArrayObject
	 */
	protected $checks;

	/**
	 * Should the run stop on first failure.
	 *
	 * @var bool
	 */
	protected $breakOnFailure = false;

	/**
	 * List of failed checks.
	 *
	 * @var array
	 */
	protected $results = [];

	/**
	 * Contains the list of default messages of the library.
	 *
	 * @var array
	 */
	protected $i18n = [];

	/**
	 * The name of the plugin using the library.
	 *
	 * @var string
	 */
	protected $pluginTitle;

	/**
	 * Path to the plugin's entry file.
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * Create a new instance of the Runner, optionally providing initial checks to perform.
	 *
	 * @param string                 $file Path to the plugin's entry file.
	 * @param string                 $title Title of the plugin using the library.
	 * @param null|array|Traversable $checks List of checks to perform.
	 * @param bool                   $breakOnFailure Should the runner stop on first failure.
	 * @param array                  $i18n List of error messages dipslayed by the library.
	 */
	public function __construct( $file, $title = null, $checks = null, $breakOnFailure = false, $i18n = [] ) {

		$this->file   = $file;
		$this->checks = new ArrayObject();

		if ( ! empty( $title ) ) {
			$this->setTitle( $title );
		}

		if ( $checks !== null ) {
			$this->addChecks( $checks );
		}

		$this->setBreakOnFailure( $breakOnFailure );

		$this->setMessages( $i18n );

	}

	/**
	 * Add diagnostic check to run.
	 *
	 * @param CheckInterface $check
	 * @param string|null    $alias
	 */
	public function addCheck( CheckInterface $check, $alias = null ) {
		$alias = is_string( $alias ) ? $alias : count( $this->checks );

		$check->setRunner( $this );

		$this->checks[ $alias ] = $check;
	}

	/**
	 * Add multiple Checks from an array, Traversable or CheckCollectionInterface.
	 *
	 * @param  array|Traversable|CheckCollectionInterface $checks
	 * @throws InvalidArgumentException When checks aren't properly configured.
	 */
	public function addChecks( $checks ) {
		if ( ! is_array( $checks ) && ! $checks instanceof Traversable ) {
			throw new InvalidArgumentException( 'Cannot add checks - expected array or Traversable.' );
		}

		foreach ( $checks as $key => $check ) {
			if ( ! $check instanceof CheckInterface ) {
				throw new InvalidArgumentException( 'Checks must implement the CheckInterface.' );
			}
			$alias = is_string( $key ) ? $key : null;
			$this->addCheck( $check, $alias );
		}
	}

	/**
	 * Get a single Check instance by its alias name
	 *
	 * @param  string $alias Alias name of the Check instance to retrieve
	 * @throws \RuntimeException When check is not found.
	 * @return CheckInterface
	 */
	public function getCheck( $alias ) {
		if ( empty( $this->checks[ $alias ] ) ) {
			throw new RuntimeException(
				sprintf(
					'There is no check instance with an alias of "%s"',
					$alias
				)
			);
		}

		return $this->checks[ $alias ];
	}

	/**
	 * @return ArrayObject
	 */
	public function getChecks() {
		return $this->checks;
	}

	/**
	 * @return array
	 */
	public function getResults() {
		return $this->results;
	}

	/**
	 * Set if checking should abort on first failure.
	 *
	 * @param boolean $breakOnFailure
	 */
	public function setBreakOnFailure( $breakOnFailure ) {
		$this->breakOnFailure = (bool) $breakOnFailure;
	}

	/**
	 * @return boolean
	 */
	public function getBreakOnFailure() {
		return $this->breakOnFailure;
	}

	/**
	 * Run checks and determine if all requirements have been satisfied.
	 *
	 * @return bool
	 */
	public function satisfied() {

		$checks  = $this->getChecks();
		$results = [];

		/** @var CheckInterface $check */
		foreach ( $checks as $alias => $check ) {
			try {
				$result = $check->check();
			} catch ( ErrorException $e ) {
				$result = new WP_Error( $e->getCode(), $e->getMessage() );
			} catch ( Exception $e ) {
				$result = new WP_Error( $e->getCode(), $e->getMessage() );
			}

			if ( is_wp_error( $result ) ) {
				$results[] = $result;

				if ( $this->getBreakOnFailure() ) {
					break;
				}
			}
		}

		if ( ! empty( $results ) ) {
			$this->results = $results;
			return false;
		}

		return true;

	}

	/**
	 * Print the notice when there are errors.
	 *
	 * @return void
	 */
	public function printNotice() {
		if ( empty( $this->results ) ) {
			return;
		}
		add_action( 'admin_notices', array( $this, 'notice' ) );
		add_action( 'admin_notices', array( $this, 'deactivate' ) );
	}

	/**
	 * Displays the content of the notice.
	 *
	 * @return void
	 */
	public function notice() {

		$results        = $this->getResults();
		$breakOnFailure = $this->getBreakOnFailure();

		?>
		<div class="notice notice-error is-dismissible">
			<p><strong><?php echo esc_html( sprintf( $this->getMessage( 'title' ), $this->getTitle() ) ); ?></strong></p>
			<?php if ( $breakOnFailure ) : ?>
				<p><?php echo esc_html( current( $results )->get_error_message() ); ?></p>
			<?php else : ?>
				<ol>
					<?php foreach ( $results as $error ) : ?>
						<li><?php echo esc_html( $error->get_error_message() ); ?></li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Define the list of default error messages.
	 *
	 * @return array
	 */
	private function getDefaultMessages() {

		return [
			'title'                   => 'The &#8220;%1$s&#8221; plugin could not be activated.',
			'class_exists'            => 'Class &#8220;%1$s&#8221; does not exist.',
			'class_exists_plural'     => 'The following classes are missing: %1$s.',
			'extension_loaded'        => 'Extension &#8220;%1$s&#8221; is not available.',
			'extension_loaded_plural' => 'Extensions &#8220;%1$s&#8221; are not available.',
			'php_version'             => 'Current PHP version is %s, expected %s %s. Please contact your host and ask them to upgrade.',
			'wp_version'              => 'Current WordPress version is %s, expected %s %s. Please update your WordPress.',
			'plugin_active'           => 'The &#8220;%1$s&#8221; plugin is missing. Please activate this plugin first.',
		];

	}

	/**
	 * Set messages of the runner.
	 *
	 * @param array $i18n
	 * @return void
	 */
	public function setMessages( $i18n = [] ) {
		$this->i18n = wp_parse_args( $i18n, $this->getDefaultMessages() );
	}

	/**
	 * Return a specific message.
	 *
	 * @param string $key
	 * @return string
	 */
	public function getMessage( $key ) {
		return $this->i18n[ $key ];
	}

	/**
	 * Set the title of the plugin using the library.
	 *
	 * @param string $title
	 * @return void
	 */
	public function setTitle( $title ) {
		$this->title = $title;
	}

	/**
	 * Get the title of the plugin using the library.
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Deactivates the plugin again.
	 *
	 * @access public
	 */
	public function deactivate() {
		if ( null !== $this->file ) {
			deactivate_plugins( plugin_basename( $this->file ) );
		}
	}
}
