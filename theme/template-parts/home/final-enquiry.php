<?php
/**
 * Closing enquiry panel from the homepage reference.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_closing_url   = esc_url( (string) get_theme_mod( 'greendawn_header_cta_url', '' ) );
$greendawn_closing_url   = '#' === $greendawn_closing_url ? '' : $greendawn_closing_url;
$greendawn_closing_phone = greendawn_home_field( 'closing_phone' );
$greendawn_closing_tel   = greendawn_phone_uri( $greendawn_closing_phone );
?>
<section id="final-enquiry" class="gd-journey-section gd-final-cta" aria-labelledby="gd-final-cta-title">
	<svg viewBox="0 0 1000 550" fill="none" aria-hidden="true" focusable="false">
		<path d="M23 526C216 90 740 -71 974 233" />
	</svg>
	<div class="gd-home-shell gd-final-cta__inner">
		<p class="gd-eyebrow gd-journey-eyebrow"><?php echo esc_html( greendawn_home_field( 'closing_eyebrow' ) ); ?></p>
		<h2 id="gd-final-cta-title"><?php echo esc_html( greendawn_home_field( 'closing_title' ) ); ?></h2>
		<p><?php echo esc_html( greendawn_home_field( 'closing_intro' ) ); ?></p>
		<p><?php echo esc_html( greendawn_home_field( 'closing_copy' ) ); ?></p>
		<div class="gd-final-cta__actions">
			<?php if ( '' !== $greendawn_closing_url ) : ?>
				<a class="gd-home-button gd-home-button--navy" href="<?php echo esc_url( $greendawn_closing_url ); ?>">
					<?php echo esc_html( greendawn_home_field( 'closing_cta_label' ) ); ?>
					<?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?>
				</a>
			<?php endif; ?>
			<?php if ( '' !== $greendawn_closing_tel ) : ?>
				<p>
					<?php echo esc_html( greendawn_home_field( 'closing_phone_prompt' ) ); ?>
					<a href="<?php echo esc_url( $greendawn_closing_tel ); ?>"><?php echo esc_html( $greendawn_closing_phone ); ?></a>
				</p>
			<?php endif; ?>
		</div>
	</div>
</section>
