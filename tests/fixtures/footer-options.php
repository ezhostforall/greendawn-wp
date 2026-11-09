<?php
/**
 * Plugin Name: Disposable footer test scenarios
 * Description: Copied only into the disposable WordPress CI environment.
 */

if ( ! defined( 'ABSPATH' ) || 'local' !== wp_get_environment_type() || 'http://localhost:8080' !== home_url() ) {
	return;
}

$greendawn_footer_test_values = array(
	'summary'           => 'Commercial charging, coordinated.',
	'company_heading'   => 'About the company',
	'solutions_heading' => 'Charging services',
	'support_heading'   => 'Help and guidance',
	'contact_heading'   => 'Speak to us',
	'phone'             => '+44 345 512 0044',
	'email'             => 'team@example.test',
	'contact'           => "Fixture House <em>registered office</em>\nBirmingham\nB1 1AA",
	'cta_label'         => 'Ask our team',
	'cta_url'           => 'https://greendawn.co.uk/contact/?source=footer#enquiry',
	'legal_name'        => 'Fixture Charging Limited',
	'company_number'    => '12345678',
	'vat_number'        => '',
);
foreach ( $greendawn_footer_test_values as $greendawn_footer_test_key => $greendawn_footer_test_value ) {
	add_filter(
		'theme_mod_greendawn_footer_' . $greendawn_footer_test_key,
		static function ( $value ) use ( $greendawn_footer_test_key, $greendawn_footer_test_value ) {
			if ( is_page( 'footer-empty' ) ) {
				return '';
			}
			if ( is_page( 'footer-long' ) ) {
				$long = array(
					'summary'    => 'A coordinated commercial charging programme for a long and varied estate.',
					'email'      => 'commercial.charging.programme.enquiries@example.test',
					'contact'    => "An exceptionally long registered address for a multi-site programme\n" . str_repeat( 'Address', 15 ),
					'legal_name' => str_repeat( 'LongCompanyName', 12 ),
					'cta_url'    => '',
				);
				return $long[ $greendawn_footer_test_key ] ?? $greendawn_footer_test_value;
			}
			return is_page( 'footer-options' ) ? $greendawn_footer_test_value : $value;
		}
	);
}
add_filter(
	'theme_mod_nav_menu_locations',
	static function ( $locations ) {
		if ( is_page( array( 'footer-options', 'footer-long', 'footer-no-core' ) ) ) {
			return array_merge( $locations, get_option( 'greendawn_footer_test_menus', array() ) );
		}
		return $locations;
	}
);
add_filter( 'greendawn_footer_defaults', static fn( $defaults ) => is_page( 'footer-no-core' ) ? array_fill_keys( array_keys( $defaults ), '' ) : $defaults, 20 );
add_filter( 'greendawn_footer_links', static fn( $links ) => is_page( array( 'footer-empty', 'footer-no-core' ) ) ? array() : $links, 20 );

// Exercise a setting change after the homepage field schema has been cached.
add_action(
	'wp',
	static function () {
		if ( is_page( 'greendawn-home-global-phone' ) ) {
			apply_filters( 'greendawn_home_field_value', '', 'headline' );
			add_filter( 'theme_mod_greendawn_footer_phone', static fn() => '0300 123 4567', 20 );
		}
	}
);
