<?php
/**
 * Presentation adapters for Guides and structured regional Pages.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read managed Page fields after the native password gate.
 *
 * @param int $id Current Page ID.
 */
function greendawn_reading_page_data( int $id ): array {
	return class_exists( '\Greendawn_Core\Modules\Guide_Content' ) && ! post_password_required( $id ) ? \Greendawn_Core\Modules\Guide_Content::read( $id ) : array();
}

/**
 * Authorised previews/password access remain separate from public discovery.
 *
 * @param int $id Current queried Guide ID.
 */
function greendawn_guide_template_data( int $id ): array {
	$data = greendawn_guide_view( $id );
	if ( $data || ! class_exists( '\Greendawn_Core\Modules\Guide_Content' ) || post_password_required( $id ) ) {
		return $data;
	}
	$post = get_post( $id );
	if ( ! $post || 'gdguide' !== $post->post_type || ! ( ( is_preview() && current_user_can( 'edit_post', $id ) ) || ( 'publish' === $post->post_status && '' !== $post->post_password ) || ( 'private' === $post->post_status && current_user_can( 'read_post', $id ) ) ) ) {
		return array();
	}
	$data = \Greendawn_Core\Modules\Guide_Content::read( $id );
	return array_merge(
		$data,
		array(
			'id'              => $id,
			'title'           => get_the_title( $id ),
			'author'          => \Greendawn_Core\Modules\Guide_Content::AUTHOR,
			'reading_minutes' => \Greendawn_Core\Modules\Guide_Content::reading_minutes( $data ),
		)
	);
}

/** Normal GET filters, separate from global blog search and pagination. */
function greendawn_guides_state(): array {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only public GET filters.
	$search = isset( $_GET['guide_search'] ) && is_string( $_GET['guide_search'] ) ? mb_substr( sanitize_text_field( wp_unslash( $_GET['guide_search'] ) ), 0, 200 ) : '';
	$topic  = isset( $_GET['guide_topic'] ) && is_string( $_GET['guide_topic'] ) ? sanitize_title( wp_unslash( $_GET['guide_topic'] ) ) : '';
	$page   = isset( $_GET['guide_page'] ) && is_string( $_GET['guide_page'] ) ? max( 1, min( 10000, absint( $_GET['guide_page'] ) ) ) : 1;
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
	return array(
		'search' => $search,
		'topic'  => $topic,
		'page'   => $page,
	);
}

/** Page-owned hub destination; the theme creates no assumed route. */
function greendawn_guides_hub_url(): string {
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'has_password'   => false,
			'posts_per_page' => 1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Only the opt-in reading templates use this.
			'meta_key'       => '_wp_page_template',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Published hub template lookup.
			'meta_value'     => 'templates/greendawn-guides.php',
		)
	);
	return $pages ? get_permalink( $pages[0] ) : '';
}

/**
 * Shared closing copy, only when supplied by the owning record.
 *
 * @param array $data Normalised content fields.
 */
function greendawn_reading_cta( array $data ): void {
	if ( empty( $data['cta_heading'] ) ) {
		return;
	}
	get_template_part(
		'template-parts/components/cta-band',
		null,
		array(
			'heading'     => $data['cta_heading'],
			'copy'        => $data['cta_copy'] ?? '',
			'label'       => $data['cta_label'] ?? '',
			'action'      => 'enquiry',
			'entry_point' => 'final-cta',
		)
	);
}

/**
 * Curated related Guides, in saved order and public only.
 *
 * @param array $ids Selected IDs.
 */
function greendawn_reading_related( array $ids ): void {
	$posts = greendawn_page_records( $ids, 'gdguide' );
	if ( $posts ) {
		get_template_part(
			'template-parts/components/articles',
			null,
			array(
				'heading' => __( 'Related guides', 'greendawn' ),
				'layout'  => 'grid',
				'posts'   => $posts,
			)
		);
	}
}
