<?php
/**
 * Decorative arrow for homepage links.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_diagonal = ! empty( $args['diagonal'] );
?>
<svg class="gd-home-arrow" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
	<path d="<?php echo esc_attr( $greendawn_diagonal ? 'M6 18 18 6M8 6h10v10' : 'M4 12h15m-6-6 6 6-6 6' ); ?>" />
</svg>
