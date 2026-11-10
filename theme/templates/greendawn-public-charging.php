<?php
/**
 * Template Name: Greendawn Public Charging
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
	$greendawn_posts    = greendawn_page_records( greendawn_public_field( 'posts', array() ), 'post' );
	$greendawn_benefits = array();
	foreach ( array( 'revenue', 'loyalty', 'futureproof', 'sustainability' ) as $greendawn_key ) {
		$greendawn_benefits[] = array(
			'title' => greendawn_public_field( $greendawn_key . '_title' ),
			'copy'  => greendawn_public_field( $greendawn_key . '_copy' ),
		);
	}
	$greendawn_steps = array();
	foreach ( array( 'consult', 'design', 'install', 'manage' ) as $greendawn_key ) {
		$greendawn_steps[] = array(
			'title' => greendawn_public_field( $greendawn_key . '_title' ),
			'copy'  => greendawn_public_field( $greendawn_key . '_copy' ),
		);
	}
	?>
	<article <?php post_class( 'greendawn-page greendawn-public-charging' ); ?>>
		<?php
		get_template_part(
			'template-parts/components/page-hero',
			null,
			array(
				'heading'  => greendawn_public_field( 'heading', get_the_title() ),
				'accent'   => greendawn_public_field( 'accent' ),
				'intro'    => greendawn_public_field( 'intro' ),
				'image_id' => greendawn_public_field( 'hero_image', get_post_thumbnail_id() ),
			)
		);
		get_template_part(
			'template-parts/components/feature-grid',
			null,
			array(
				'instance_id' => 'public-benefits',
				'heading'     => greendawn_public_field( 'benefits_heading' ),
				'items'       => $greendawn_benefits,
			)
		);
		get_template_part(
			'template-parts/components/charger-collection',
			null,
			array(
				'instance_id' => 'public',
				'heading'     => greendawn_public_field( 'chargers_heading' ),
				'intro'       => greendawn_public_field( 'chargers_intro' ),
				'post_ids'    => greendawn_public_field( 'chargers', array() ),
				'items'       => array(
					array(
						'title' => greendawn_public_field( 'ac_title' ),
						'copy'  => greendawn_public_field( 'ac_copy' ),
					),
					array(
						'title' => greendawn_public_field( 'dc_title' ),
						'copy'  => greendawn_public_field( 'dc_copy' ),
					),
				),
				'outro'       => greendawn_public_field( 'chargers_outro' ),
			)
		);
		if ( '' !== greendawn_public_field( 'cta_heading' ) ) {
			get_template_part(
				'template-parts/components/cta-band',
				null,
				array(
					'instance_id' => 'public-cta',
					'heading'     => greendawn_public_field( 'cta_heading' ),
					'copy'        => greendawn_public_field( 'cta_copy' ),
					'label'       => greendawn_public_field( 'cta_label' ),
					'url'         => greendawn_public_field( 'cta_url' ),
				)
			);
		}
		if ( '' !== greendawn_public_field( 'process_title' ) ) {
			get_template_part(
				'template-parts/components/process',
				null,
				array(
					'instance_id' => 'public-process',
					'heading'     => greendawn_public_field( 'process_title' ),
					'steps'       => $greendawn_steps,
					'intro'       => greendawn_public_field( 'process_intro' ),
				)
			);
		}
		if ( '' !== greendawn_public_field( 'final_cta_heading' ) ) {
			get_template_part(
				'template-parts/components/cta-band',
				null,
				array(
					'instance_id' => 'public-final-cta',
					'heading'     => greendawn_public_field( 'final_cta_heading' ),
					'copy'        => greendawn_public_field( 'final_cta_copy' ),
					'label'       => greendawn_public_field( 'final_cta_label' ),
					'url'         => greendawn_public_field( 'final_cta_url' ),
				)
			);
		}
		get_template_part(
			'template-parts/components/articles',
			null,
			array(
				'instance_id' => 'public',
				'heading'     => greendawn_public_field( 'posts_heading' ),
				'posts'       => $greendawn_posts,
			)
		);
		?>
	</article>
	<?php
endwhile;
get_footer();
