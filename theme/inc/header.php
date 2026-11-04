<?php
/**
 * Optional header presentation settings. Menus remain core WordPress menus.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers opt-in header settings in the core Customizer.
 *
 * @param WP_Customize_Manager $customizer Customizer instance.
 */
function greendawn_customize_header( WP_Customize_Manager $customizer ): void {
	$customizer->add_section(
		'greendawn_header',
		array(
			'title'       => __( 'Header', 'greendawn' ),
			'description' => __( 'Assign primary and optional utility links under Menus. The utility menu shows one level.', 'greendawn' ),
		)
	);
	$customizer->add_setting(
		'greendawn_header_sticky',
		array(
			'default'           => false,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);
	$customizer->add_control(
		'greendawn_header_sticky',
		array(
			'section' => 'greendawn_header',
			'type'    => 'checkbox',
			'label'   => __( 'Keep the header visible while scrolling', 'greendawn' ),
		)
	);
	foreach ( array( 'label', 'url' ) as $field ) {
		$setting = 'greendawn_header_cta_' . $field;
		$customizer->add_setting(
			$setting,
			array(
				'default'           => '',
				'sanitize_callback' => 'url' === $field ? 'esc_url_raw' : 'sanitize_text_field',
			)
		);
		$customizer->add_control(
			$setting,
			array(
				'section' => 'greendawn_header',
				'type'    => 'url' === $field ? 'url' : 'text',
				'label'   => 'url' === $field ? __( 'Call-to-action destination', 'greendawn' ) : __( 'Call-to-action text', 'greendawn' ),
			)
		);
	}
}
add_action( 'customize_register', 'greendawn_customize_header' );

/**
 * Leave image priority to the business homepage hero when it has a featured image.
 *
 * @param array $attributes Custom logo image attributes.
 * @return array
 */
function greendawn_home_logo_priority( array $attributes ): array {
	if ( is_page_template( 'templates/business-homepage.php' ) && has_post_thumbnail( get_queried_object_id() ) ) {
		$attributes['fetchpriority'] = 'auto';
	}
	return $attributes;
}
add_filter( 'get_custom_logo_image_attributes', 'greendawn_home_logo_priority' );
