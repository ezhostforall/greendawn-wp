<?php
/**
 * Fixed native charger collection; content and ordered IDs come from the page adapter.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_collection_id = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-chargers-' ) );
$greendawn_collection    = greendawn_page_records( $args['post_ids'] ?? array(), 'evcharger' );
$greendawn_intro         = (string) ( $args['intro'] ?? '' );
$greendawn_outro         = (string) ( $args['outro'] ?? '' );
$greendawn_guidance      = array_filter( (array) ( $args['items'] ?? array() ), static fn( $item ) => ! empty( $item['title'] ) || ! empty( $item['copy'] ) );
if ( ! $greendawn_collection && ! $greendawn_guidance ) {
	return;
}
?>
<section class="gd-section gd-recommended-chargers" aria-labelledby="<?php echo esc_attr( $greendawn_collection_id . '-chargers-title' ); ?>">
	<div class="gd-shell">
		<h2 id="<?php echo esc_attr( $greendawn_collection_id . '-chargers-title' ); ?>"><?php echo esc_html( $args['heading'] ?? '' ); ?></h2>
		<?php if ( '' !== $greendawn_intro ) : ?>
			<p class="gd-chargers-intro"><?php echo esc_html( $greendawn_intro ); ?></p>
		<?php endif; ?>
		<?php if ( $greendawn_guidance ) : ?>
			<ul class="gd-charger-guidance">
				<?php foreach ( $greendawn_guidance as $greendawn_item ) : ?>
					<li><strong><?php echo esc_html( $greendawn_item['title'] ?? '' ); ?></strong> <?php echo esc_html( $greendawn_item['copy'] ?? '' ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php if ( '' !== $greendawn_outro ) : ?>
			<p class="gd-chargers-intro"><?php echo esc_html( $greendawn_outro ); ?></p>
		<?php endif; ?>
		<?php if ( $greendawn_collection ) : ?>
		<div class="gd-charger-grid">
			<?php foreach ( $greendawn_collection as $greendawn_charger ) : ?>
				<?php
				get_template_part(
					'template-parts/components/charger-card',
					null,
					array(
						'post_id'     => $greendawn_charger->ID,
						'instance_id' => $greendawn_collection_id . '-product-' . $greendawn_charger->ID,
					)
				);
				?>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
</section>
