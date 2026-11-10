<?php
/**
 * Shared static process. Homepage motion can enhance its existing markup.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_process_id    = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-process-' ) );
$greendawn_process_title = sanitize_html_class( $args['title_id'] ?? $greendawn_process_id . '-title' );
$greendawn_process_steps = (array) ( $args['steps'] ?? array() );
$greendawn_process_navy  = 'navy' === ( $args['surface'] ?? '' );
?>
<section id="<?php echo esc_attr( $greendawn_process_id ); ?>" class="gd-journey-section gd-process<?php echo $greendawn_process_navy ? ' gd-process--navy' : ''; ?>" aria-labelledby="<?php echo esc_attr( $greendawn_process_title ); ?>">
	<div class="gd-home-shell">
		<div class="gd-process__intro">
			<div>
				<?php
				if ( ! empty( $args['eyebrow'] ) ) :
					?>
					<p class="gd-eyebrow gd-journey-eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p><?php endif; ?>
				<h2 id="<?php echo esc_attr( $greendawn_process_title ); ?>"><?php echo esc_html( $args['heading'] ?? '' ); ?></h2>
			</div>
			<?php
			if ( ! empty( $args['intro'] ) ) :
				?>
				<p><?php echo esc_html( $args['intro'] ); ?></p><?php endif; ?>
		</div>
		<div class="gd-process__track">
			<div class="gd-process__line" aria-hidden="true"><span></span></div>
			<ol class="gd-process__steps" role="list">
				<?php foreach ( $greendawn_process_steps as $greendawn_index => $greendawn_step ) : ?>
					<li>
						<span aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $greendawn_index + 1 ) ); ?></span>
						<h3><?php echo esc_html( $greendawn_step['title'] ?? '' ); ?></h3>
						<p><?php echo esc_html( $greendawn_step['copy'] ?? '' ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
		<?php
		$greendawn_process_media = array();
		foreach ( (array) ( $args['media'] ?? array() ) as $greendawn_media_id ) {
			$greendawn_image = wp_get_attachment_image(
				absint( $greendawn_media_id ),
				'large',
				false,
				array(
					'loading' => 'lazy',
					'sizes'   => '(max-width: 42rem) calc(100vw - 2rem), 45vw',
				)
			);
			if ( $greendawn_image ) {
				$greendawn_process_media[] = $greendawn_image;
			}
		}
		if ( $greendawn_process_media ) :
			?>
			<div class="gd-process-media<?php echo 1 === count( $greendawn_process_media ) ? ' gd-process-media--single' : ''; ?>">
				<?php
				foreach ( $greendawn_process_media as $greendawn_image ) :
					?>
					<figure><?php echo $greendawn_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress attachment markup. ?></figure><?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
