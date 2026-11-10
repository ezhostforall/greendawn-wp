<?php
/**
 * Native related-article collection with optional controls and a no-JS scroll track.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_articles = (array) ( $args['posts'] ?? array() );
if ( ! $greendawn_articles ) {
	return;
}
$greendawn_articles_id = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-articles-' ) );
?>
<section class="gd-section gd-page-insights" data-gd-articles aria-labelledby="<?php echo esc_attr( $greendawn_articles_id . '-insights-title' ); ?>">
	<div class="gd-shell">
		<h2 id="<?php echo esc_attr( $greendawn_articles_id . '-insights-title' ); ?>"><?php echo esc_html( $args['heading'] ?? '' ); ?></h2>
		<div class="gd-article-controls"><button type="button" data-gd-track-direction="-1" aria-controls="<?php echo esc_attr( $greendawn_articles_id . '-article-track' ); ?>" aria-label="<?php esc_attr_e( 'Previous articles', 'greendawn' ); ?>" hidden>←</button><button type="button" data-gd-track-direction="1" aria-controls="<?php echo esc_attr( $greendawn_articles_id . '-article-track' ); ?>" aria-label="<?php esc_attr_e( 'Next articles', 'greendawn' ); ?>" hidden>→</button></div>
		<div id="<?php echo esc_attr( $greendawn_articles_id . '-article-track' ); ?>" class="gd-article-track" role="region" aria-label="<?php esc_attr_e( 'Related articles — scroll for more', 'greendawn' ); ?>" tabindex="0">
			<?php foreach ( $greendawn_articles as $greendawn_article ) : ?>
				<?php
				get_template_part(
					'template-parts/components/article-card',
					null,
					array(
						'instance_id' => $greendawn_articles_id . '-article-' . $greendawn_article->ID,
						'title'       => get_the_title( $greendawn_article ),
						'url'         => get_permalink( $greendawn_article ),
						'image_id'    => get_post_thumbnail_id( $greendawn_article ),
						'show_label'  => false,
					)
				);
				?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
