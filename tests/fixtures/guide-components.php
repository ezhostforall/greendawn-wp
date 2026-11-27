<?php
/**
 * Plugin Name: Disposable shared reading component fixtures
 * Description: Never package or install on hosted staging or production.
 */

if ( ! defined( 'ABSPATH' ) || 'local' !== wp_get_environment_type() || 'http://localhost:8080' !== home_url() ) {
	return;
}

add_filter( 'template_include', static function ( $template ) {
	return is_page( array( 'reading-components', 'reading-components-empty', 'reading-components-legacy' ) ) ? __DIR__ . '/guide-components-template.php' : $template;
} );
add_filter( 'greendawn_page_entries', static function ( $entries ) {
	if ( is_page( array( 'reading-components', 'reading-components-empty', 'reading-components-legacy' ) ) ) { $entries[] = 'greendawn-reading'; }
	return $entries;
} );
