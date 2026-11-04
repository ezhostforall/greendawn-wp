<?php
/**
 * Plugin Name: Agency Core
 * Description: Optional, modular foundation for reusable agency functionality. Does nothing until modules are enabled.
 * Version: 0.5.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: EZ Host For All
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://updates.ezhostforall.invalid/agency-core
 * Text Domain: agency-core
 *
 * @package Agency_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'AGENCY_CORE_VERSION', '0.5.0' );
define( 'AGENCY_CORE_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Minimal PSR-4 style autoloader: Agency_Core\Modules\Diagnostics => includes/Modules/Diagnostics.php.
 */
spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = 'Agency_Core\\';
		if ( ! str_starts_with( $class_name, $prefix ) ) {
			return;
		}
		$relative = str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) );
		$file     = AGENCY_CORE_DIR . 'includes/' . $relative . '.php';
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

add_action( 'plugins_loaded', array( \Agency_Core\Plugin::class, 'boot' ) );
