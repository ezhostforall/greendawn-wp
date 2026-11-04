<?php
/**
 * Optional React example. Nothing here loads React unless the template part is rendered.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode wrapper: [greendawn_react_example label="Count" start="3"].
 *
 * @param array<string,string>|string $atts Shortcode attributes.
 * @return string
 */
function greendawn_react_example_shortcode( $atts ): string {
	$atts = shortcode_atts(
		array(
			'label' => __( 'Count', 'greendawn' ),
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
add_shortcode( 'greendawn_react_example', 'greendawn_react_example_shortcode' );
