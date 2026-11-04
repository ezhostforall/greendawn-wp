<?php
/**
 * Single post template.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) {
	the_post();
	get_template_part( 'template-parts/content', 'single' );
	echo '<div class="editorial-followup">';
	the_post_navigation();
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
	echo '</div>';
}

get_footer();
