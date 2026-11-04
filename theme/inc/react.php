<?php
/**
 * Optional React example. Nothing here loads React unless the template part is rendered.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode wrapper: [wp_starter_react_example label="Count" start="3"].
 *
 * @param array<string,string>|string $atts Shortcode attributes.
 * @return string
 */
function wp_starter_react_example_shortcode( $atts ): string {
	$atts = shortcode_atts(
		array(
			'label' => __( 'Count', 'wp-starter' ),
			'start' => 0,
		),
		(array) $atts
	);

	ob_start();
	get_template_part(
		'template-parts/react-example',
		null,
		array(
			'label' => sanitize_text_field( $atts['label'] ),
			'start' => (int) $atts['start'],
		)
	);
	return (string) ob_get_clean();
}
add_shortcode( 'wp_starter_react_example', 'wp_starter_react_example_shortcode' );
