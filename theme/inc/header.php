<?php
/**
 * Optional header presentation settings. Menus remain core WordPress menus.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers opt-in header settings in the core Customizer.
 *
 * @param WP_Customize_Manager $customizer Customizer instance.
 */
function wp_starter_customize_header( WP_Customize_Manager $customizer ): void {
	$customizer->add_section(
		'wp_starter_header',
		array(
			'title'       => __( 'Header', 'wp-starter' ),
			'description' => __( 'Assign primary and optional utility links under Menus. The utility menu shows one level.', 'wp-starter' ),
		)
	);
	$customizer->add_setting(
		'wp_starter_header_sticky',
		array(
			'default'           => false,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);
	$customizer->add_control(
		'wp_starter_header_sticky',
		array(
			'section' => 'wp_starter_header',
			'type'    => 'checkbox',
			'label'   => __( 'Keep the header visible while scrolling', 'wp-starter' ),
		)
	);
	foreach ( array( 'label', 'url' ) as $field ) {
		$setting = 'wp_starter_header_cta_' . $field;
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
				'section' => 'wp_starter_header',
				'type'    => 'url' === $field ? 'url' : 'text',
				'label'   => 'url' === $field ? __( 'Call-to-action destination', 'wp-starter' ) : __( 'Call-to-action text', 'wp-starter' ),
			)
		);
	}
}
add_action( 'customize_register', 'wp_starter_customize_header' );

/**
 * Leave image priority to the business homepage hero when it has a featured image.
 *
 * @param array $attributes Custom logo image attributes.
 * @return array
 */
function wp_starter_home_logo_priority( array $attributes ): array {
	if ( is_page_template( 'templates/business-homepage.php' ) && has_post_thumbnail( get_queried_object_id() ) ) {
		$attributes['fetchpriority'] = 'auto';
	}
	return $attributes;
}
add_filter( 'get_custom_logo_image_attributes', 'wp_starter_home_logo_priority' );
