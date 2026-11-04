<?php
/**
 * Blog posts index for the Page assigned under Settings → Reading.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

get_header();

$wp_starter_posts_page = (int) get_option( 'page_for_posts' );
$wp_starter_blog_title = $wp_starter_posts_page ? get_the_title( $wp_starter_posts_page ) : '';
?>
<div class="blog-index">
	<header class="editorial-hero">
		<div class="container layout-stack">
			<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
			<h1 class="page-title type-display"><?php echo esc_html( '' !== $wp_starter_blog_title ? $wp_starter_blog_title : __( 'Blog', 'wp-starter' ) ); ?></h1>
		</div>
	</header>
	<div class="container section">
		<?php get_template_part( 'template-parts/post-list' ); ?>
	</div>
</div>
<?php
get_footer();
