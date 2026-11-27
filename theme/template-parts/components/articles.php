<?php
/**
 * Native related-article collection with optional controls and a no-JS scroll track.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_articles = (array) ( $args['posts'] ?? array() );
$greendawn_articles = array_values( array_filter( array_map( static fn( $post ): array => $post instanceof WP_Post ? greendawn_article_card_data( $post ) : array(), $greendawn_articles ) ) );
if ( ! $greendawn_articles ) {
	return;
}
$greendawn_articles_id = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-articles-' ) );
$greendawn_grid        = 'grid' === ( $args['layout'] ?? '' );
?>
<section class="gd-section gd-page-insights<?php echo $greendawn_grid ? ' gd-page-insights--grid' : ''; ?>" data-gd-articles aria-labelledby="<?php echo esc_attr( $greendawn_articles_id . '-insights-title' ); ?>">
	<div class="gd-shell">
		<h2 id="<?php echo esc_attr( $greendawn_articles_id . '-insights-title' ); ?>"><?php echo esc_html( $args['heading'] ?? '' ); ?></h2>
		<?php
		if ( ! $greendawn_grid ) :
			?>
			<div class="gd-article-controls"><button type="button" data-gd-track-direction="-1" aria-controls="<?php echo esc_attr( $greendawn_articles_id . '-article-track' ); ?>" aria-label="<?php esc_attr_e( 'Previous articles', 'greendawn' ); ?>" hidden>←</button><button type="button" data-gd-track-direction="1" aria-controls="<?php echo esc_attr( $greendawn_articles_id . '-article-track' ); ?>" aria-label="<?php esc_attr_e( 'Next articles', 'greendawn' ); ?>" hidden>→</button></div><?php endif; ?>
		<div id="<?php echo esc_attr( $greendawn_articles_id . '-article-track' ); ?>" class="<?php echo $greendawn_grid ? 'gd-related-grid' : 'gd-article-track'; ?>"
		<?php
		if ( ! $greendawn_grid ) :
			?>
			role="region" aria-label="<?php esc_attr_e( 'Related articles — scroll for more', 'greendawn' ); ?>" tabindex="0"<?php endif; ?>>
			<?php foreach ( $greendawn_articles as $greendawn_article ) : ?>
				<?php
				get_template_part(
					'template-parts/components/article-card',
					null,
					array_merge( $greendawn_article, array( 'instance_id' => $greendawn_articles_id . '-article-' . $greendawn_article['post_id'] ) )
				);
				?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
