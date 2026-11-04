<?php
/**
 * Single post content.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'editorial-entry' ); ?>>
	<?php
	get_template_part(
		'template-parts/entry-hero',
		null,
		array(
			'show_date'  => true,
			'wide_media' => true,
		)
	);
	?>
	<div class="entry-content">
		<?php
		the_content();
		wp_link_pages();
		?>
	</div>
	<footer class="entry-footer">
		<?php the_category( ', ' ); ?>
		<?php the_tags( '<p>', ', ', '</p>' ); ?>
	</footer>
</article>
