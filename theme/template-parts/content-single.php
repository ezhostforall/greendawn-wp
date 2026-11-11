<?php
/**
 * Single post content.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_editorial = is_singular( 'post' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $greendawn_editorial ? 'editorial-entry greendawn-editorial' : 'editorial-entry' ); ?>>
	<?php
	get_template_part(
		'template-parts/entry-hero',
		null,
		array(
			'show_date'  => true,
			'wide_media' => true,
			'editorial'  => $greendawn_editorial,
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
