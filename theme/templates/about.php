<?php
/**
 * Template Name: About
 * Template Post Type: page
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$greendawn_about_intro         = trim( (string) greendawn_get_field( 'about_intro', false, '' ) );
	$greendawn_about_story_heading = trim( (string) greendawn_get_field( 'about_story_heading', false, '' ) );
	$greendawn_about_story         = trim( (string) greendawn_get_field( 'about_story', false, '' ) );
	$greendawn_about_story_image   = wp_get_attachment_image( absint( greendawn_get_field( 'about_story_image', false, 0 ) ), 'large' );
	$greendawn_about_cta_label     = trim( (string) greendawn_get_field( 'about_cta_label', false, '' ) );
	$greendawn_about_cta_url       = sanitize_url( (string) greendawn_get_field( 'about_cta_url', false, '' ) );
	?>
	<article <?php post_class(); ?>>
		<header class="feature-hero">
			<div class="container feature-hero__inner">
				<div class="layout-stack feature-hero__copy">
					<?php the_title( '<h1 class="type-display">', '</h1>' ); ?>
					<?php if ( '' !== $greendawn_about_intro ) : ?>
						<p class="type-lead"><?php echo esc_html( $greendawn_about_intro ); ?></p>
					<?php endif; ?>
				</div>
				<?php get_template_part( 'template-parts/feature-hero-media' ); ?>
			</div>
		</header>

		<?php if ( '' !== $greendawn_about_story || $greendawn_about_story_image || get_the_content() ) : ?>
			<section class="container section layout-media about-story">
				<div class="prose">
					<h2><?php echo esc_html( '' !== $greendawn_about_story_heading ? $greendawn_about_story_heading : __( 'Our story', 'greendawn' ) ); ?></h2>
					<?php
					if ( '' !== $greendawn_about_story ) {
						echo wp_kses_post( $greendawn_about_story );
					} else {
						the_content();
					}
					?>
				</div>
				<?php if ( $greendawn_about_story_image ) : ?>
					<div><?php echo wp_kses_post( $greendawn_about_story_image ); ?></div>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<?php if ( '' !== $greendawn_about_cta_label && '' !== $greendawn_about_cta_url ) : ?>
			<div class="feature-cta">
				<div class="container section layout-row">
					<a class="button" href="<?php echo esc_url( $greendawn_about_cta_url ); ?>"><?php echo esc_html( $greendawn_about_cta_label ); ?></a>
				</div>
			</div>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
