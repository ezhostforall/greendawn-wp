<?php
/**
 * Optional header presentation settings. Menus remain core WordPress menus.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/** Returns the original utility-bar wording. */
function greendawn_utility_text_default(): string {
	return __( 'Commercial EV infrastructure across England, Scotland & Wales', 'greendawn' );
}

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
			'section'     => 'greendawn_header',
			'type'        => 'checkbox',
			'label'       => __( 'Keep the header visible while scrolling', 'greendawn' ),
			'description' => __( 'The main header becomes compact after scrolling. The utility bar scrolls with the page.', 'greendawn' ),
		)
	);
	$utility_setting = 'greendawn_header_utility_text';
	$utility_default = greendawn_utility_text_default();
	$customizer->add_setting(
		$utility_setting,
		array(
			'default'           => $utility_default,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$customizer->add_control(
		$utility_setting,
		array(
			'section' => 'greendawn_header',
			'type'    => 'text',
			'label'   => __( 'Utility bar text', 'greendawn' ),
		)
	);
	add_action(
		'customize_controls_enqueue_scripts',
		static function () use ( $utility_setting, $utility_default ): void {
			$config = wp_json_encode(
				array(
					'id'    => $utility_setting,
					'value' => $utility_default,
					'label' => __( 'Reset utility bar text', 'greendawn' ),
				),
				JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
			);
			wp_add_inline_script(
				'customize-controls',
				'(function (config) {
					wp.customize.control(config.id, function (control) {
						control.deferred.embedded.done(function () {
							const button = document.createElement("button");
							button.type = "button";
							button.className = "button";
							button.textContent = config.label;
							button.addEventListener("click", function () { control.setting.set(config.value); });
							control.container[0].append(button);
						});
					});
				})(' . $config . ');'
			);
		}
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
	$business_hero = is_page_template( 'templates/business-homepage.php' ) && has_post_thumbnail( get_queried_object_id() );
	if ( is_page_template( 'templates/greendawn-homepage.php' ) || $business_hero ) {
		$attributes['fetchpriority'] = 'auto';
	}
	return $attributes;
}
add_filter( 'get_custom_logo_image_attributes', 'greendawn_home_logo_priority' );
