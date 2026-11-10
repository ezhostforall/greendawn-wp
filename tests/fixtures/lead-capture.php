<?php
/**
 * Plugin Name: Disposable callback integration scenarios
 * Description: Never packaged or deployed. Intercepts all test email delivery.
 */

if ( ! defined( 'ABSPATH' ) || 'local' !== wp_get_environment_type() || 'http://localhost:8080' !== home_url() ) {
	return;
}

add_filter( 'greendawn_lead_config', static fn( $config ) => ( is_page( array( 'callback-home', 'callback-inner', 'enquire' ) ) || is_singular( 'evcharger' ) ) ? $config : array(), 30 );
add_filter( 'theme_mod_greendawn_header_cta_url', static fn( $value ) => is_page( array( 'callback-home', 'callback-inner' ) ) ? home_url( '/about/' ) : $value, 30 );
add_filter( 'theme_mod_greendawn_footer_cta_url', static fn( $value ) => is_page( array( 'callback-home', 'callback-inner' ) ) ? home_url( '/about/' ) : $value, 30 );
add_filter( 'theme_mod_greendawn_footer_cta_label', static fn( $value ) => is_page( array( 'callback-home', 'callback-inner' ) ) ? 'Discuss your site' : $value, 30 );
add_filter( 'pre_wp_mail', static function ( $result, $atts ) {
	update_option( 'greendawn_test_callback_mail', $atts, false );
	return ! str_contains( $atts['message'], 'failure@example.test' );
}, 99, 2 );

add_action( 'rest_api_init', static function () {
	register_rest_route( 'greendawn-test/v1', '/callback', array(
		'methods' => 'GET',
		'permission_callback' => '__return_true',
		'callback' => static fn() => get_option( 'greendawn_test_callback_mail', array() ),
	) );
	register_rest_route( 'greendawn-test/v1', '/callback/reset', array(
		'methods' => 'POST',
		'permission_callback' => '__return_true',
		'callback' => static function () {
			global $wpdb;
			delete_option( 'greendawn_test_callback_mail' );
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_greendawn_callback_%' OR option_name LIKE '_transient_timeout_greendawn_callback_%'" );
			return true;
		},
	) );
} );
