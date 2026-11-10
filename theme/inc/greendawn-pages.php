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

/**
 * Preserves visible product context on the ordinary no-JavaScript enquiry destination.
 *
 * @param string $content Main page content.
 */
function greendawn_charger_enquiry_context( string $content ): string {
	if ( ! is_page( 'enquire' ) || ! is_main_query() || ! in_the_loop() ) {
		return $content;
	}
	$context = apply_filters( 'greendawn_selected_charger', array() );
	if ( empty( $context['id'] ) ) {
		return $content;
	}
	return '<aside class="charger-enquiry-context"><p><strong>' . esc_html__( 'Enquiry about:', 'greendawn' ) . '</strong> <a href="' . esc_url( $context['url'] ) . '">' . esc_html( $context['title'] ) . '</a></p></aside>' . $content;
}
add_filter( 'the_content', 'greendawn_charger_enquiry_context', 8 );

/**
 * Resolves independent Workplace content through Core with native metadata fallback.
 *
 * @param string $name Short field name.
 * @param mixed  $fallback Value without the content module.
 * @return mixed
 */
function greendawn_workplace_field( string $name, $fallback = '' ) {
	$id    = get_the_ID();
	$value = metadata_exists( 'post', $id, 'greendawn_workplace_' . $name ) ? get_post_meta( $id, 'greendawn_workplace_' . $name, true ) : $fallback;
	return apply_filters( 'greendawn_workplace_field_value', $value, $name, $id );
}
