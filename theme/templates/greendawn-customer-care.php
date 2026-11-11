<?php
/**
 * Template Name: Greendawn Customer Care
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
	$greendawn_whatsapp = apply_filters( 'greendawn_customer_care_whatsapp_url', '', (string) greendawn_customer_care_field( 'whatsapp_url' ) );
	$greendawn_label    = (string) greendawn_customer_care_field( 'whatsapp_label' );
	$greendawn_points   = array();
	foreach ( range( 1, 4 ) as $greendawn_number ) {
		$greendawn_points[] = array(
			'title' => greendawn_customer_care_field( 'point_' . $greendawn_number . '_title' ),
			'copy'  => greendawn_customer_care_field( 'point_' . $greendawn_number . '_copy' ),
		);
	}
	?>
	<article <?php post_class( 'greendawn-page greendawn-customer-care' ); ?>>
		<?php
		get_template_part(
			'template-parts/components/page-hero',
			null,
			array(
				'heading'  => greendawn_customer_care_field( 'heading', get_the_title() ),
				'accent'   => greendawn_customer_care_field( 'accent' ),
				'intro'    => greendawn_customer_care_field( 'intro' ),
				'image_id' => 0,
			)
		);
		get_template_part(
			'template-parts/components/feature-grid',
			null,
			array(
				'instance_id' => 'customer-care-points',
				'variant'     => 'support',
				'items'       => $greendawn_points,
			)
		);
		?>
		<section class="gd-section gd-support-contact" aria-label="<?php esc_attr_e( 'Customer support contact', 'greendawn' ); ?>">
			<div class="gd-shell">
				<?php if ( '' !== (string) greendawn_customer_care_field( 'contact_heading' ) ) : ?>
					<h2><?php echo esc_html( greendawn_customer_care_field( 'contact_heading' ) ); ?></h2>
				<?php endif; ?>
				<div class="gd-support-contact__grid">
					<div class="gd-support-whatsapp">
						<?php if ( '' !== (string) greendawn_customer_care_field( 'whatsapp_heading' ) ) : ?>
							<h3><?php echo esc_html( greendawn_customer_care_field( 'whatsapp_heading' ) ); ?></h3>
						<?php endif; ?>
						<?php if ( '' !== $greendawn_whatsapp && '' !== $greendawn_label ) : ?>
							<a class="gd-home-button gd-page-button--navy" href="<?php echo esc_url( $greendawn_whatsapp ); ?>"><?php echo esc_html( $greendawn_label ); ?><?php get_template_part( 'template-parts/home/arrow' ); ?></a>
						<?php endif; ?>
					</div>
					<div class="gd-support-form">
						<?php if ( '' !== (string) greendawn_customer_care_field( 'form_heading' ) ) : ?>
							<h3><?php echo esc_html( greendawn_customer_care_field( 'form_heading' ) ); ?></h3>
						<?php endif; ?>
						<?php
						get_template_part(
							'template-parts/components/form-content',
							null,
							array(
								'form_id'             => greendawn_customer_care_field( 'form', 0 ),
								'unavailable_message' => __( 'The support form is currently unavailable.', 'greendawn' ),
								'fallback_url'        => '' !== $greendawn_label ? $greendawn_whatsapp : '',
								'fallback_label'      => $greendawn_label,
							)
						);
						?>
					</div>
				</div>
			</div>
		</section>
	</article>
	<?php
endwhile;
get_footer();
