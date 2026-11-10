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
<section class="gd-section gd-story" <?php echo '' !== $greendawn_heading ? 'aria-labelledby="' . esc_attr( $greendawn_id ) . '-title"' : ''; ?>>
	<div class="gd-shell gd-media-copy<?php echo $greendawn_image ? '' : ' gd-media-copy--text'; ?>">
		<?php if ( $greendawn_image ) : ?>
			<figure><?php echo $greendawn_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native escaped attachment markup. ?></figure>
		<?php endif; ?>
		<div class="gd-reading-copy">
			<?php if ( '' !== $greendawn_heading ) : ?>
				<h2 id="<?php echo esc_attr( $greendawn_id ); ?>-title"><?php echo esc_html( $greendawn_heading ); ?></h2>
			<?php endif; ?>
			<?php echo greendawn_page_prose( $greendawn_copy ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Restricted semantic content allowlist. ?>
		</div>
	</div>
</section>
