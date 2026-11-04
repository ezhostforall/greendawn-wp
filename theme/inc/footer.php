<?php
/**
 * Footer content settings and menu rendering.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers optional footer content in the Customizer.
 *
 * @param WP_Customize_Manager $customizer Customizer instance.
 */
function greendawn_customize_footer( WP_Customize_Manager $customizer ): void {
	$customizer->add_section(
		'greendawn_footer',
		array(
			'title'       => __( 'Footer', 'greendawn' ),
			'description' => __( 'Add footer content here and assign links under Menus.', 'greendawn' ),
		)
	);

	$fields = array(
		'summary'   => array( __( 'Business summary', 'greendawn' ), 'textarea', 'sanitize_textarea_field' ),
		'contact'   => array( __( 'Contact details', 'greendawn' ), 'textarea', 'sanitize_textarea_field' ),
		'cta_label' => array( __( 'Call-to-action text', 'greendawn' ), 'text', 'sanitize_text_field' ),
		'cta_url'   => array( __( 'Call-to-action destination', 'greendawn' ), 'url', 'esc_url_raw' ),
	);

	foreach ( $fields as $key => list( $label, $type, $sanitize ) ) {
		$setting = 'greendawn_footer_' . $key;
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
				'section' => 'greendawn_footer',
				'label'   => $label,
				'type'    => $type,
			)
		);
	}
}
add_action( 'customize_register', 'greendawn_customize_footer' );

/**
 * Renders an assigned footer menu.
 *
 * @param string $location Menu location.
 * @param string $label Accessible navigation label.
 */
function greendawn_footer_menu( string $location, string $label ): void {
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
