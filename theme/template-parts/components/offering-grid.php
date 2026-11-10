<?php
/**
 * Full-content offering cards: headings, paragraphs and lists remain visible.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_heading = (string) ( $args['heading'] ?? '' );
$greendawn_items   = array_values( array_filter( $args['items'] ?? array(), static fn( array $item ): bool => '' !== (string) ( $item['title'] ?? '' ) || '' !== (string) ( $item['copy'] ?? '' ) ) );
$greendawn_id      = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-offerings-' ) );
if ( ! $greendawn_items ) {
	return;
}
?>
<section class="gd-section gd-offerings" <?php echo '' !== $greendawn_heading ? 'aria-labelledby="' . esc_attr( $greendawn_id ) . '-title"' : ''; ?>>
	<div class="gd-shell">
		<?php if ( '' !== $greendawn_heading ) : ?>
			<h2 id="<?php echo esc_attr( $greendawn_id ); ?>-title"><?php echo esc_html( $greendawn_heading ); ?></h2>
		<?php endif; ?>
		<div class="gd-offering-grid">
			<?php foreach ( $greendawn_items as $greendawn_item ) : ?>
				<article class="gd-offering-card">
					<?php if ( '' !== (string) ( $greendawn_item['title'] ?? '' ) ) : ?>
						<h3><?php echo esc_html( $greendawn_item['title'] ); ?></h3>
					<?php endif; ?>
					<?php echo greendawn_page_prose( (string) ( $greendawn_item['copy'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Restricted semantic content allowlist. ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
