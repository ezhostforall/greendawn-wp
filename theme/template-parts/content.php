<?php
/**
 * Post summary card used in loops.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'card layout-stack post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php
			the_post_thumbnail(
				'medium_large',
				array( 'sizes' => '(min-width: 72rem) 24rem, (min-width: 64rem) 33vw, (min-width: 42rem) 50vw, calc(100vw - 4rem)' )
			);
			?>
		</a>
	<?php endif; ?>
	<h2 class="card__title type-subheading"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	<p class="entry-meta"><?php greendawn_posted_on(); ?></p>
	<div class="entry-summary"><?php the_excerpt(); ?></div>
</article>
