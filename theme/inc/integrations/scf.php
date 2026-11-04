<?php
/**
 * Secure Custom Fields (ACF-compatible API) helpers. Everything degrades gracefully when
 * the plugin is inactive.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Local JSON directory for field groups. Prefer pointing this at a site-specific plugin
 * via the `wp_starter_scf_json_path` filter so content models outlive the theme.
 */
function wp_starter_scf_json_path(): string {
	return (string) apply_filters( 'wp_starter_scf_json_path', WP_STARTER_DIR . '/acf-json' );
}

add_filter(
	'acf/settings/save_json',
	static function ( $path ) {
		$json_path = wp_starter_scf_json_path();
		return '' !== $json_path ? $json_path : $path;
	}
);

add_filter(
	'acf/settings/load_json',
	static function ( $paths ) {
		$paths[] = wp_starter_scf_json_path();
		return array_values( array_unique( $paths ) );
	}
);

/**
 * Returns a field value or a default. Never fatals if SCF is missing.
 *
 * @param string          $name    Field name.
 * @param int|string|bool $post_id Post ID, 'option', or false for the current post.
 * @param mixed           $fallback Returned when the field is missing or empty.
 * @return mixed Raw value: escape it on output (or use wp_starter_the_field()).
 */
function wp_starter_get_field( string $name, $post_id = false, $fallback = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $fallback;
	}
	$value = get_field( $name, $post_id );
	return ( null === $value || '' === $value || false === $value || array() === $value ) ? $fallback : $value;
}

/**
 * Echoes a scalar field value, escaped for its context.
 *
 * @param string          $name    Field name.
 * @param string          $context One of text, html, url, attr.
 * @param int|string|bool $post_id Post ID, 'option', or false for the current post.
 */
function wp_starter_the_field( string $name, string $context = 'text', $post_id = false ): void {
	$value = wp_starter_get_field( $name, $post_id );
	if ( ! is_scalar( $value ) ) {
		return;
	}
	$value = (string) $value;
	switch ( $context ) {
		case 'html':
			echo wp_kses_post( $value );
			break;
		case 'url':
			echo esc_url( $value );
			break;
		case 'attr':
			echo esc_attr( $value );
			break;
		default:
			echo esc_html( $value );
	}
}
