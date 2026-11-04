<?php
/**
 * Single page template.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) {
	the_post();
	get_template_part( 'template-parts/content', 'page' );
	if ( comments_open() || get_comments_number() ) {
		echo '<div class="editorial-followup">';
		comments_template();
		echo '</div>';
	}
}

get_footer();
