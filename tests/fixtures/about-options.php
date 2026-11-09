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
		if ( is_page( 'gd-about-no-core' ) ) {
			remove_all_filters( 'greendawn_about_field_value' );
		}
	}
);
