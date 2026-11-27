<?php
/**
 * Public, bounded Guide discovery. No global search filters or public endpoints.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/** Query public records and the Core-owned plain-text projection. */
final class Guide_Discovery {
	/**
	 * Literal substring search, including punctuation and SQL wildcard characters.
	 *
	 * @param string $search Search text.
	 * @param string $topic Optional exact topic slug.
	 * @param int    $page Result page.
	 */
	public static function search( string $search = '', string $topic = '', int $page = 1 ): \WP_Query {
		$args   = array(
			'post_type'           => Guides::TYPE,
			'post_status'         => 'publish',
			'has_password'        => false,
			'posts_per_page'      => 24,
			'paged'               => max( 1, min( 10000, $page ) ),
			'orderby'             => array(
				'title' => 'ASC',
				'ID'    => 'ASC',
			),
			'ignore_sticky_posts' => true,
		);
		$search = mb_substr( sanitize_text_field( $search ), 0, 200 );
		if ( '' !== $search ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Explicit bounded Guides search.
			$args['meta_query'] = array(
				array(
					'key'     => Guide_Content::SEARCH_META,
					'value'   => $search,
					'compare' => 'LIKE',
				),
			);
		}
		if ( '' !== $topic ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Explicit Guides topic filter.
			$args['tax_query'] = array(
				array(
					'taxonomy'         => Guides::TAXONOMY,
					'field'            => 'slug',
					'terms'            => sanitize_title( $topic ),
					'include_children' => false,
				),
			);
		}
		return new \WP_Query( $args );
	}

	/**
	 * Live counts exclude non-public records, independently of native term counts.
	 *
	 * @param int[] $order Editor-selected order, followed by remaining topic names.
	 */
	public static function topics( array $order = array() ): array {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Small live aggregate; reflect publication, password and term changes immediately.
		$counts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT tt.term_id, COUNT(DISTINCT p.ID) AS total FROM {$wpdb->term_taxonomy} tt INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id WHERE tt.taxonomy = %s AND p.post_type = %s AND p.post_status = 'publish' AND p.post_password = '' GROUP BY tt.term_id",
				Guides::TAXONOMY,
				Guides::TYPE
			),
			OBJECT_K
		);
		if ( ! $counts ) {
			return array();
		}
		$terms = get_terms(
			array(
				'taxonomy'   => Guides::TAXONOMY,
				'include'    => array_keys( $counts ),
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);
		if ( is_wp_error( $terms ) ) {
			return array();
		}
		$topics = array();
		foreach ( $terms as $term ) {
			$topics[ $term->term_id ] = array(
				'id'    => $term->term_id,
				'slug'  => $term->slug,
				'name'  => $term->name,
				'count' => (int) $counts[ $term->term_id ]->total,
			);
		}
		$ordered = array();
		foreach ( $order as $id ) {
			if ( isset( $topics[ $id ] ) ) {
				$ordered[] = $topics[ $id ];
				unset( $topics[ $id ] );
			}
		}
		return array_merge( $ordered, array_values( $topics ) );
	}
}
