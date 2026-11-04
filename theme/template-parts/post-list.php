<?php
/**
 * Card grid and pagination for post listings.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;
?>
<?php if ( have_posts() ) : ?>
	<div class="post-grid">
		<?php
		while ( have_posts() ) {
			the_post();
			get_template_part( 'template-parts/content' );
		}
		?>
	</div>
	<?php the_posts_pagination(); ?>
<?php else : ?>
	<?php get_template_part( 'template-parts/content-none' ); ?>
<?php endif; ?>
