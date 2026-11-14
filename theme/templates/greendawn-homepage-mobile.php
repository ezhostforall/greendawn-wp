<?php
/**
 * Template Name: Greendawn Homepage — Mobile Refresh
 * Template Post Type: page
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'gd-home' ); ?> data-gd-home-motion data-gd-home-refresh>
		<?php get_template_part( 'template-parts/home/hero', null, array( 'mobile_refresh' => true ) ); ?>
		<?php get_template_part( 'template-parts/home/trust' ); ?>
		<?php get_template_part( 'template-parts/home/problem' ); ?>
		<?php get_template_part( 'template-parts/home/system' ); ?>
		<?php get_template_part( 'template-parts/home/process', null, array( 'mobile_refresh' => true ) ); ?>
		<?php get_template_part( 'template-parts/home/featured-project', null, array( 'mobile_refresh' => true ) ); ?>
		<?php get_template_part( 'template-parts/home/surveys', null, array( 'mobile_refresh' => true ) ); ?>
		<?php get_template_part( 'template-parts/home/solutions', null, array( 'mobile_refresh' => true ) ); ?>
		<?php get_template_part( 'template-parts/home/aftercare', null, array( 'mobile_refresh' => true ) ); ?>
		<?php get_template_part( 'template-parts/home/supporting-proof' ); ?>
		<?php get_template_part( 'template-parts/home/insights', null, array( 'mobile_refresh' => true ) ); ?>
		<?php get_template_part( 'template-parts/home/final-enquiry', null, array( 'mobile_refresh' => true ) ); ?>
	</article>
	<?php
endwhile;

get_footer();
