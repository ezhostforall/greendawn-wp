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
$greendawn_phone_title   = $args['phone_title'] ?? '';
$greendawn_phone_intro   = $args['phone_intro'] ?? '';
?>
<section id="<?php echo esc_attr( $greendawn_process_id ); ?>" class="gd-journey-section gd-process<?php echo $greendawn_process_navy ? ' gd-process--navy' : ''; ?>" aria-labelledby="<?php echo esc_attr( $greendawn_process_title ); ?>">
	<div class="gd-home-shell">
		<div class="gd-process__intro">
			<div>
				<?php
				if ( ! empty( $args['eyebrow'] ) ) :
					?>
					<p class="gd-eyebrow gd-journey-eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p><?php endif; ?>
				<h2 id="<?php echo esc_attr( $greendawn_process_title ); ?>">
					<?php if ( '' !== $greendawn_phone_title ) : ?>
						<span class="gd-refresh-wide"><?php echo esc_html( $args['heading'] ?? '' ); ?></span>
						<span class="gd-refresh-phone"><?php echo esc_html( $greendawn_phone_title ); ?></span>
					<?php else : ?>
						<?php echo esc_html( $args['heading'] ?? '' ); ?>
					<?php endif; ?>
				</h2>
			</div>
			<?php
			if ( ! empty( $args['intro'] ) || '' !== $greendawn_phone_intro ) :
				?>
				<p>
					<?php if ( '' !== $greendawn_phone_intro ) : ?>
						<span class="gd-refresh-wide"><?php echo esc_html( $args['intro'] ?? '' ); ?></span>
						<span class="gd-refresh-phone"><?php echo esc_html( $greendawn_phone_intro ); ?></span>
					<?php else : ?>
						<?php echo esc_html( $args['intro'] ?? '' ); ?>
					<?php endif; ?>
				</p><?php endif; ?>
		</div>
		<div class="gd-process__track">
			<?php if ( '' !== $greendawn_phone_title ) : ?>
				<div class="gd-refresh-process-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Delivery stages', 'greendawn' ); ?>" hidden>
					<?php foreach ( $greendawn_process_steps as $greendawn_index => $greendawn_step ) : ?>
						<button type="button" id="<?php echo esc_attr( $greendawn_process_id . '-tab-' . $greendawn_index ); ?>" role="tab" aria-controls="<?php echo esc_attr( $greendawn_process_id . '-stage-' . $greendawn_index ); ?>"><?php echo esc_html( $greendawn_step['title'] ?? '' ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="gd-process__line" aria-hidden="true"><span></span></div>
			<ol class="gd-process__steps" role="list">
				<?php foreach ( $greendawn_process_steps as $greendawn_index => $greendawn_step ) : ?>
					<li
					<?php
					if ( '' !== $greendawn_phone_title ) :
						?>
						id="<?php echo esc_attr( $greendawn_process_id . '-stage-' . $greendawn_index ); ?>"<?php endif; ?>>
						<span aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $greendawn_index + 1 ) ); ?></span>
						<h3><?php echo esc_html( $greendawn_step['title'] ?? '' ); ?></h3>
						<p><?php echo esc_html( $greendawn_step['copy'] ?? '' ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			<?php if ( '' !== $greendawn_phone_title ) : ?>
				<div class="gd-refresh-process-controls" hidden>
					<p data-gd-stage-count role="status"><?php esc_html_e( 'Swipe or choose a stage', 'greendawn' ); ?></p>
					<button type="button" data-gd-step="-1" aria-label="<?php esc_attr_e( 'Previous stage', 'greendawn' ); ?>">←</button>
					<button type="button" data-gd-step="1" aria-label="<?php esc_attr_e( 'Next stage', 'greendawn' ); ?>">→</button>
				</div>
			<?php endif; ?>
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
