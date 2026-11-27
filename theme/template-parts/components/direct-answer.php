<?php
/**
 * A concise opening answer, without a second content/editor system.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_answer = greendawn_component_text( $args['answer'] ?? '' );
if ( '' === $greendawn_answer ) {
	return;
}
$greendawn_answer_id = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-answer-' ) );
?>
<aside class="gd-direct-answer" aria-labelledby="<?php echo esc_attr( $greendawn_answer_id ); ?>">
	<p class="gd-eyebrow" id="<?php echo esc_attr( $greendawn_answer_id ); ?>"><?php echo esc_html( $args['label'] ?? __( 'The short answer', 'greendawn' ) ); ?></p>
	<div><?php echo greendawn_page_prose( $greendawn_answer ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Restricted prose allowlist. ?></div>
</aside>
