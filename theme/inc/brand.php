<?php
/**
 * Site-local brand overrides for shared theme.json roles.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lists the palette roles exposed in the Customizer.
 *
 * @return string[]
 */
function wp_starter_brand_colour_roles(): array {
	return array( 'text', 'heading', 'background', 'surface', 'muted', 'accent', 'link', 'focus', 'border', 'on-accent', 'accent-hover' );
}

/**
 * Lists the active theme's font presets by slug.
 *
 * @param array $data Theme JSON data.
 * @return array<string, string>
 */
function wp_starter_brand_fonts( array $data ): array {
	$fonts = array();
	foreach ( $data['settings']['typography']['fontFamilies']['theme'] ?? array() as $font ) {
		if ( ! empty( $font['name'] ) && preg_match( '/^[a-z0-9-]+$/D', $font['slug'] ?? '' ) ) {
			$fonts[ $font['slug'] ] = $font['name'];
		}
	}
	return $fonts;
}

/**
 * Merges saved and previewed values into the theme's global styles.
 *
 * @param WP_Theme_JSON_Data $theme_json Active theme data.
 * @return WP_Theme_JSON_Data
 */
function wp_starter_apply_brand( WP_Theme_JSON_Data $theme_json ): WP_Theme_JSON_Data {
	$data     = $theme_json->get_data();
	$palette  = $data['settings']['color']['palette']['theme'] ?? array();
	$settings = array();
	$changed  = false;

	foreach ( $palette as &$colour ) {
		if ( in_array( $colour['slug'], wp_starter_brand_colour_roles(), true ) ) {
			$value = sanitize_hex_color( (string) get_theme_mod( 'wp_starter_brand_colour_' . $colour['slug'], '' ) );
			if ( $value ) {
				$colour['color'] = $value;
				$changed         = true;
			}
		}
	}
	unset( $colour );

	if ( $changed ) {
		$settings['color']['palette'] = $palette;
	}

	$fonts = wp_starter_brand_fonts( $data );
	foreach ( array( 'body', 'heading' ) as $role ) {
		$value = (string) get_theme_mod( 'wp_starter_brand_font_' . $role, '' );
		if ( isset( $fonts[ $value ] ) ) {
			$settings['custom']['brand'][ $role ] = 'var(--wp--preset--font-family--' . $value . ')';
		}
	}

	if ( $settings ) {
		$theme_json->update_with(
			array(
				'version'  => 3,
				'settings' => $settings,
			)
		);
	}
	return $theme_json;
}
add_filter( 'wp_theme_json_data_theme', 'wp_starter_apply_brand' );

/**
 * Registers administrator-only brand controls.
 *
 * @param WP_Customize_Manager $customizer Customizer instance.
 */
function wp_starter_customize_brand( WP_Customize_Manager $customizer ): void {
	$customizer->add_section(
		'wp_starter_brand',
		array(
			'title'       => __( 'Brand', 'wp-starter' ),
			'capability'  => 'manage_options',
			'description' => __( 'Clear a colour or choose Theme default for a font to restore that setting. Check text, links, buttons and focus indicators for contrast before publishing.', 'wp-starter' ),
		)
	);

	$data     = WP_Theme_JSON_Resolver::get_theme_data()->get_raw_data();
	$settings = array();
	foreach ( $data['settings']['color']['palette']['theme'] ?? array() as $colour ) {
		if ( ! in_array( $colour['slug'], wp_starter_brand_colour_roles(), true ) ) {
			continue;
		}

		$id = 'wp_starter_brand_colour_' . $colour['slug'];
		$customizer->add_setting(
			$id,
			array(
				'default'           => '',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);
		$customizer->add_control(
			new WP_Customize_Color_Control(
				$customizer,
				$id,
				array(
					'label'   => $colour['name'],
					'section' => 'wp_starter_brand',
				)
			)
		);
		$settings[] = $id;
	}

	$choices = array( '' => __( 'Theme default', 'wp-starter' ) ) + wp_starter_brand_fonts( $data );
	$labels  = array(
		'body'    => __( 'Body font', 'wp-starter' ),
		'heading' => __( 'Heading font', 'wp-starter' ),
	);

	foreach ( $labels as $role => $label ) {
		$id = 'wp_starter_brand_font_' . $role;
		$customizer->add_setting(
			$id,
			array(
				'default'           => '',
				'capability'        => 'manage_options',
				'sanitize_callback' => static function ( $value ) use ( $choices ) {
					return is_string( $value ) && isset( $choices[ $value ] ) ? $value : null;
				},
			)
		);
		$customizer->add_control(
			$id,
			array(
				'type'    => 'select',
				'label'   => $label,
				'choices' => $choices,
				'section' => 'wp_starter_brand',
			)
		);
		$settings[] = $id;
	}

	add_action(
		'customize_controls_enqueue_scripts',
		static function () use ( $settings ): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$config = wp_json_encode(
				array(
					'ids'   => $settings,
					'label' => __( 'Reset brand styles', 'wp-starter' ),
				),
				JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
			);
			wp_add_inline_script(
				'customize-controls',
				'(function (config) {
					wp.customize.control(config.ids[0], function (control) {
						control.deferred.embedded.done(function () {
							const item = document.createElement("li");
							const button = document.createElement("button");
							item.className = "customize-control";
							button.type = "button";
							button.className = "button";
							button.textContent = config.label;
							button.addEventListener("click", function () {
								config.ids.forEach(function (id) { wp.customize(id).set(""); });
							});
							item.append(button);
							control.container[0].before(item);
						});
					});
				})(' . $config . ');'
			);
		}
	);
}
add_action( 'customize_register', 'wp_starter_customize_brand' );

// Rebuild the theme data after Customizer settings begin filtering theme mods.
add_action( 'customize_preview_init', array( 'WP_Theme_JSON_Resolver', 'clean_cached_data' ), 0 );
