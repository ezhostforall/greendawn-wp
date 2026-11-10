<?php
/**
 * Template Name: Greendawn Fleet Charging
 * Template Post Type: page
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	if ( post_password_required() ) {
		?>
		<article class="container section"><h1><?php the_title(); ?></h1><?php echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native WordPress password form. ?></article>
		<?php
		continue;
	}
	$greendawn_posts    = greendawn_page_records( greendawn_fleet_field( 'posts', array() ), 'post' );
	$greendawn_benefits = array();
	foreach ( array( 'cost', 'efficiency', 'energy', 'sustainability' ) as $greendawn_key ) {
		$greendawn_benefits[] = array(
			'title' => greendawn_fleet_field( $greendawn_key . '_title' ),
			'copy'  => greendawn_fleet_field( $greendawn_key . '_copy' ),
		);
	}
	$greendawn_steps = array();
	foreach ( array( 'consult', 'design', 'install', 'manage' ) as $greendawn_key ) {
		$greendawn_steps[] = array(
			'title' => greendawn_fleet_field( $greendawn_key . '_title' ),
			'copy'  => greendawn_fleet_field( $greendawn_key . '_copy' ),
		);
	}
	?>
	<article <?php post_class( 'greendawn-page greendawn-fleet' ); ?>>
		<?php
		get_template_part(
			'template-parts/components/page-hero',
			null,
			array(
				'heading'  => greendawn_fleet_field( 'heading', get_the_title() ),
				'accent'   => greendawn_fleet_field( 'accent' ),
				'intro'    => greendawn_fleet_field( 'intro' ),
				'image_id' => greendawn_fleet_field( 'hero_image', get_post_thumbnail_id() ),
			)
		);
		get_template_part(
			'template-parts/components/feature-grid',
			null,
			array(
				'instance_id' => 'fleet-benefits',
				'heading'     => greendawn_fleet_field( 'benefits_heading' ),
				'items'       => $greendawn_benefits,
			)
		);
		get_template_part(
			'template-parts/components/charger-collection',
			null,
			array(
				'instance_id' => 'fleet',
				'heading'     => greendawn_fleet_field( 'chargers_heading' ),
				'intro'       => greendawn_fleet_field( 'chargers_intro' ),
				'post_ids'    => greendawn_fleet_field( 'chargers', array() ),
			)
		);
		if ( '' !== greendawn_fleet_field( 'cta_heading' ) ) {
			get_template_part(
				'template-parts/components/cta-band',
				null,
				array(
					'instance_id' => 'fleet-cta',
					'heading'     => greendawn_fleet_field( 'cta_heading' ),
					'copy'        => greendawn_fleet_field( 'cta_copy' ),
					'label'       => greendawn_fleet_field( 'cta_label' ),
					'url'         => greendawn_fleet_field( 'cta_url' ),
				)
			);
		}
		if ( '' !== greendawn_fleet_field( 'process_title' ) ) {
			get_template_part(
				'template-parts/components/process',
				null,
				array(
					'instance_id' => 'fleet-process',
					'heading'     => greendawn_fleet_field( 'process_title' ),
					'steps'       => $greendawn_steps,
					'media'       => array( greendawn_fleet_field( 'process_image_1', 0 ), greendawn_fleet_field( 'process_image_2', 0 ) ),
				)
			);
		}
		get_template_part(
			'template-parts/components/articles',
			null,
			array(
				'instance_id' => 'fleet',
				'heading'     => greendawn_fleet_field( 'posts_heading' ),
				'posts'       => $greendawn_posts,
			)
		);
		?>
	</article>
	<?php
endwhile;
get_footer();
