<?php
/**
 * Block pattern support. Pattern files in /patterns are registered automatically by core.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the pattern category used by the bundled example patterns.
 */
function wp_starter_register_pattern_category(): void {
	register_block_pattern_category(
		'theme-examples',
		array( 'label' => __( 'Starter', 'wp-starter' ) )
	);
}
add_action( 'init', 'wp_starter_register_pattern_category' );
