<?php
/**
 * Native product card; source Request Quote destinations remain product details.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_card_post = get_post( absint( $args['post_id'] ?? 0 ) );
if ( ! $greendawn_card_post || 'evcharger' !== $greendawn_card_post->post_type || 'publish' !== $greendawn_card_post->post_status || '' !== $greendawn_card_post->post_password ) {
	return;
}
$greendawn_card_id    = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-product-' ) );
$greendawn_card_title = get_the_title( $greendawn_card_post );
$greendawn_card_url   = get_permalink( $greendawn_card_post );
$greendawn_card_media = wp_get_attachment_image(
	get_post_thumbnail_id( $greendawn_card_post ),
	'medium_large',
	false,
	array(
		'alt'     => $greendawn_card_title,
		'loading' => 'lazy',
		'sizes'   => '(max-width: 42rem) calc(100vw - 2rem), (max-width: 61rem) 45vw, 30vw',
	)
);
?>
<article class="gd-charger-card">
	<a class="gd-charger-card__product" href="<?php echo esc_url( $greendawn_card_url ); ?>" aria-labelledby="<?php echo esc_attr( $greendawn_card_id . '-title' ); ?>">
		<?php
		if ( $greendawn_card_media ) :
			?>
			<figure><?php echo $greendawn_card_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native escaped attachment markup. ?></figure><?php endif; ?>
		<h3 id="<?php echo esc_attr( $greendawn_card_id . '-title' ); ?>"><?php echo esc_html( $greendawn_card_title ); ?></h3>
	</a>
	<a class="gd-page-text-link" href="<?php echo esc_url( $greendawn_card_url ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: charger title. */ __( 'Request Quote — %s', 'greendawn' ), $greendawn_card_title ) ); ?>"><?php esc_html_e( 'Request Quote', 'greendawn' ); ?><?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?></a>
</article>
