<?php
/**
 * Service card for grids on business pages.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'card layout-stack service-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<?php
		// Keep the source ratio for srcset. CSS crops the card.
		the_post_thumbnail(
			'medium_large',
			array( 'sizes' => '(min-width: 72rem) 33rem, (min-width: 42rem) calc(50vw - 2.5rem), calc(100vw - 4rem)' )
		);
		?>
	<?php endif; ?>
	<?php if ( ! empty( $args['nested'] ) ) : ?>
		<h3 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
	<?php else : ?>
		<h2 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	<?php endif; ?>
	<?php if ( has_excerpt() ) : ?>
		<div class="entry-summary"><?php the_excerpt(); ?></div>
	<?php endif; ?>
</article>
