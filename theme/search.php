<?php
/**
 * Search results template.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/breadcrumbs' );
?>
<header class="page-header">
	<h1 class="page-title">
		<?php
		/* translators: %s: search query. */
		printf( esc_html__( 'Search results for: %s', 'wp-starter' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
		?>
	</h1>
</header>

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
<?php
get_footer();
