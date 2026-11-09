<?php
/**
 * Plugin Name: Disposable header test scenarios
 * Description: Copied only into the disposable WordPress CI environment.
 */

if ( ! defined( 'ABSPATH' ) || 'local' !== wp_get_environment_type() || 'http://localhost:8080' !== home_url() ) {
	return;
}

add_filter( 'theme_mod_greendawn_header_sticky', static fn( $value ) => is_page( 'header-options' ) ? true : $value );
add_filter( 'theme_mod_greendawn_header_cta_label', static fn( $value ) => is_page( 'header-options' ) ? 'Talk to us' : $value );
add_filter(
	'theme_mod_greendawn_header_cta_url',
	static function ( $value ) {
		if ( is_page( array( 'header-options', 'greendawn-home' ) ) ) {
			return home_url( '/about/' );
		}
		if ( is_page( 'greendawn-home-edited' ) ) {
			return home_url( '/contact/?from=home#enquiry' );
		}
		if ( is_page( 'greendawn-home-no-insight-images' ) ) {
			return '#';
		}
		return $value;
	}
);
add_filter(
	'theme_mod_nav_menu_locations',
	static function ( $locations ) {
		if ( is_page( 'header-empty' ) ) {
			return array();
		}
		if ( ! is_page( array( 'header-options', 'greendawn-home' ) ) ) {
			unset( $locations['utility'] );
		}
		return $locations;
	}
);
