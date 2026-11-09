<?php
/**
 * Plugin Name: Greendawn disposable media fixture
 * Description: Simulates missing media on the designated integration-test pages only.
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
		if ( in_array( $name, array( 'insight_power_image', 'insight_survey_image', 'insight_fleet_image' ), true ) && is_page( 'greendawn-home-no-insight-images' ) ) {
			return 0;
		}
		return $value;
	},
	20,
	2
);
