<?php
/**
 * Archive template (categories, tags, dates, authors, post type archives).
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_category() || is_tag() || is_author() || is_date() ) {
	$greendawn_title = is_category() || is_tag() ? single_term_title( '', false ) : wp_strip_all_tags( get_the_archive_title() );
	?>
	<div class="blog-index greendawn-editorial">
		<?php
		get_template_part( 'template-parts/components/page-hero', null, array( 'heading' => $greendawn_title ) );
		get_template_part( 'template-parts/editorial-listing', null, array( 'description' => get_the_archive_description() ) );
		?>
	</div>
	<?php
	get_footer();
	return;
}

get_template_part( 'template-parts/breadcrumbs' );
?>
<header class="page-header">
	<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
	<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
</header>

<?php
get_template_part( 'template-parts/post-list' );

get_footer();
