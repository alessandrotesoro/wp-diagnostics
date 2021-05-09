<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Check the existance of plugins.
 *
 * @package   wp-diagnostics
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

namespace Sematico\Diagnostics\Check;

use WP_Error;

/**
 * Determine if one plugins exist.
 */
class PluginActive extends AbstractCheck implements CheckInterface {

	/**
	 * Plugin to check.
	 *
	 * @var array
	 */
	protected $plugin;

	/**
	 * Name of the plugin
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Setup the check.
	 *
	 * @param string $plugin directory/entry-file.php name of plugin to check.
	 * @param string $name Human readable title of the plugin to check.
	 */
	public function __construct( $plugin, $name ) {
		$this->plugin = $plugin;
		$this->name   = $name;
	}

	/**
	 * Perform the check.
	 *
	 * @return bool|\WP_Error
	 */
	public function check() {

		if ( ! in_array( $this->plugin, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
			return new WP_Error( 'plugin-missing', sprintf( $this->getRunner()->getMessage( 'plugin_active' ), $this->name ) );
		}

		return true;
	}

}
