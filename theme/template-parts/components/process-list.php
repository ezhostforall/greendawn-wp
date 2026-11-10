<?php
/**
 * Vertical sales-process list alongside a form; receives explicit page-owned steps.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_steps = array_values( array_filter( $args['steps'] ?? array(), static fn( array $step ): bool => '' !== (string) ( $step['title'] ?? '' ) || '' !== (string) ( $step['copy'] ?? '' ) ) );
if ( ! $greendawn_steps ) {
	return;
}
?>
<ol class="gd-enquiry-process" aria-label="<?php esc_attr_e( 'Installation process', 'greendawn' ); ?>">
	<?php foreach ( $greendawn_steps as $greendawn_index => $greendawn_step ) : ?>
		<li>
			<span aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $greendawn_index + 1 ) ); ?></span>
			<div>
				<?php if ( '' !== (string) ( $greendawn_step['title'] ?? '' ) ) : ?>
					<h2><?php echo esc_html( $greendawn_step['title'] ); ?></h2>
				<?php endif; ?>
				<?php echo wpautop( esc_html( (string) ( $greendawn_step['copy'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain text escaped before paragraph formatting. ?>
			</div>
		</li>
	<?php endforeach; ?>
</ol>
