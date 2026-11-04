<?php
/**
 * Template Name: Business Homepage
 * Template Post Type: page
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$greendawn_home_title    = trim( (string) greendawn_get_field( 'home_hero_title', false, '' ) );
	$greendawn_home_hero     = trim( (string) greendawn_get_field( 'home_hero_text', false, '' ) );
	$greendawn_home_intro    = (string) greendawn_get_field( 'home_intro', false, '' );
	$greendawn_home_cta      = trim( (string) greendawn_get_field( 'home_cta_label', false, '' ) );
	$greendawn_home_url      = sanitize_url( (string) greendawn_get_field( 'home_cta_url', false, '' ) );
	$greendawn_home_heading  = trim( (string) greendawn_get_field( 'home_cta_heading', false, '' ) );
	$greendawn_home_services = array_filter( array_map( 'absint', (array) greendawn_get_field( 'home_featured_services', false, array() ) ) );
	$greendawn_home_contact  = '' !== $greendawn_home_cta && '' !== $greendawn_home_url && '#' !== $greendawn_home_url;
	$greendawn_home_query    = null;

	if ( $greendawn_home_services && post_type_exists( 'service' ) ) {
		$greendawn_home_query = new WP_Query(
			array(
				'post_type'      => 'service',
				'post_status'    => 'publish',
				'post__in'       => $greendawn_home_services,
				'orderby'        => 'post__in',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
			)
		);
	}
	$greendawn_home_show_services = $greendawn_home_query && $greendawn_home_query->have_posts();
	?>
	<article <?php post_class( 'business-home' ); ?>>
		<header class="feature-hero">
			<div class="container feature-hero__inner">
				<div class="layout-stack feature-hero__copy">
					<h1 class="type-display"><?php echo esc_html( '' !== $greendawn_home_title ? $greendawn_home_title : get_the_title() ); ?></h1>
					<?php if ( '' !== $greendawn_home_hero ) : ?>
						<p class="type-lead"><?php echo esc_html( $greendawn_home_hero ); ?></p>
					<?php endif; ?>
					<?php if ( $greendawn_home_contact || $greendawn_home_show_services ) : ?>
						<div class="layout-row business-home__actions">
							<?php if ( $greendawn_home_contact ) : ?>
								<a class="button" href="<?php echo esc_url( $greendawn_home_url ); ?>"><?php echo esc_html( $greendawn_home_cta ); ?></a>
							<?php endif; ?>
							<?php if ( $greendawn_home_show_services ) : ?>
								<a class="button button--outline" href="#featured-services"><?php esc_html_e( 'Explore services', 'greendawn' ); ?></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php get_template_part( 'template-parts/feature-hero-media', null, array( 'priority' => true ) ); ?>
			</div>
		</header>

		<?php if ( '' !== trim( $greendawn_home_intro ) || get_the_content() ) : ?>
			<div class="container business-home__intro">
				<div class="prose">
					<?php
					if ( '' !== trim( $greendawn_home_intro ) ) {
						echo wp_kses_post( $greendawn_home_intro );
					} else {
						the_content();
					}
					?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $greendawn_home_show_services ) : ?>
			<section class="container section layout-stack business-home__services" id="featured-services" aria-labelledby="featured-services-heading">
				<h2 id="featured-services-heading"><?php esc_html_e( 'Featured services', 'greendawn' ); ?></h2>
				<div class="layout-grid service-grid">
					<?php
					while ( $greendawn_home_query->have_posts() ) {
						$greendawn_home_query->the_post();
						get_template_part( 'template-parts/service-card', null, array( 'nested' => true ) );
					}
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $greendawn_home_contact && '' !== $greendawn_home_heading ) : ?>
			<section class="feature-cta">
				<div class="container section layout-row">
					<h2><?php echo esc_html( $greendawn_home_heading ); ?></h2>
					<a class="button" href="<?php echo esc_url( $greendawn_home_url ); ?>"><?php echo esc_html( $greendawn_home_cta ); ?></a>
				</div>
			</section>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
