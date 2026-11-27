<?php
/**
 * Fixed media/copy split with restricted semantic prose and native attachment markup.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_heading = (string) ( $args['heading'] ?? '' );
$greendawn_copy    = (string) ( $args['copy'] ?? '' );
$greendawn_id      = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-story-' ) );
$greendawn_dark    = 'dark' === ( $args['tone'] ?? '' );
$greendawn_kicker  = greendawn_component_text( $args['kicker'] ?? '' );
$greendawn_caption = greendawn_component_text( $args['caption'] ?? '' );
$greendawn_image   = wp_get_attachment_image(
	absint( $args['image_id'] ?? 0 ),
	'large',
	false,
	array(
		'loading' => 'lazy',
		'sizes'   => '(max-width: 61rem) calc(100vw - 2rem), 45vw',
	)
);
if ( '' === $greendawn_heading && '' === $greendawn_copy && ! $greendawn_image ) {
	return;
}
?>
<section class="gd-section gd-story<?php echo $greendawn_dark ? ' gd-story--dark' : ''; ?>" <?php echo '' !== $greendawn_heading ? 'aria-labelledby="' . esc_attr( $greendawn_id ) . '-title"' : ''; ?>>
	<div class="gd-shell gd-media-copy<?php echo $greendawn_image ? '' : ' gd-media-copy--text'; ?>">
		<?php if ( $greendawn_image ) : ?>
			<figure><?php echo $greendawn_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native escaped attachment markup. ?>
			<?php
			if ( $greendawn_caption ) :
				?>
				<figcaption><?php echo esc_html( $greendawn_caption ); ?></figcaption><?php endif; ?></figure>
		<?php endif; ?>
		<div class="gd-reading-copy">
			<?php
			if ( $greendawn_kicker ) :
				?>
				<p class="gd-eyebrow"><?php echo esc_html( $greendawn_kicker ); ?></p><?php endif; ?>
			<?php if ( '' !== $greendawn_heading ) : ?>
				<h2 id="<?php echo esc_attr( $greendawn_id ); ?>-title"><?php echo esc_html( $greendawn_heading ); ?></h2>
			<?php endif; ?>
			<?php echo greendawn_page_prose( $greendawn_copy ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Restricted semantic content allowlist. ?>
			<?php if ( is_array( $args['link'] ?? null ) && ! empty( $args['link']['label'] ) && ! empty( $args['link']['url'] ) && esc_url( $args['link']['url'] ) ) : ?>
				<a class="gd-page-text-link" href="<?php echo esc_url( $args['link']['url'] ); ?>"><?php echo esc_html( $args['link']['label'] ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
