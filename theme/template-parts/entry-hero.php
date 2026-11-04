<?php
/**
 * Shared title, breadcrumb and featured image for editorial entries.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

$wp_starter_wide_media = ! empty( $args['wide_media'] );
?>
<header class="editorial-hero">
	<div class="entry-header layout-stack">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php the_title( '<h1 class="entry-title type-display">', '</h1>' ); ?>
		<?php if ( ! empty( $args['show_date'] ) ) : ?>
			<p class="entry-meta"><?php wp_starter_posted_on(); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="entry-thumbnail<?php echo $wp_starter_wide_media ? ' entry-thumbnail--wide' : ''; ?>">
			<?php
			the_post_thumbnail(
				'large',
				array(
					'loading' => 'eager',
					'sizes'   => $wp_starter_wide_media ? '(min-width: 72rem) 70rem, calc(100vw - 2rem)' : '(min-width: 56rem) 54rem, calc(100vw - 2rem)',
				)
			);
			?>
		</div>
	<?php endif; ?>
</header>
