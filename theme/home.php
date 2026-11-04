<?php
/**
 * Blog posts index for the Page assigned under Settings → Reading.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

get_header();

$greendawn_posts_page = (int) get_option( 'page_for_posts' );
$greendawn_blog_title = $greendawn_posts_page ? get_the_title( $greendawn_posts_page ) : '';
?>
<div class="blog-index">
	<header class="editorial-hero">
		<div class="container layout-stack">
			<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
			<h1 class="page-title type-display"><?php echo esc_html( '' !== $greendawn_blog_title ? $greendawn_blog_title : __( 'Blog', 'greendawn' ) ); ?></h1>
		</div>
	</header>
	<div class="container section">
		<?php get_template_part( 'template-parts/post-list' ); ?>
	</div>
</div>
<?php
get_footer();
