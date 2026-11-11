<?php
/**
 * Template Name: Greendawn Gallery
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
	$greendawn_stories       = greendawn_gallery_field( 'stories', array() );
	$greendawn_photos        = greendawn_gallery_images( greendawn_gallery_field( 'photos', array() ) );
	$greendawn_photo_heading = (string) greendawn_gallery_field( 'photo_heading' );
	$greendawn_cta_heading   = (string) greendawn_gallery_field( 'cta_heading' );
	$greendawn_has_photos    = (bool) $greendawn_photos;
	?>
	<article <?php post_class( 'greendawn-page greendawn-gallery' ); ?>>
		<?php
		get_template_part(
			'template-parts/components/page-hero',
			null,
			array(
				'heading' => greendawn_gallery_field( 'heading', get_the_title() ),
				'accent'  => greendawn_gallery_field( 'accent' ),
				'intro'   => greendawn_gallery_field( 'intro' ),
			)
		);
		foreach ( is_array( $greendawn_stories ) ? $greendawn_stories : array() as $greendawn_story ) {
			if ( empty( $greendawn_story['title'] ) ) {
				continue;
			}
			$greendawn_story['images'] = greendawn_gallery_images( $greendawn_story['images'] ?? array() );
			$greendawn_has_photos      = $greendawn_has_photos || $greendawn_story['images'];
			get_template_part( 'template-parts/components/case-study', null, $greendawn_story );
		}
		if ( '' !== $greendawn_cta_heading ) {
			get_template_part(
				'template-parts/components/cta-band',
				null,
				array(
					'heading' => $greendawn_cta_heading,
					'copy'    => greendawn_gallery_field( 'cta_copy' ),
					'label'   => greendawn_gallery_field( 'cta_label' ),
					'url'     => home_url( '/enquire/' ),
				)
			);
		}
		if ( $greendawn_photos ) :
			?>
			<section class="gd-section gd-gallery-photos" aria-label="<?php esc_attr_e( 'Project photo gallery', 'greendawn' ); ?>">
				<div class="gd-shell">
					<?php if ( '' !== $greendawn_photo_heading ) : ?>
						<h2><?php echo esc_html( $greendawn_photo_heading ); ?></h2>
					<?php endif; ?>
					<?php
					get_template_part( 'template-parts/components/photo-grid', null, array( 'images' => $greendawn_photos ) );
					?>
				</div>
			</section>
			<?php
		endif;
		if ( $greendawn_has_photos ) {
			get_template_part( 'template-parts/components/photo-dialog' );
		}
		?>
	</article>
	<?php
endwhile;
get_footer();
