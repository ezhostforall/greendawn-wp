<?php
/**
 * Template Name: Greendawn Enquire
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
	$greendawn_contacts        = array(
		'address' => greendawn_footer_value( 'contact' ),
		'phone'   => greendawn_footer_value( 'phone' ),
		'email'   => greendawn_footer_value( 'email' ),
	);
	$greendawn_contact_heading = (string) greendawn_enquire_field( 'contact_heading' );
	$greendawn_contact_copy    = (string) greendawn_enquire_field( 'contact_copy' );
	$greendawn_has_contact     = '' !== $greendawn_contact_heading || '' !== $greendawn_contact_copy || array_filter( $greendawn_contacts );
	$greendawn_charger         = apply_filters( 'greendawn_selected_charger', array() );
	$greendawn_steps           = array();
	foreach ( array( 'consultation', 'hardware_quote', 'site_survey', 'full_quote', 'acceptance', 'aftercare' ) as $greendawn_stage ) {
		$greendawn_steps[] = array(
			'title' => greendawn_enquire_field( $greendawn_stage . '_title' ),
			'copy'  => greendawn_enquire_field( $greendawn_stage . '_copy' ),
		);
	}
	?>
	<article <?php post_class( 'greendawn-page greendawn-enquire' ); ?>>
		<?php
		get_template_part(
			'template-parts/components/page-hero',
			null,
			array(
				'heading'  => greendawn_enquire_field( 'heading', get_the_title() ),
				'accent'   => greendawn_enquire_field( 'accent' ),
				'intro'    => greendawn_enquire_field( 'intro' ),
				'image_id' => 0,
			)
		);
		?>
		<section class="gd-section gd-enquire-section" aria-label="<?php esc_attr_e( 'Project enquiry', 'greendawn' ); ?>">
			<div class="gd-shell gd-enquire-layout">
				<div class="gd-enquire-form">
					<?php
					if ( '' !== (string) greendawn_enquire_field( 'form_heading' ) ) :
						?>
						<h2><?php echo esc_html( greendawn_enquire_field( 'form_heading' ) ); ?></h2>
					<?php endif; ?>
					<?php echo greendawn_charger_enquiry_summary(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Validated, escaped public product context. ?>
					<?php
					get_template_part(
						'template-parts/components/form-content',
						null,
						array(
							'form_id'       => greendawn_enquire_field( 'form', 0 ),
							'form_location' => 'enquire',
							'charger_slug'  => $greendawn_charger['slug'] ?? '',
							'fallback_url'  => $greendawn_has_contact ? '#enquire-contact' : '',
						)
					);
					?>
				</div>
				<?php get_template_part( 'template-parts/components/process-list', null, array( 'steps' => $greendawn_steps ) ); ?>
			</div>
		</section>
		<?php
		if ( $greendawn_has_contact ) {
			get_template_part(
				'template-parts/components/contact-panel',
				null,
				array_merge(
					$greendawn_contacts,
					array(
						'instance_id'   => 'enquire-contact',
						'heading'       => $greendawn_contact_heading,
						'copy'          => $greendawn_contact_copy,
						'address_label' => greendawn_enquire_field( 'address_label' ),
						'phone_label'   => greendawn_enquire_field( 'phone_label' ),
						'email_label'   => greendawn_enquire_field( 'email_label' ),
					)
				)
			);
		}
		?>
	</article>
	<?php
endwhile;
get_footer();
