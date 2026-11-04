<?php
/**
 * Front page: shows the static front page content, or the latest posts.
 * The business homepage is opt-in through its Page template.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

if ( is_page() && 'templates/business-homepage.php' === get_page_template_slug( get_queried_object_id() ) ) {
	get_template_part( 'templates/business-homepage' );
	return;
}

get_header();

if ( have_posts() ) {
	if ( is_page() ) {
		while ( have_posts() ) {
			the_post();
			get_template_part( 'template-parts/content', 'page' );
		}
	} else {
		echo '<h1 class="screen-reader-text">' . esc_html( get_bloginfo( 'name' ) ) . '</h1>';
		echo '<div class="post-grid">';
		while ( have_posts() ) {
			the_post();
			get_template_part( 'template-parts/content' );
		}
		echo '</div>';
		the_posts_pagination();
	}
} else {
	get_template_part( 'template-parts/content-none' );
}

get_footer();
