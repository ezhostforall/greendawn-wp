<?php
/**
 * Native post grid and category navigation for Greendawn editorial listings.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_categories      = array();
$greendawn_category_labels = array(
	'guides'    => __( 'Guides', 'greendawn' ),
	'editorial' => __( 'Editorials', 'greendawn' ),
);
foreach ( $greendawn_category_labels as $greendawn_slug => $greendawn_label ) {
	$greendawn_category = get_category_by_slug( $greendawn_slug );
	if ( ! $greendawn_category ) {
		continue;
	}
	$greendawn_url = get_term_link( $greendawn_category );
	if ( ! is_wp_error( $greendawn_url ) ) {
		$greendawn_categories[] = array(
			'name'    => $greendawn_label,
			'url'     => $greendawn_url,
			'current' => is_category( $greendawn_category->term_id ),
		);
	}
}
$greendawn_posts_page = (int) get_option( 'page_for_posts' );
if ( ! is_home() && $greendawn_posts_page && 'publish' === get_post_status( $greendawn_posts_page ) ) {
	array_unshift(
		$greendawn_categories,
		array(
			'name'    => __( 'All articles', 'greendawn' ),
			'url'     => get_permalink( $greendawn_posts_page ),
			'current' => false,
		)
	);
}
?>
<div class="gd-shell gd-editorial-listing">
	<?php if ( ! empty( $args['description'] ) ) : ?>
		<div class="gd-editorial-description"><?php echo wp_kses_post( $args['description'] ); ?></div>
	<?php endif; ?>
	<?php if ( $greendawn_categories ) : ?>
		<nav class="gd-editorial-categories" aria-label="<?php esc_attr_e( 'Article categories', 'greendawn' ); ?>">
			<ul role="list">
				<?php foreach ( $greendawn_categories as $greendawn_link ) : ?>
					<li><a href="<?php echo esc_url( $greendawn_link['url'] ); ?>"<?php echo $greendawn_link['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $greendawn_link['name'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>
	<?php get_template_part( 'template-parts/post-list', null, array( 'editorial' => true ) ); ?>
</div>
