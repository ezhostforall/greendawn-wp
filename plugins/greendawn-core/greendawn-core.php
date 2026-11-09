<?php
/**
 * Plugin Name: Greendawn Core
 * Description: Optional, modular foundation for reusable agency functionality. Does nothing until modules are enabled.
 * Version: 0.6.14
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: Greendawn
 * Author URI: https://greendawn.co.uk/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://updates.ezhostforall.invalid/greendawn-core
 * Text Domain: greendawn-core
 *
 * @package Greendawn_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'GREENDAWN_CORE_VERSION', '0.6.14' );
define( 'GREENDAWN_CORE_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Minimal PSR-4 style autoloader: Greendawn_Core\Modules\Diagnostics => includes/Modules/Diagnostics.php.
 */
spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = 'Greendawn_Core\\';
		if ( ! str_starts_with( $class_name, $prefix ) ) {
			return;
		}
		$relative = str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) );
		$file     = GREENDAWN_CORE_DIR . 'includes/' . $relative . '.php';
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

add_action( 'plugins_loaded', array( \Greendawn_Core\Plugin::class, 'boot' ) );
