<?php
/**
 * Plugin Name: Disposable About template scenarios
 *
 * @package Greendawn
 */

if ( ! defined( 'ABSPATH' ) || 'local' !== wp_get_environment_type() ) {
	return;
}
add_action(
	'wp',
	static function (): void {
		if ( is_page( 'gd-enquire-no-core' ) ) {
			remove_all_filters( 'greendawn_enquire_field_value' );
		}
		if ( is_page( 'gd-enquire-no-cf7' ) ) {
			remove_shortcode( 'contact-form-7' );
		}
		if ( is_page( 'gd-enquire-contacts-cleared' ) ) {
			foreach ( array( 'phone', 'email', 'contact' ) as $name ) {
				add_filter( 'theme_mod_greendawn_footer_' . $name, static fn() => '' );
			}
		}
		if ( is_page( 'gd-enquire-shared-contact' ) ) {
			add_filter( 'theme_mod_greendawn_footer_phone', static fn() => '0300 123 4567' );
			add_filter( 'theme_mod_greendawn_footer_email', static fn() => 'changed@example.test' );
			add_filter( 'theme_mod_greendawn_footer_contact', static fn() => "Shared Office\nKeele\nST5 5NU" );
		}
		if ( is_page( 'gd-enquire-long-contact' ) ) {
			add_filter( 'theme_mod_greendawn_footer_phone', static fn() => 'Ask the team' );
			add_filter( 'theme_mod_greendawn_footer_email', static fn() => 'not-an-email' );
			add_filter( 'theme_mod_greendawn_footer_contact', static fn() => str_repeat( 'LongAddress', 20 ) . '<script>discard</script>' );
		}
		if ( is_page( 'gd-aftercare-no-core' ) ) {
			remove_all_filters( 'greendawn_aftercare_field_value' );
		}
		if ( is_page( 'gd-aftercare-no-cf7' ) ) {
			remove_shortcode( 'contact-form-7' );
		}
		if ( is_page( 'gd-public-no-core' ) ) {
			remove_all_filters( 'greendawn_public_field_value' );
		}
		if ( is_page( 'gd-fleet-no-core' ) ) {
			remove_all_filters( 'greendawn_fleet_field_value' );
		}
		if ( is_page( 'gd-workplace-no-core' ) ) {
			remove_all_filters( 'greendawn_workplace_field_value' );
		}
		if ( is_page( 'gd-about-no-core' ) ) {
			remove_all_filters( 'greendawn_about_field_value' );
		}
	}
);
