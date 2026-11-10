<?php
/**
 * Always-visible benefit copy; explicit content, fixed responsive layout.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_feature_id = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-features-' ) );
$greendawn_features   = array_filter( (array) ( $args['items'] ?? array() ), static fn( $item ) => ! empty( $item['title'] ) );
if ( ! $greendawn_features ) {
	return;
}
?>
<section class="gd-section gd-benefits" aria-labelledby="<?php echo esc_attr( $greendawn_feature_id . '-title' ); ?>">
	<div class="gd-shell">
		<h2 id="<?php echo esc_attr( $greendawn_feature_id . '-title' ); ?>"><?php echo esc_html( $args['heading'] ?? '' ); ?></h2>
		<div class="gd-feature-grid">
			<?php foreach ( array_values( $greendawn_features ) as $greendawn_index => $greendawn_feature ) : ?>
				<article>
					<span class="gd-feature-number" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $greendawn_index + 1 ) ); ?></span>
					<h3><?php echo esc_html( $greendawn_feature['title'] ); ?></h3>
					<?php
					if ( ! empty( $greendawn_feature['copy'] ) ) :
						?>
						<p><?php echo esc_html( $greendawn_feature['copy'] ); ?></p><?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
