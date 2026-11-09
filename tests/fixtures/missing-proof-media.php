<?php
/**
 * Plugin Name: Greendawn disposable media fixture
 * Description: Simulates missing media for one integration-test page only.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'greendawn_home_field_value',
	static function ( $value, string $name ) {
		if ( 'proof_image' === $name && is_page( 'greendawn-home-no-proof-image' ) ) {
			return 0;
		}
		return $value;
	},
	20,
	2
);
