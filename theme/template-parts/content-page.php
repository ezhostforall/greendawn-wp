<?php
/**
 * Page content.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'editorial-entry editorial-page greendawn-editorial' ); ?>>
	<?php
	get_template_part(
		'template-parts/components/page-hero',
		null,
		array( 'heading' => get_the_title() )
	);
	?>
	<?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
		<div class="entry-thumbnail">
			<?php
			the_post_thumbnail(
				'large',
				array(
					'loading' => 'eager',
					'sizes'   => '(min-width: 56rem) 54rem, calc(100vw - 2rem)',
				)
			);
			?>
		</div>
	<?php endif; ?>
	<div class="entry-content">
		<?php
		the_content();
		wp_link_pages();
		?>
	</div>
</article>
