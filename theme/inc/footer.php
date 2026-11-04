<?php
/**
 * Footer content settings and menu rendering.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers optional footer content in the Customizer.
 *
 * @param WP_Customize_Manager $customizer Customizer instance.
 */
function wp_starter_customize_footer( WP_Customize_Manager $customizer ): void {
	$customizer->add_section(
		'wp_starter_footer',
		array(
			'title'       => __( 'Footer', 'wp-starter' ),
			'description' => __( 'Add footer content here and assign links under Menus.', 'wp-starter' ),
		)
	);

	$fields = array(
		'summary'   => array( __( 'Business summary', 'wp-starter' ), 'textarea', 'sanitize_textarea_field' ),
		'contact'   => array( __( 'Contact details', 'wp-starter' ), 'textarea', 'sanitize_textarea_field' ),
		'cta_label' => array( __( 'Call-to-action text', 'wp-starter' ), 'text', 'sanitize_text_field' ),
		'cta_url'   => array( __( 'Call-to-action destination', 'wp-starter' ), 'url', 'esc_url_raw' ),
	);

	foreach ( $fields as $key => list( $label, $type, $sanitize ) ) {
		$setting = 'wp_starter_footer_' . $key;
		$customizer->add_setting(
			$setting,
			array(
				'default'           => '',
				'sanitize_callback' => $sanitize,
			)
		);
		$customizer->add_control(
			$setting,
			array(
				'section' => 'wp_starter_footer',
				'label'   => $label,
				'type'    => $type,
			)
		);
	}
}
add_action( 'customize_register', 'wp_starter_customize_footer' );

/**
 * Renders an assigned footer menu.
 *
 * @param string $location Menu location.
 * @param string $label Accessible navigation label.
 */
function wp_starter_footer_menu( string $location, string $label ): void {
	if ( ! has_nav_menu( $location ) ) {
		return;
	}

	wp_nav_menu(
		array(
			'theme_location'       => $location,
			'container'            => 'nav',
			'container_class'      => 'footer-nav',
			'container_aria_label' => $label,
			'depth'                => 1,
			'fallback_cb'          => false,
		)
	);
}
