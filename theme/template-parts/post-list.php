<?php
/**
 * Card grid and pagination for post listings.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
?>
<?php if ( have_posts() ) : ?>
	<div class="post-grid">
		<?php
		while ( have_posts() ) {
			the_post();
			if ( ! empty( $args['editorial'] ) ) {
				get_template_part(
					'template-parts/components/article-card',
					null,
					array(
						'editorial' => true,
						'post_id'   => get_the_ID(),
						'title'     => get_the_title(),
						'url'       => get_permalink(),
						'image_id'  => post_password_required() ? 0 : get_post_thumbnail_id(),
						'date'      => get_the_date(),
						'datetime'  => get_the_date( DATE_W3C ),
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Apply the core excerpt formatting used by the_excerpt().
						'excerpt'   => apply_filters( 'the_excerpt', get_the_excerpt() ),
					)
				);
			} else {
				get_template_part( 'template-parts/content' );
			}
		}
		?>
	</div>
	<?php the_posts_pagination(); ?>
<?php else : ?>
	<?php get_template_part( 'template-parts/content-none' ); ?>
<?php endif; ?>
