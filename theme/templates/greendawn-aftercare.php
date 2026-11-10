<?php
/**
 * Template Name: Greendawn Aftercare
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
	$greendawn_posts = greendawn_page_records( greendawn_aftercare_field( 'posts', array() ), 'post' );
	?>
	<article <?php post_class( 'greendawn-page greendawn-aftercare-page' ); ?>>
		<?php
		get_template_part(
			'template-parts/components/page-hero',
			null,
			array(
				'heading'  => greendawn_aftercare_field( 'heading', get_the_title() ),
				'accent'   => greendawn_aftercare_field( 'accent' ),
				'intro'    => greendawn_aftercare_field( 'intro' ),
				'image_id' => greendawn_aftercare_field( 'hero_image', get_post_thumbnail_id() ),
			)
		);
		get_template_part(
			'template-parts/components/media-copy',
			null,
			array(
				'instance_id' => 'aftercare-introduction',
				'heading'     => greendawn_aftercare_field( 'story_heading' ),
				'copy'        => greendawn_aftercare_field( 'story' ),
				'image_id'    => greendawn_aftercare_field( 'story_image', 0 ),
			)
		);
		$greendawn_offerings = array();
		foreach ( range( 1, 4 ) as $greendawn_number ) {
			$greendawn_offerings[] = array(
				'title' => greendawn_aftercare_field( 'offering_' . $greendawn_number . '_title' ),
				'copy'  => greendawn_aftercare_field( 'offering_' . $greendawn_number . '_copy' ),
			);
		}
		get_template_part(
			'template-parts/components/offering-grid',
			null,
			array(
				'instance_id' => 'aftercare-offerings',
				'heading'     => greendawn_aftercare_field( 'offerings_heading' ),
				'items'       => $greendawn_offerings,
			)
		);
		get_template_part(
			'template-parts/components/faq',
			null,
			array(
				'instance_id'  => 'aftercare-faq',
				'heading'      => greendawn_aftercare_field( 'faq_heading' ),
				'items'        => greendawn_aftercare_field( 'faqs', array() ),
				'form_id'      => greendawn_aftercare_field( 'form', 0 ),
				'form_heading' => greendawn_aftercare_field( 'form_heading' ),
			)
		);
		get_template_part(
			'template-parts/components/articles',
			null,
			array(
				'instance_id' => 'aftercare',
				'heading'     => greendawn_aftercare_field( 'posts_heading' ),
				'posts'       => $greendawn_posts,
			)
		);
		?>
	</article>
	<?php
endwhile;
get_footer();
