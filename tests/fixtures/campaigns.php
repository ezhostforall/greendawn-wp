<?php
/**
 * Plugin Name: Disposable Workplace Charging Scheme campaign scenarios
 */

if ( ! defined( 'ABSPATH' ) || 'local' !== wp_get_environment_type() || 'http://localhost:8080' !== home_url() ) {
	return;
}

add_filter(
	'pre_option_greendawn_core_featured_campaign',
	static function ( $option ) {
		if ( ! isset( $_COOKIE['gd_test_campaign'] ) ) {
			return $option;
		}
		$post = get_page_by_path( 'workplace-charging-grant', OBJECT, 'gdcampaign' );
		return $post->ID ?? 0;
	}
);

add_filter(
	'greendawn_core_campaign_time',
	static function ( int $now ): int {
		return match ( $_COOKIE['gd_test_campaign'] ?? '' ) {
			'live' => strtotime( '2027-01-15T12:00:00Z' ),
			'late' => strtotime( '2027-02-01T00:00:00Z' ),
			'closed' => strtotime( '2027-03-31T23:00:00Z' ),
			default => $now,
		};
	}
);
