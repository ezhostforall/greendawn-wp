<?php
/**
 * Template Name: Greendawn Workplace Charging
 * Template Post Type: page
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$greendawn_posts    = greendawn_page_records( greendawn_workplace_field( 'posts', array() ), 'post' );
	$greendawn_benefits = array();
	foreach ( array( 'cost', 'reliability', 'energy', 'sustainability' ) as $greendawn_key ) {
		$greendawn_benefits[] = array(
			'title' => greendawn_workplace_field( $greendawn_key . '_title' ),
			'copy'  => greendawn_workplace_field( $greendawn_key . '_copy' ),
		);
	}
	$greendawn_steps = array();
	foreach ( array( 'consult', 'design', 'install', 'manage' ) as $greendawn_key ) {
		$greendawn_steps[] = array(
			'title' => greendawn_workplace_field( $greendawn_key . '_title' ),
			'copy'  => greendawn_workplace_field( $greendawn_key . '_copy' ),
		);
	}
	?>
	<article <?php post_class( 'greendawn-page greendawn-workplace' ); ?>>
		<?php
		get_template_part(
			'template-parts/components/page-hero',
			null,
			array(
				'heading'  => greendawn_workplace_field( 'heading', get_the_title() ),
				'accent'   => greendawn_workplace_field( 'accent' ),
				'intro'    => greendawn_workplace_field( 'intro' ),
				'image_id' => greendawn_workplace_field( 'hero_image', get_post_thumbnail_id() ),
			)
		);
		get_template_part(
			'template-parts/components/feature-grid',
			null,
			array(
				'instance_id' => 'workplace-benefits',
				'heading'     => greendawn_workplace_field( 'benefits_heading' ),
				'items'       => $greendawn_benefits,
			)
		);
		get_template_part(
			'template-parts/components/charger-collection',
			null,
			array(
				'instance_id' => 'workplace',
				'heading'     => greendawn_workplace_field( 'chargers_heading' ),
				'intro'       => greendawn_workplace_field( 'chargers_intro' ),
				'post_ids'    => greendawn_workplace_field( 'chargers', array() ),
			)
		);
		if ( '' !== greendawn_workplace_field( 'cta_heading' ) ) {
			get_template_part(
				'template-parts/components/cta-band',
				null,
				array(
					'instance_id' => 'workplace-cta',
					'heading'     => greendawn_workplace_field( 'cta_heading' ),
					'copy'        => greendawn_workplace_field( 'cta_copy' ),
					'label'       => greendawn_workplace_field( 'cta_label' ),
					'url'         => greendawn_workplace_field( 'cta_url' ),
				)
			);
		}
		if ( '' !== greendawn_workplace_field( 'process_title' ) ) {
			get_template_part(
				'template-parts/components/process',
				null,
				array(
					'instance_id' => 'workplace-process',
					'heading'     => greendawn_workplace_field( 'process_title' ),
					'steps'       => $greendawn_steps,
					'media'       => array( greendawn_workplace_field( 'process_image_1', 0 ), greendawn_workplace_field( 'process_image_2', 0 ) ),
				)
			);
		}
		get_template_part(
			'template-parts/components/form-panel',
			null,
			array(
				'instance_id' => 'workplace-form',
				'heading'     => greendawn_workplace_field( 'form_heading' ),
				'form_id'     => greendawn_workplace_field( 'form', 0 ),
			)
		);
		get_template_part(
			'template-parts/components/articles',
			null,
			array(
				'instance_id' => 'workplace',
				'heading'     => greendawn_workplace_field( 'posts_heading' ),
				'posts'       => $greendawn_posts,
			)
		);
		?>
	</article>
	<?php
endwhile;
get_footer();
