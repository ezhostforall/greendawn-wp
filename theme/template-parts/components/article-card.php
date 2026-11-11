<?php
/**
 * Article card shared by homepage curation and native post relationships.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_card_id       = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-article-' ) );
$greendawn_card_title_id = sanitize_html_class( $args['title_id'] ?? $greendawn_card_id . '-title' );
$greendawn_card_label_id = sanitize_html_class( $args['label_id'] ?? $greendawn_card_id . '-label' );
$greendawn_card_label    = ! empty( $args['show_label'] );
$greendawn_card_class    = sanitize_html_class( $args['variant'] ?? '' );
$greendawn_editorial     = ! empty( $args['editorial'] );
$greendawn_card_image    = wp_get_attachment_image(
	absint( $args['image_id'] ?? 0 ),
	$greendawn_editorial ? 'medium_large' : 'large',
	false,
	array(
		'loading' => 'lazy',
		'sizes'   => $args['sizes'] ?? '(max-width: 42rem) calc(100vw - 2rem), (max-width: 61rem) 45vw, 30vw',
	)
);
if ( $greendawn_editorial ) :
	$greendawn_title = ! empty( $args['title'] ) ? (string) $args['title'] : __( 'Untitled article', 'greendawn' );
	$greendawn_url   = (string) ( $args['url'] ?? '' );
	?>
	<article id="post-<?php echo absint( $args['post_id'] ); ?>" <?php post_class( 'gd-insight-card gd-editorial-card post-card', absint( $args['post_id'] ) ); ?> aria-labelledby="<?php echo esc_attr( $greendawn_card_title_id ); ?>">
		<?php if ( $greendawn_card_image ) : ?>
			<figure>
				<a href="<?php echo esc_url( $greendawn_url ); ?>" tabindex="-1" aria-hidden="true">
					<?php echo $greendawn_card_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped native attachment markup. ?>
				</a>
			</figure>
		<?php endif; ?>
		<div class="gd-editorial-card__body">
			<h2 id="<?php echo esc_attr( $greendawn_card_title_id ); ?>"><a href="<?php echo esc_url( $greendawn_url ); ?>"><?php echo esc_html( $greendawn_title ); ?></a></h2>
			<time class="entry-meta" datetime="<?php echo esc_attr( $args['datetime'] ?? '' ); ?>"><?php echo esc_html( $args['date'] ?? '' ); ?></time>
			<div class="entry-summary"><?php echo wp_kses_post( $args['excerpt'] ?? '' ); ?></div>
			<a class="gd-editorial-card__more" href="<?php echo esc_url( $greendawn_url ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: article title. */ __( 'Read more: %s', 'greendawn' ), $greendawn_title ) ); ?>">
				<?php esc_html_e( 'Read more', 'greendawn' ); ?>
				<?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?>
			</a>
		</div>
	</article>
	<?php
	return;
endif;
?>
<a class="gd-insight-card<?php echo $greendawn_card_class ? ' gd-insight-card--' . esc_attr( $greendawn_card_class ) : ''; ?>" href="<?php echo esc_url( $args['url'] ?? '' ); ?>" aria-labelledby="<?php echo esc_attr( ( $greendawn_card_label ? $greendawn_card_label_id . ' ' : '' ) . $greendawn_card_title_id ); ?>">
	<?php if ( $greendawn_card_image ) : ?>
		<figure>
			<?php echo $greendawn_card_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generates escaped attachment markup. ?>
			<span aria-hidden="true"><?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?></span>
		</figure>
	<?php endif; ?>
	<?php
	if ( $greendawn_card_label ) :
		?>
		<p id="<?php echo esc_attr( $greendawn_card_label_id ); ?>"><?php echo esc_html( $args['label'] ?? '' ); ?></p><?php endif; ?>
	<h3 id="<?php echo esc_attr( $greendawn_card_title_id ); ?>"><?php echo esc_html( $args['title'] ?? '' ); ?></h3>
</a>
