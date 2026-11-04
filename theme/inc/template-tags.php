<?php
/**
 * Template helpers.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prints the post date as a <time> element.
 */
function greendawn_posted_on(): void {
	printf(
		'<time class="entry-date" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);
}

/**
 * Outputs breadcrumbs. Uses Rank Math when active, otherwise a minimal fallback.
 * Rank Math owns breadcrumb structured data; the fallback emits none.
 */
function greendawn_breadcrumbs(): void {
	if ( is_front_page() ) {
		return;
	}

	if ( greendawn_is_rank_math_active() && function_exists( 'rank_math_the_breadcrumbs' ) ) {
		rank_math_the_breadcrumbs();
		return;
	}

	$items = array( array( __( 'Home', 'greendawn' ), home_url( '/' ) ) );
	if ( is_singular() && is_post_type_hierarchical( get_post_type() ) ) {
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $ancestor ) {
			$items[] = array( get_the_title( $ancestor ), get_permalink( $ancestor ) );
		}
	}
	if ( is_singular( 'service' ) ) {
		$overview = get_page_by_path( 'services' );
		if ( $overview && 'publish' === $overview->post_status && 'templates/services.php' === get_page_template_slug( $overview ) ) {
			$items[] = array( get_the_title( $overview ), get_permalink( $overview ) );
		}
	} elseif ( is_singular( 'post' ) && 'page' === get_option( 'show_on_front' ) ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page && 'publish' === get_post_status( $posts_page ) ) {
			$items[] = array( get_the_title( $posts_page ), get_permalink( $posts_page ) );
		}
	}
	if ( is_singular() || ( is_home() && get_queried_object_id() ) ) {
		$items[] = array( get_the_title( get_queried_object_id() ), '' );
	} else {
		$items[] = array( wp_strip_all_tags( wp_get_document_title() ), '' );
	}
	?>
	<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'greendawn' ); ?>">
		<ol>
			<?php foreach ( $items as $item ) : ?>
				<li>
					<?php if ( $item[1] ) : ?>
						<a href="<?php echo esc_url( $item[1] ); ?>"><?php echo esc_html( $item[0] ); ?></a>
					<?php else : ?>
						<span aria-current="page"><?php echo esc_html( $item[0] ); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>
	<?php
}
