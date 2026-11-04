<?php
/**
 * Block pattern support. Pattern files in /patterns are registered automatically by core.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the pattern category used by the bundled example patterns.
 */
function greendawn_register_pattern_category(): void {
	register_block_pattern_category(
		'theme-examples',
		array( 'label' => __( 'Starter', 'greendawn' ) )
	);
}
add_action( 'init', 'greendawn_register_pattern_category' );
