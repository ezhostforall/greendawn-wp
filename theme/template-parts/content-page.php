<?php
/**
 * Page content.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'editorial-entry editorial-page' ); ?>>
	<?php get_template_part( 'template-parts/entry-hero' ); ?>
	<div class="entry-content">
		<?php
		the_content();
		wp_link_pages();
		?>
	</div>
</article>
