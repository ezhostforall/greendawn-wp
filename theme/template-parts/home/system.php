<?php
/**
 * Charging system layers and planning guides.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
?>
<section id="system" class="gd-journey-section gd-system" aria-labelledby="gd-system-title">
	<div class="gd-home-shell gd-system__header">
		<p class="gd-eyebrow gd-journey-eyebrow"><?php echo esc_html( greendawn_home_field( 'system_eyebrow' ) ); ?></p>
		<h2 id="gd-system-title"><?php echo esc_html( greendawn_home_field( 'system_title' ) ); ?></h2>
		<p><?php echo esc_html( greendawn_home_field( 'system_intro' ) ); ?></p>
	</div>

	<div class="gd-home-shell gd-system__stage">
		<svg class="gd-system__arc" viewBox="0 0 1200 490" fill="none" aria-hidden="true" focusable="false">
			<path d="M21 432C190 75 762 -91 1171 241" />
		</svg>
		<div class="gd-system__cards">
			<?php foreach ( array( 'power', 'equipment', 'software' ) as $greendawn_index => $greendawn_layer ) : ?>
				<article class="gd-system__card">
					<p class="gd-system__card-meta">
						<span><?php echo esc_html( sprintf( '%02d', $greendawn_index + 1 ) ); ?></span>
						<span><?php echo esc_html( greendawn_home_field( 'system_' . $greendawn_layer . '_detail' ) ); ?></span>
					</p>
					<h3><?php echo esc_html( greendawn_home_field( 'system_' . $greendawn_layer . '_title' ) ); ?></h3>
					<p><?php echo esc_html( greendawn_home_field( 'system_' . $greendawn_layer . '_copy' ) ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
		<div class="gd-system__decisions">
			<?php foreach ( array( 'technology', 'site_power' ) as $greendawn_decision ) : ?>
				<?php $greendawn_decision_prefix = 'system_' . $greendawn_decision; ?>
				<article>
					<p class="gd-system__decision-label"><?php echo esc_html( greendawn_home_field( $greendawn_decision_prefix . '_label' ) ); ?></p>
					<h3><?php echo esc_html( greendawn_home_field( $greendawn_decision_prefix . '_title' ) ); ?></h3>
					<p><?php echo esc_html( greendawn_home_field( $greendawn_decision_prefix . '_copy' ) ); ?></p>
					<?php $greendawn_guide_url = esc_url( greendawn_home_field( $greendawn_decision_prefix . '_url' ) ); ?>
					<?php if ( '' !== $greendawn_guide_url ) : ?>
						<a class="gd-home-text-link" href="<?php echo esc_url( $greendawn_guide_url ); ?>">
							<?php echo esc_html( greendawn_home_field( $greendawn_decision_prefix . '_link' ) ); ?>
							<?php get_template_part( 'template-parts/home/arrow' ); ?>
						</a>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
