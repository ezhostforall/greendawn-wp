<?php
/**
 * Named places served; this list makes no office or proximity claims.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_places = array();
foreach ( is_array( $args['items'] ?? null ) ? $args['items'] : array() as $greendawn_place ) {
	$greendawn_name = greendawn_component_text( is_array( $greendawn_place ) ? $greendawn_place['name'] ?? '' : $greendawn_place );
	if ( '' !== $greendawn_name ) {
		$greendawn_places[] = $greendawn_name; }
}
if ( ! $greendawn_places ) {
	return; }
?>
<ul class="gd-coverage-list" aria-label="<?php echo esc_attr( $args['label'] ?? __( 'Places served', 'greendawn' ) ); ?>">
	<?php foreach ( array_unique( $greendawn_places ) as $greendawn_place ) : ?>
		<li><?php echo greendawn_component_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed decorative SVG. ?><span><?php echo esc_html( $greendawn_place ); ?></span></li>
	<?php endforeach; ?>
</ul>
