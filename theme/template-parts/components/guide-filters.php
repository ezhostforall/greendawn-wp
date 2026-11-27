<?php
/**
 * Native GET discovery controls; querying and result counts belong to the caller.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_action        = esc_url( $args['url'] ?? home_url( '/guides/' ) );
$greendawn_query         = greendawn_component_text( $args['query'] ?? '' );
$greendawn_encoded_query = rawurlencode( $greendawn_query );
$greendawn_active        = sanitize_title( greendawn_component_text( $args['topic'] ?? '' ) );
$greendawn_id            = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-search-' ) );
$greendawn_topics        = is_array( $args['topics'] ?? null ) ? $args['topics'] : array();
?>
<div class="gd-guide-filters" data-gd-guide-filters>
	<form class="gd-guide-search" role="search" aria-label="<?php esc_attr_e( 'Guides', 'greendawn' ); ?>" action="<?php echo esc_url( $greendawn_action ); ?>" method="get">
		<label for="<?php echo esc_attr( $greendawn_id ); ?>"><?php echo esc_html( $args['label'] ?? __( 'Search guides', 'greendawn' ) ); ?></label>
		<div><input id="<?php echo esc_attr( $greendawn_id ); ?>" type="search" name="guide_search" value="<?php echo esc_attr( $greendawn_query ); ?>" maxlength="200"><button class="gd-home-button gd-page-button--navy" type="submit"><?php esc_html_e( 'Search', 'greendawn' ); ?></button></div>
		<?php
		if ( $greendawn_active ) :
			?>
			<input type="hidden" name="guide_topic" value="<?php echo esc_attr( $greendawn_active ); ?>"><?php endif; ?>
	</form>
	<nav class="gd-guide-topics" aria-label="<?php esc_attr_e( 'Guide topics', 'greendawn' ); ?>">
		<a href="<?php echo esc_url( $greendawn_query ? add_query_arg( 'guide_search', $greendawn_encoded_query, $greendawn_action ) : $greendawn_action ); ?>" <?php echo '' === $greendawn_active ? 'aria-current="true"' : ''; ?>><?php echo esc_html( $args['all_label'] ?? __( 'All topics', 'greendawn' ) ); ?></a>
		<?php foreach ( $greendawn_topics as $greendawn_topic ) : ?>
			<?php
			if ( ! is_array( $greendawn_topic ) || empty( $greendawn_topic['slug'] ) || empty( $greendawn_topic['name'] ) ) {
				continue; }
			$greendawn_slug = sanitize_title( $greendawn_topic['slug'] );
			$greendawn_url  = add_query_arg(
				array(
					'guide_topic'  => $greendawn_slug,
					'guide_search' => $greendawn_query ? $greendawn_encoded_query : false,
				),
				$greendawn_action
			);
			?>
			<a href="<?php echo esc_url( $greendawn_url ); ?>" <?php echo $greendawn_slug === $greendawn_active ? 'aria-current="true"' : ''; ?>><?php echo esc_html( $greendawn_topic['name'] ); ?>
			<?php
			if ( isset( $greendawn_topic['count'] ) && is_scalar( $greendawn_topic['count'] ) ) :
				?>
				<span>(<?php echo esc_html( number_format_i18n( absint( $greendawn_topic['count'] ) ) ); ?>)</span><?php endif; ?></a>
		<?php endforeach; ?>
	</nav>
	<?php
	if ( '' !== $greendawn_query || $greendawn_active ) :
		?>
		<a class="gd-page-text-link" href="<?php echo esc_url( $greendawn_action ); ?>"><?php echo esc_html( $args['reset_label'] ?? __( 'Clear filters', 'greendawn' ) ); ?></a><?php endif; ?>
</div>
