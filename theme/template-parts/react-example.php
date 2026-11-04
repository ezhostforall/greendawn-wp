<?php
/**
 * Mount point for the optional React example.
 *
 * Args: label (string), start (int). Server-provided config is JSON-encoded into a data
 * attribute and validated again in TypeScript. The inner paragraph is the no-JS fallback.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_config = array(
	'label' => isset( $args['label'] ) ? (string) $args['label'] : __( 'Count', 'greendawn' ),
	'start' => isset( $args['start'] ) ? (int) $args['start'] : 0,
);

greendawn_enqueue_entry( 'react-example' );
?>
<div data-react-root="example" data-config="<?php echo esc_attr( (string) wp_json_encode( $greendawn_config ) ); ?>">
	<p><?php esc_html_e( 'This interactive example requires JavaScript.', 'greendawn' ); ?></p>
</div>
