<?php
/**
 * Greendawn homepage hero and delivery stages.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_hero_sizes = '(max-width: 61rem) 100vw, 60vw';
$greendawn_hero_image = absint( greendawn_home_field( 'hero_image' ) );
$greendawn_hero_html  = $greendawn_hero_image ? wp_get_attachment_image(
	$greendawn_hero_image,
	'large',
	false,
	array(
		'loading'       => 'eager',
		'fetchpriority' => 'high',
		'sizes'         => $greendawn_hero_sizes,
	)
) : '';

$greendawn_enquiry_url   = esc_url( (string) get_theme_mod( 'greendawn_header_cta_url', '' ) );
$greendawn_enquiry_url   = '#' === $greendawn_enquiry_url ? '' : $greendawn_enquiry_url;
$greendawn_enquiry_label = trim( (string) get_theme_mod( 'greendawn_header_cta_label', '' ) );
$greendawn_project_url   = esc_url( greendawn_home_field( 'projects_url' ) );
?>
<section class="gd-hero" id="top" aria-labelledby="gd-hero-title">
	<div class="gd-hero__media">
		<?php if ( '' !== $greendawn_hero_html ) : ?>
			<?php echo $greendawn_hero_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generates escaped attachment markup. ?>
		<?php endif; ?>
	</div>
	<div class="gd-hero__shade" aria-hidden="true"></div>
	<svg class="gd-hero__arc" viewBox="0 0 900 470" fill="none" aria-hidden="true" focusable="false">
		<path d="M32 438C174 83 602 -77 868 222" />
	</svg>

	<div class="gd-hero__content gd-home-shell">
		<div class="gd-hero__copy">
			<p class="gd-eyebrow">
				<span><?php echo esc_html( greendawn_home_field( 'eyebrow' ) ); ?></span>
				<span><?php echo esc_html( greendawn_home_field( 'coverage' ) ); ?></span>
			</p>
			<h1 id="gd-hero-title">
				<span class="gd-hero-line gd-hero-line--service"><span><?php echo esc_html( greendawn_home_field( 'headline' ) ); ?></span></span>
				<span class="gd-hero-line gd-hero-line--accent"><span><?php echo esc_html( greendawn_home_field( 'headline_accent' ) ); ?></span></span>
			</h1>
			<p class="gd-hero__lead"><?php echo esc_html( greendawn_home_field( 'lead' ) ); ?></p>
			<div class="gd-hero__actions">
				<?php if ( '' !== $greendawn_enquiry_url ) : ?>
					<a class="gd-home-button" href="<?php echo esc_url( $greendawn_enquiry_url ); ?>">
						<?php echo esc_html( '' !== $greendawn_enquiry_label ? $greendawn_enquiry_label : __( 'Discuss your site', 'greendawn' ) ); ?>
						<?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?>
					</a>
				<?php endif; ?>
				<?php if ( '' !== $greendawn_project_url ) : ?>
					<a class="gd-hero__project" href="<?php echo esc_url( $greendawn_project_url ); ?>">
						<?php echo esc_html( greendawn_home_field( 'projects_label' ) ); ?>
						<?php get_template_part( 'template-parts/home/arrow' ); ?>
					</a>
				<?php endif; ?>
			</div>
			<ul class="gd-hero__assurances">
				<li><?php echo esc_html( greendawn_home_field( 'assurance_requirements' ) ); ?></li>
				<li><?php echo esc_html( greendawn_home_field( 'assurance_equipment' ) ); ?></li>
				<li><?php echo esc_html( greendawn_home_field( 'assurance_support' ) ); ?></li>
			</ul>
		</div>
	</div>

	<div class="gd-hero__proof">
		<?php $greendawn_stage_number = 0; ?>
		<?php foreach ( array( 'consult', 'design', 'install', 'manage' ) as $greendawn_stage ) : ?>
			<div>
				<span aria-hidden="true"><?php echo esc_html( sprintf( '%02d', ++$greendawn_stage_number ) ); ?></span>
				<strong><?php echo esc_html( greendawn_home_field( $greendawn_stage . '_label' ) ); ?></strong>
				<small><?php echo esc_html( greendawn_home_field( $greendawn_stage . '_caption' ) ); ?></small>
			</div>
		<?php endforeach; ?>
	</div>
</section>
