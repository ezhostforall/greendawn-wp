<?php
/**
 * Aftercare scope and routes to ongoing customer support.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_aftercare_url = esc_url( greendawn_home_field( 'aftercare_cta_url' ) );
$greendawn_support_url   = esc_url( greendawn_home_field( 'aftercare_support_url' ) );
?>
<section id="aftercare" class="gd-journey-section gd-aftercare" aria-labelledby="gd-aftercare-title">
	<div class="gd-home-shell gd-aftercare__layout">
		<div class="gd-aftercare__copy">
			<p class="gd-eyebrow gd-journey-eyebrow"><?php echo esc_html( greendawn_home_field( 'aftercare_eyebrow' ) ); ?></p>
			<h2 id="gd-aftercare-title"><?php echo esc_html( greendawn_home_field( 'aftercare_title' ) ); ?></h2>
			<p class="gd-aftercare__intro"><?php echo esc_html( greendawn_home_field( 'aftercare_intro' ) ); ?></p>
			<p><?php echo esc_html( greendawn_home_field( 'aftercare_copy' ) ); ?></p>
			<?php if ( '' !== $greendawn_aftercare_url ) : ?>
				<a class="gd-home-button" href="<?php echo esc_url( $greendawn_aftercare_url ); ?>">
					<?php echo esc_html( greendawn_home_field( 'aftercare_cta_label' ) ); ?>
					<?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?>
				</a>
			<?php endif; ?>
			<p class="gd-aftercare__support">
				<?php echo esc_html( greendawn_home_field( 'aftercare_support_prompt' ) ); ?>
				<?php if ( '' !== $greendawn_support_url ) : ?>
					<a class="gd-aftercare__support-link" href="<?php echo esc_url( $greendawn_support_url ); ?>"><?php echo esc_html( greendawn_home_field( 'aftercare_support_label' ) ); ?></a>.
				<?php endif; ?>
			</p>
		</div>
		<div class="gd-aftercare__services">
			<?php foreach ( array( 'monitoring', 'testing', 'maintenance', 'users' ) as $greendawn_index => $greendawn_service ) : ?>
				<article>
					<span aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $greendawn_index + 1 ) ); ?></span>
					<h3><?php echo esc_html( greendawn_home_field( 'aftercare_' . $greendawn_service . '_title' ) ); ?></h3>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
	<p class="gd-aftercare__quote gd-home-shell"><?php echo esc_html( greendawn_home_field( 'aftercare_statement' ) ); ?></p>
</section>
