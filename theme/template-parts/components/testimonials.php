<?php
/**
 * Full quotes with local media and an optional, explicitly activated video.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_heading = (string) ( $args['heading'] ?? '' );
$greendawn_intro   = (string) ( $args['intro'] ?? '' );
$greendawn_id      = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-testimonials-' ) );
$greendawn_items   = array_filter( (array) ( $args['items'] ?? array() ), static fn( array $item ): bool => '' !== (string) ( $item['title'] ?? '' ) || '' !== (string) ( $item['quote'] ?? '' ) );
if ( ! $greendawn_items ) {
	return;
}
?>
<section class="gd-section gd-testimonials" <?php echo '' !== $greendawn_heading ? 'aria-labelledby="' . esc_attr( $greendawn_id ) . '-title"' : ''; ?>>
	<div class="gd-shell">
		<?php if ( '' !== $greendawn_heading ) : ?>
			<h2 id="<?php echo esc_attr( $greendawn_id ); ?>-title"><?php echo esc_html( $greendawn_heading ); ?></h2>
		<?php endif; ?>
		<?php if ( '' !== $greendawn_intro ) : ?>
			<div class="gd-testimonials-intro"><?php echo wpautop( esc_html( $greendawn_intro ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain text escaped before paragraph formatting. ?></div>
		<?php endif; ?>
		<div class="gd-testimonial-grid">
			<?php foreach ( $greendawn_items as $greendawn_item ) : ?>
				<?php
				$greendawn_title = (string) ( $greendawn_item['title'] ?? '' );
				$greendawn_video = apply_filters( 'greendawn_page_video', array(), (string) ( $greendawn_item['video_url'] ?? '' ) );
				$greendawn_image = wp_get_attachment_image(
					absint( $greendawn_item['image_id'] ?? 0 ),
					'large',
					false,
					array(
						'loading' => 'lazy',
						'alt'     => $greendawn_title,
						'sizes'   => '(max-width: 42rem) calc(100vw - 2rem), (max-width: 61rem) 45vw, 30vw',
					)
				);
				$greendawn_label = '' !== $greendawn_title ? sprintf( /* translators: %s: client organisation. */ __( 'Watch %s video testimonial', 'greendawn' ), $greendawn_title ) : __( 'Watch video testimonial', 'greendawn' );
				?>
				<article class="gd-testimonial-card">
					<?php if ( $greendawn_image || $greendawn_video ) : ?>
						<div class="gd-testimonial-media<?php echo 'contain' === ( $greendawn_item['image_fit'] ?? '' ) ? ' gd-testimonial-media--logo' : ''; ?>" <?php echo $greendawn_video ? 'data-gd-video-media' : ''; ?>>
							<?php echo $greendawn_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped native attachment markup. ?>
							<?php if ( $greendawn_video ) : ?>
								<a class="gd-video-link" href="<?php echo esc_url( $greendawn_video['url'] ); ?>" data-gd-video="<?php echo esc_url( $greendawn_video['embed'] ); ?>">
									<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m8 5 11 7-11 7Z"/></svg>
									<span><?php echo esc_html( $greendawn_label ); ?></span>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<div class="gd-testimonial-copy">
						<?php if ( '' !== $greendawn_title ) : ?>
							<h3>
								<?php if ( $greendawn_video ) : ?>
									<a href="<?php echo esc_url( $greendawn_video['url'] ); ?>"><?php echo esc_html( $greendawn_title ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $greendawn_title ); ?>
								<?php endif; ?>
							</h3>
						<?php endif; ?>
						<blockquote><?php echo wpautop( esc_html( (string) ( $greendawn_item['quote'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Complete plain-text quote escaped before paragraph formatting. ?></blockquote>
						<?php if ( '' !== (string) ( $greendawn_item['attribution'] ?? '' ) ) : ?>
							<p class="gd-testimonial-attribution"><?php echo esc_html( $greendawn_item['attribution'] ); ?></p>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
