<?php
/**
 * Archive template (categories, tags, dates, authors, post type archives).
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/breadcrumbs' );
?>
<header class="page-header">
	<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
	<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
</header>

<?php
get_template_part( 'template-parts/post-list' );

get_footer();
