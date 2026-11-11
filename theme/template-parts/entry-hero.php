<?php
/**
 * Shared title, breadcrumb and featured image for editorial entries.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_wide_media = ! empty( $args['wide_media'] );
$greendawn_editorial  = ! empty( $args['editorial'] );
?>
<header class="editorial-hero<?php echo $greendawn_editorial ? ' gd-page-hero' : ''; ?>">
	<div class="entry-header layout-stack<?php echo $greendawn_editorial ? ' gd-shell' : ''; ?>">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php if ( $greendawn_editorial ) : ?>
			<h1 class="entry-title"><?php echo esc_html( get_the_title() ? get_the_title() : __( 'Untitled article', 'greendawn' ) ); ?></h1>
		<?php else : ?>
			<?php the_title( '<h1 class="entry-title type-display">', '</h1>' ); ?>
		<?php endif; ?>
		<?php if ( ! empty( $args['show_date'] ) ) : ?>
			<p class="entry-meta">
				<?php greendawn_posted_on(); ?>
				<?php if ( $greendawn_editorial && get_the_author() ) : ?>
					<span><?php echo esc_html( sprintf( /* translators: %s: author display name. */ __( 'By %s', 'greendawn' ), get_the_author() ) ); ?></span>
				<?php endif; ?>
			</p>
		<?php endif; ?>
	</div>
	<?php if ( has_post_thumbnail() && ( ! $greendawn_editorial || ! post_password_required() ) ) : ?>
		<div class="entry-thumbnail<?php echo $greendawn_wide_media ? ' entry-thumbnail--wide' : ''; ?><?php echo $greendawn_editorial ? ' gd-shell' : ''; ?>">
			<?php
			$greendawn_image_attributes = array(
				'loading' => 'eager',
				'sizes'   => $greendawn_wide_media ? '(min-width: 72rem) 70rem, calc(100vw - 2rem)' : '(min-width: 56rem) 54rem, calc(100vw - 2rem)',
			);
			if ( $greendawn_editorial ) {
				$greendawn_image_attributes['sizes']         = '(min-width: 95rem) 92rem, calc(100vw - 2rem)';
				$greendawn_image_attributes['fetchpriority'] = 'high';
			}
			the_post_thumbnail( 'large', $greendawn_image_attributes );
			?>
		</div>
	<?php endif; ?>
</header>
