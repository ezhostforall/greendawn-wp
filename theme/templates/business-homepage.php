<?php
/**
 * Template Name: Business Homepage
 * Template Post Type: page
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$wp_starter_home_title    = trim( (string) wp_starter_get_field( 'home_hero_title', false, '' ) );
	$wp_starter_home_hero     = trim( (string) wp_starter_get_field( 'home_hero_text', false, '' ) );
	$wp_starter_home_intro    = (string) wp_starter_get_field( 'home_intro', false, '' );
	$wp_starter_home_cta      = trim( (string) wp_starter_get_field( 'home_cta_label', false, '' ) );
	$wp_starter_home_url      = sanitize_url( (string) wp_starter_get_field( 'home_cta_url', false, '' ) );
	$wp_starter_home_heading  = trim( (string) wp_starter_get_field( 'home_cta_heading', false, '' ) );
	$wp_starter_home_services = array_filter( array_map( 'absint', (array) wp_starter_get_field( 'home_featured_services', false, array() ) ) );
	$wp_starter_home_contact  = '' !== $wp_starter_home_cta && '' !== $wp_starter_home_url && '#' !== $wp_starter_home_url;
	$wp_starter_home_query    = null;

	if ( $wp_starter_home_services && post_type_exists( 'service' ) ) {
		$wp_starter_home_query = new WP_Query(
			array(
				'post_type'      => 'service',
				'post_status'    => 'publish',
				'post__in'       => $wp_starter_home_services,
				'orderby'        => 'post__in',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
			)
		);
	}
	$wp_starter_home_show_services = $wp_starter_home_query && $wp_starter_home_query->have_posts();
	?>
	<article <?php post_class( 'business-home' ); ?>>
		<header class="feature-hero">
			<div class="container feature-hero__inner">
				<div class="layout-stack feature-hero__copy">
					<h1 class="type-display"><?php echo esc_html( '' !== $wp_starter_home_title ? $wp_starter_home_title : get_the_title() ); ?></h1>
					<?php if ( '' !== $wp_starter_home_hero ) : ?>
						<p class="type-lead"><?php echo esc_html( $wp_starter_home_hero ); ?></p>
					<?php endif; ?>
					<?php if ( $wp_starter_home_contact || $wp_starter_home_show_services ) : ?>
						<div class="layout-row business-home__actions">
							<?php if ( $wp_starter_home_contact ) : ?>
								<a class="button" href="<?php echo esc_url( $wp_starter_home_url ); ?>"><?php echo esc_html( $wp_starter_home_cta ); ?></a>
							<?php endif; ?>
							<?php if ( $wp_starter_home_show_services ) : ?>
								<a class="button button--outline" href="#featured-services"><?php esc_html_e( 'Explore services', 'wp-starter' ); ?></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php get_template_part( 'template-parts/feature-hero-media', null, array( 'priority' => true ) ); ?>
			</div>
		</header>

		<?php if ( '' !== trim( $wp_starter_home_intro ) || get_the_content() ) : ?>
			<div class="container business-home__intro">
				<div class="prose">
					<?php
					if ( '' !== trim( $wp_starter_home_intro ) ) {
						echo wp_kses_post( $wp_starter_home_intro );
					} else {
						the_content();
					}
					?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $wp_starter_home_show_services ) : ?>
			<section class="container section layout-stack business-home__services" id="featured-services" aria-labelledby="featured-services-heading">
				<h2 id="featured-services-heading"><?php esc_html_e( 'Featured services', 'wp-starter' ); ?></h2>
				<div class="layout-grid service-grid">
					<?php
					while ( $wp_starter_home_query->have_posts() ) {
						$wp_starter_home_query->the_post();
						get_template_part( 'template-parts/service-card', null, array( 'nested' => true ) );
					}
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $wp_starter_home_contact && '' !== $wp_starter_home_heading ) : ?>
			<section class="feature-cta">
				<div class="container section layout-row">
					<h2><?php echo esc_html( $wp_starter_home_heading ); ?></h2>
					<a class="button" href="<?php echo esc_url( $wp_starter_home_url ); ?>"><?php echo esc_html( $wp_starter_home_cta ); ?></a>
				</div>
			</section>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
