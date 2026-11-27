<?php
/**
 * Explicit editorial identity, genuine review date and computed reading estimate.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_author  = greendawn_component_text( $args['author'] ?? '' );
$greendawn_date    = greendawn_editorial_date( $args['reviewed_on'] ?? '' );
$greendawn_minutes = is_scalar( $args['reading_minutes'] ?? null ) ? absint( $args['reading_minutes'] ) : 0;
if ( '' === $greendawn_author && ! $greendawn_date && ! $greendawn_minutes ) {
	return;
}
?>
<div class="gd-editorial-meta">
	<?php if ( '' !== $greendawn_author ) : ?>
		<span class="gd-editorial-meta__author"><?php echo esc_html( $greendawn_author ); ?></span>
	<?php endif; ?>
	<?php if ( $greendawn_date ) : ?>
		<span><?php esc_html_e( 'Reviewed', 'greendawn' ); ?> <time datetime="<?php echo esc_attr( $greendawn_date['iso'] ); ?>"><?php echo esc_html( $greendawn_date['label'] ); ?></time></span>
	<?php endif; ?>
	<?php if ( $greendawn_minutes ) : ?>
		<span><?php echo esc_html( sprintf( /* translators: %s: estimated minutes. */ _n( '%s minute read', '%s minutes read', $greendawn_minutes, 'greendawn' ), number_format_i18n( $greendawn_minutes ) ) ); ?></span>
	<?php endif; ?>
</div>
