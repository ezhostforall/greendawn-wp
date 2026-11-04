<?php
/**
 * Template Name: Services Overview
 * Template Post Type: page
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$greendawn_services_intro = greendawn_get_field( 'services_intro', false, '' );
	$greendawn_services_query = new WP_Query(
		array(
			'post_type'      => 'service',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array(
				'title' => 'ASC',
				'ID'    => 'ASC',
			),
			'no_found_rows'  => true,
		)
	);
	?>
	<article <?php post_class( 'services-overview' ); ?>>
		<header class="feature-hero">
			<div class="container service-breadcrumbs">
				<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
			</div>
			<div class="container layout-stack feature-hero__copy">
				<?php the_title( '<h1 class="type-display">', '</h1>' ); ?>
				<?php if ( is_string( $greendawn_services_intro ) && '' !== trim( $greendawn_services_intro ) ) : ?>
					<div class="type-lead"><?php echo wp_kses_post( wpautop( esc_html( $greendawn_services_intro ) ) ); ?></div>
				<?php elseif ( get_the_content() ) : ?>
					<div class="prose"><?php the_content(); ?></div>
				<?php endif; ?>
			</div>
		</header>

		<div class="container section">
			<?php if ( $greendawn_services_query->have_posts() ) : ?>
				<div class="layout-grid service-grid">
					<?php
					while ( $greendawn_services_query->have_posts() ) {
						$greendawn_services_query->the_post();
						get_template_part( 'template-parts/service-card' );
					}
					wp_reset_postdata();
					?>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'There are no services to display yet.', 'greendawn' ); ?></p>
			<?php endif; ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
