<?php
/**
 * Greendawn homepage hero and delivery stages.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_hero_sizes = '(max-width: 61rem) 100vw, 60vw';
$greendawn_hero_image = absint( greendawn_get_field( 'greendawn_home_hero_image', false, 0 ) );
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

$greendawn_hero_src = get_theme_file_uri( '/assets/home/greendawn-engineers-electrical-distribution-' );
$greendawn_srcset   = array();
foreach ( array( 480, 800, 1200, 1600 ) as $greendawn_width ) {
	$greendawn_srcset[] = esc_url_raw( $greendawn_hero_src . $greendawn_width . '.webp' ) . ' ' . $greendawn_width . 'w';
}

$greendawn_enquiry_url   = esc_url( (string) get_theme_mod( 'greendawn_header_cta_url', '' ) );
$greendawn_enquiry_url   = '#' === $greendawn_enquiry_url ? '' : $greendawn_enquiry_url;
$greendawn_enquiry_label = trim( (string) get_theme_mod( 'greendawn_header_cta_label', '' ) );
$greendawn_project_url   = esc_url( (string) greendawn_get_field( 'greendawn_home_projects_url', false, '' ) );
$greendawn_stages        = array(
	__( 'Consult', 'greendawn' ) => __( 'Understand your requirement', 'greendawn' ),
	__( 'Design', 'greendawn' )  => __( 'Resolve power, layout and scope', 'greendawn' ),
	__( 'Install', 'greendawn' ) => __( 'Coordinate works and commissioning', 'greendawn' ),
	__( 'Manage', 'greendawn' )  => __( 'Support the charging operation', 'greendawn' ),
);
?>
<section class="gd-hero" id="top" aria-labelledby="gd-hero-title">
	<div class="gd-hero__media">
		<?php if ( '' !== $greendawn_hero_html ) : ?>
			<?php echo $greendawn_hero_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generates escaped attachment markup. ?>
		<?php else : ?>
			<img
				src="<?php echo esc_url( $greendawn_hero_src . '1200.webp' ); ?>"
				srcset="<?php echo esc_attr( implode( ', ', $greendawn_srcset ) ); ?>"
				sizes="<?php echo esc_attr( $greendawn_hero_sizes ); ?>"
				width="1200" height="900" loading="eager" fetchpriority="high" decoding="async"
				alt="<?php esc_attr_e( 'Greendawn engineers working on commercial electrical distribution equipment', 'greendawn' ); ?>"
			>
		<?php endif; ?>
	</div>
	<div class="gd-hero__shade" aria-hidden="true"></div>
	<svg class="gd-hero__arc" viewBox="0 0 900 470" fill="none" aria-hidden="true" focusable="false">
		<path d="M32 438C174 83 602 -77 868 222" />
	</svg>

	<div class="gd-hero__content gd-home-shell">
		<div class="gd-hero__copy">
			<p class="gd-eyebrow">
				<span><?php greendawn_the_field( 'greendawn_home_eyebrow', 'text', false, __( 'Commercial EV charging infrastructure', 'greendawn' ) ); ?></span>
				<span><?php esc_html_e( 'England · Scotland · Wales', 'greendawn' ); ?></span>
			</p>
			<h1 id="gd-hero-title">
				<span class="gd-hero-line gd-hero-line--service"><span><?php greendawn_the_field( 'greendawn_home_headline', 'text', false, __( 'EV charging.', 'greendawn' ) ); ?></span></span>
				<span class="gd-hero-line gd-hero-line--accent"><span><?php greendawn_the_field( 'greendawn_home_headline_accent', 'text', false, __( 'Handled.', 'greendawn' ) ); ?></span></span>
			</h1>
			<p class="gd-hero__lead"><?php greendawn_the_field( 'greendawn_home_lead', 'text', false, __( 'Greendawn plans, installs and supports commercial EV charging around your vehicles, users and available power—from a single site to a coordinated estate programme.', 'greendawn' ) ); ?></p>
			<?php if ( '' !== $greendawn_enquiry_url || '' !== $greendawn_project_url ) : ?>
				<div class="gd-hero__actions">
					<?php if ( '' !== $greendawn_enquiry_url ) : ?>
						<a class="gd-home-button" href="<?php echo esc_url( $greendawn_enquiry_url ); ?>">
							<?php echo esc_html( '' !== $greendawn_enquiry_label ? $greendawn_enquiry_label : __( 'Discuss your site', 'greendawn' ) ); ?>
							<?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?>
						</a>
					<?php endif; ?>
					<?php if ( '' !== $greendawn_project_url ) : ?>
						<a class="gd-hero__project" href="<?php echo esc_url( $greendawn_project_url ); ?>">
							<?php greendawn_the_field( 'greendawn_home_projects_label', 'text', false, __( 'Explore our projects', 'greendawn' ) ); ?>
							<?php get_template_part( 'template-parts/home/arrow' ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<ul class="gd-hero__assurances">
				<li><?php greendawn_the_field( 'greendawn_home_assurance_requirements', 'text', false, __( 'Site requirements first', 'greendawn' ) ); ?></li>
				<li><?php greendawn_the_field( 'greendawn_home_assurance_equipment', 'text', false, __( 'Equipment chosen for the operation', 'greendawn' ) ); ?></li>
				<li><?php greendawn_the_field( 'greendawn_home_assurance_support', 'text', false, __( 'Agreed support after installation', 'greendawn' ) ); ?></li>
			</ul>
		</div>
	</div>

	<div class="gd-hero__proof">
		<?php $greendawn_stage_number = 0; ?>
		<?php foreach ( $greendawn_stages as $greendawn_stage => $greendawn_caption ) : ?>
			<div>
				<span aria-hidden="true"><?php echo esc_html( sprintf( '%02d', ++$greendawn_stage_number ) ); ?></span>
				<strong><?php echo esc_html( $greendawn_stage ); ?></strong>
				<small><?php echo esc_html( $greendawn_caption ); ?></small>
			</div>
		<?php endforeach; ?>
	</div>
</section>
