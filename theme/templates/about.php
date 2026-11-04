<?php
/**
 * Template Name: About
 * Template Post Type: page
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$wp_starter_about_intro         = trim( (string) wp_starter_get_field( 'about_intro', false, '' ) );
	$wp_starter_about_story_heading = trim( (string) wp_starter_get_field( 'about_story_heading', false, '' ) );
	$wp_starter_about_story         = trim( (string) wp_starter_get_field( 'about_story', false, '' ) );
	$wp_starter_about_story_image   = wp_get_attachment_image( absint( wp_starter_get_field( 'about_story_image', false, 0 ) ), 'large' );
	$wp_starter_about_cta_label     = trim( (string) wp_starter_get_field( 'about_cta_label', false, '' ) );
	$wp_starter_about_cta_url       = sanitize_url( (string) wp_starter_get_field( 'about_cta_url', false, '' ) );
	?>
	<article <?php post_class(); ?>>
		<header class="feature-hero">
			<div class="container feature-hero__inner">
				<div class="layout-stack feature-hero__copy">
					<?php the_title( '<h1 class="type-display">', '</h1>' ); ?>
					<?php if ( '' !== $wp_starter_about_intro ) : ?>
						<p class="type-lead"><?php echo esc_html( $wp_starter_about_intro ); ?></p>
					<?php endif; ?>
				</div>
				<?php get_template_part( 'template-parts/feature-hero-media' ); ?>
			</div>
		</header>

		<?php if ( '' !== $wp_starter_about_story || $wp_starter_about_story_image || get_the_content() ) : ?>
			<section class="container section layout-media about-story">
				<div class="prose">
					<h2><?php echo esc_html( '' !== $wp_starter_about_story_heading ? $wp_starter_about_story_heading : __( 'Our story', 'wp-starter' ) ); ?></h2>
					<?php
					if ( '' !== $wp_starter_about_story ) {
						echo wp_kses_post( $wp_starter_about_story );
					} else {
						the_content();
					}
					?>
				</div>
				<?php if ( $wp_starter_about_story_image ) : ?>
					<div><?php echo wp_kses_post( $wp_starter_about_story_image ); ?></div>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<?php if ( '' !== $wp_starter_about_cta_label && '' !== $wp_starter_about_cta_url ) : ?>
			<div class="feature-cta">
				<div class="container section layout-row">
					<a class="button" href="<?php echo esc_url( $wp_starter_about_cta_url ); ?>"><?php echo esc_html( $wp_starter_about_cta_label ); ?></a>
				</div>
			</div>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
