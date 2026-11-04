<?php
/**
 * Bootstrap for pure-PHP unit tests (no WordPress required). Integration behaviour is
 * covered by the Playwright suite against a real WordPress install.
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'GREENDAWN_DIR', dirname( __DIR__, 2 ) . '/theme' );

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';
require_once GREENDAWN_DIR . '/inc/vite.php';

// WordPress error object is minimal in pure unit tests; browser tests exercise WordPress itself.
if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		private string $code;
		public function __construct( string $code, string $message = '' ) {
			$this->code = $code;
		}
		public function get_error_code(): string {
			return $this->code;
		}
	}
}
