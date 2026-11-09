<?php
/**
 * Data adapters for fixed Greendawn page components.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gets raw About content from Core, retaining native metadata without SCF.
 *
 * @param string $name Short field name.
 * @param mixed  $fallback Value when the content model is unavailable.
 * @return mixed
 */
function greendawn_about_field( string $name, $fallback = '' ) {
	$id    = get_the_ID();
	$value = metadata_exists( 'post', $id, 'greendawn_about_' . $name ) ? get_post_meta( $id, 'greendawn_about_' . $name, true ) : $fallback;
	return apply_filters( 'greendawn_about_field_value', $value, $name, $id );
}

/**
 * Returns published relationship records in the editor-selected order.
 *
 * @param mixed  $selected Selected IDs.
 * @param string $type Allowed post type.
 * @return WP_Post[]
 */
function greendawn_page_records( $selected, string $type ): array {
	if ( ! is_array( $selected ) ) {
		return array();
	}
	$ids = array_values( array_unique( array_filter( array_map( 'absint', $selected ) ) ) );
	return $ids ? get_posts(
		array(
			'post_type'      => $type,
			'post_status'    => 'publish',
			'has_password'   => false,
			'post__in'       => $ids,
			'orderby'        => 'post__in',
			'posts_per_page' => count( $ids ),
		)
	) : array();
}
