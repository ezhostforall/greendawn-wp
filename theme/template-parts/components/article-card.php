<?php
/**
 * Article card shared by homepage curation and native post relationships.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_card_id       = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-article-' ) );
$greendawn_card_title_id = sanitize_html_class( $args['title_id'] ?? $greendawn_card_id . '-title' );
$greendawn_card_label_id = sanitize_html_class( $args['label_id'] ?? $greendawn_card_id . '-label' );
$greendawn_card_label    = ! empty( $args['show_label'] );
$greendawn_card_class    = sanitize_html_class( $args['variant'] ?? '' );
$greendawn_card_image    = wp_get_attachment_image(
	absint( $args['image_id'] ?? 0 ),
	'large',
	false,
	array(
		'loading' => 'lazy',
		'sizes'   => $args['sizes'] ?? '(max-width: 42rem) calc(100vw - 2rem), (max-width: 61rem) 45vw, 30vw',
	)
);
?>
<a class="gd-insight-card<?php echo $greendawn_card_class ? ' gd-insight-card--' . esc_attr( $greendawn_card_class ) : ''; ?>" href="<?php echo esc_url( $args['url'] ?? '' ); ?>" aria-labelledby="<?php echo esc_attr( ( $greendawn_card_label ? $greendawn_card_label_id . ' ' : '' ) . $greendawn_card_title_id ); ?>">
	<?php if ( $greendawn_card_image ) : ?>
		<figure>
			<?php echo $greendawn_card_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generates escaped attachment markup. ?>
			<span aria-hidden="true"><?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?></span>
		</figure>
	<?php endif; ?>
	<?php
	if ( $greendawn_card_label ) :
		?>
		<p id="<?php echo esc_attr( $greendawn_card_label_id ); ?>"><?php echo esc_html( $args['label'] ?? '' ); ?></p><?php endif; ?>
	<h3 id="<?php echo esc_attr( $greendawn_card_title_id ); ?>"><?php echo esc_html( $args['title'] ?? '' ); ?></h3>
</a>
