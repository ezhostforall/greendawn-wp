<?php
/**
 * Ordered photographs with native links to the full image.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_project = (string) ( $args['project'] ?? '' );
$greendawn_images  = $args['images'] ?? array();
?>
<ul class="gd-photo-grid<?php echo '' !== $greendawn_project ? ' gd-photo-grid--project' : ''; ?>" data-gd-photo-group role="list">
	<?php foreach ( $greendawn_images as $greendawn_index => $greendawn_image ) : ?>
		<?php
		$greendawn_full    = wp_get_attachment_image_url( $greendawn_image->ID, 'full' );
		$greendawn_caption = trim( wp_strip_all_tags( $greendawn_image->post_excerpt ) );
		$greendawn_alt     = trim( wp_strip_all_tags( (string) get_post_meta( $greendawn_image->ID, '_wp_attachment_image_alt', true ) ) );
		if ( '' === $greendawn_alt ) {
			$greendawn_alt = $greendawn_caption;
		}
		if ( '' === $greendawn_alt ) {
			$greendawn_alt = '' !== $greendawn_project
				? sprintf( /* translators: 1: project title, 2: photograph number. */ __( '%1$s, photograph %2$d', 'greendawn' ), $greendawn_project, $greendawn_index + 1 )
				: sprintf( /* translators: %d: photograph number. */ __( 'Project photograph %d', 'greendawn' ), $greendawn_index + 1 );
		}
		$greendawn_lead  = '' !== $greendawn_project && 0 === $greendawn_index;
		$greendawn_thumb = wp_get_attachment_image(
			$greendawn_image->ID,
			$greendawn_lead ? 'large' : 'medium_large',
			false,
			array(
				'alt'     => $greendawn_alt,
				'loading' => 'lazy',
				'sizes'   => $greendawn_lead ? '(max-width: 61rem) 95vw, 45vw' : '(max-width: 42rem) 45vw, 25vw',
			)
		);
		if ( ! $greendawn_full || ! $greendawn_thumb ) {
			continue;
		}
		?>
		<li>
			<figure>
				<a href="<?php echo esc_url( $greendawn_full ); ?>" data-gd-photo aria-label="<?php echo esc_attr( sprintf( /* translators: %s: image description. */ __( 'View larger image: %s', 'greendawn' ), $greendawn_alt ) ); ?>">
					<?php echo $greendawn_thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped native attachment markup. ?>
				</a>
				<?php if ( '' !== $greendawn_caption ) : ?>
					<figcaption><?php echo esc_html( $greendawn_caption ); ?></figcaption>
				<?php endif; ?>
			</figure>
		</li>
	<?php endforeach; ?>
</ul>
