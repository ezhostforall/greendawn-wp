<?php
/**
 * Always-visible service summaries beside a sales form.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_services = array_filter( (array) ( $args['items'] ?? array() ), static fn( array $item ): bool => '' !== (string) ( $item['title'] ?? '' ) || '' !== (string) ( $item['copy'] ?? '' ) );
if ( ! $greendawn_services ) {
	return;
}
?>
<div class="gd-service-list">
	<?php foreach ( $greendawn_services as $greendawn_service ) : ?>
		<article>
			<?php if ( '' !== (string) ( $greendawn_service['title'] ?? '' ) ) : ?>
				<h3><?php echo esc_html( $greendawn_service['title'] ); ?></h3>
			<?php endif; ?>
			<?php echo wpautop( esc_html( (string) ( $greendawn_service['copy'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain text escaped before paragraph formatting. ?>
		</article>
	<?php endforeach; ?>
</div>
