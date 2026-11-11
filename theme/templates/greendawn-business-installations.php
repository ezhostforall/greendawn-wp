<?php
/**
 * Template Name: Greendawn Business EV Charging Installations
 * Template Post Type: page
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	if ( post_password_required() ) {
		echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native password gate.
		continue;
	}
	?>
	<article <?php post_class( 'greendawn-page greendawn-business' ); ?>>
		<?php
		get_template_part(
			'template-parts/components/page-hero',
			null,
			array(
				'heading'  => greendawn_business_field( 'heading', get_the_title() ),
				'accent'   => greendawn_business_field( 'accent' ),
				'intro'    => greendawn_business_field( 'intro' ),
				'image_id' => 0,
			)
		);
		$greendawn_services     = array();
		$greendawn_testimonials = array();
		foreach ( range( 1, 3 ) as $greendawn_number ) {
			$greendawn_services[]     = array(
				'title' => greendawn_business_field( 'service_' . $greendawn_number . '_title' ),
				'copy'  => greendawn_business_field( 'service_' . $greendawn_number . '_copy' ),
			);
			$greendawn_testimonials[] = array(
				'title'       => greendawn_business_field( 'testimonial_' . $greendawn_number . '_title' ),
				'quote'       => greendawn_business_field( 'testimonial_' . $greendawn_number . '_quote' ),
				'attribution' => greendawn_business_field( 'testimonial_' . $greendawn_number . '_attribution' ),
				'image_id'    => greendawn_business_field( 'testimonial_' . $greendawn_number . '_image', 0 ),
				'video_url'   => greendawn_business_field( 'testimonial_' . $greendawn_number . '_video' ),
				'image_fit'   => 1 === $greendawn_number ? 'cover' : 'contain',
			);
		}
		?>
		<section class="gd-section gd-business-enquiry" aria-label="<?php esc_attr_e( 'Business charging and project enquiry', 'greendawn' ); ?>">
			<div class="gd-shell gd-business-layout">
				<div class="gd-business-help">
					<?php if ( '' !== (string) greendawn_business_field( 'help_heading' ) ) : ?>
						<h2><?php echo esc_html( greendawn_business_field( 'help_heading' ) ); ?></h2>
					<?php endif; ?>
					<?php echo wpautop( esc_html( greendawn_business_field( 'help_intro' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain text escaped before paragraph formatting. ?>
					<?php get_template_part( 'template-parts/components/service-list', null, array( 'items' => $greendawn_services ) ); ?>
				</div>
				<div class="gd-business-form">
					<?php if ( '' !== (string) greendawn_business_field( 'form_heading' ) ) : ?>
						<h2><?php echo esc_html( greendawn_business_field( 'form_heading' ) ); ?></h2>
					<?php endif; ?>
					<?php echo wpautop( esc_html( greendawn_business_field( 'form_intro' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain text escaped before paragraph formatting. ?>
					<?php
					get_template_part(
						'template-parts/components/form-content',
						null,
						array(
							'form_id'       => greendawn_business_field( 'form', 0 ),
							'form_location' => 'business_installations',
						)
					);
					?>
				</div>
			</div>
		</section>
		<?php
		get_template_part(
			'template-parts/components/testimonials',
			null,
			array(
				'instance_id' => 'business-testimonials',
				'heading'     => greendawn_business_field( 'testimonials_heading' ),
				'intro'       => greendawn_business_field( 'testimonials_intro' ),
				'items'       => $greendawn_testimonials,
			)
		);
		?>
	</article>
	<?php
endwhile;
get_footer();
