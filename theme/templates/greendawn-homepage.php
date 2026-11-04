<?php
/**
 * Template Name: Greendawn Homepage
 * Template Post Type: page
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'gd-home' ); ?>>
		<?php get_template_part( 'template-parts/home/hero' ); ?>
		<?php get_template_part( 'template-parts/home/trust' ); ?>
		<?php get_template_part( 'template-parts/home/problem' ); ?>
		<?php get_template_part( 'template-parts/home/system' ); ?>
		<?php get_template_part( 'template-parts/home/process' ); ?>
	</article>
	<?php
endwhile;

get_footer();
