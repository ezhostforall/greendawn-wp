<?php
/**
 * Evidence links and genuine checked dates, with no automatic freshness claims.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_sources = array();
foreach ( is_array( $args['items'] ?? null ) ? $args['items'] : array() as $greendawn_item ) {
	if ( ! is_array( $greendawn_item ) ) {
		continue; }
	$greendawn_url   = esc_url( greendawn_component_text( $greendawn_item['url'] ?? '' ), array( 'http', 'https' ) );
	$greendawn_title = greendawn_component_text( $greendawn_item['title'] ?? '' );
	if ( $greendawn_url && $greendawn_title ) {
		$greendawn_sources[] = array(
			'url'       => $greendawn_url,
			'title'     => $greendawn_title,
			'publisher' => greendawn_component_text( $greendawn_item['publisher'] ?? '' ),
			'date'      => greendawn_editorial_date( $greendawn_item['checked_on'] ?? '' ),
		);
	}
}
if ( ! $greendawn_sources ) {
	return; }
$greendawn_id = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-sources-' ) );
?>
<section class="gd-source-section" aria-labelledby="<?php echo esc_attr( $greendawn_id ); ?>">
	<h2 id="<?php echo esc_attr( $greendawn_id ); ?>"><?php echo esc_html( $args['heading'] ?? __( 'Sources and further reading', 'greendawn' ) ); ?></h2>
	<ul class="gd-source-list">
		<?php foreach ( $greendawn_sources as $greendawn_source ) : ?>
			<li><a href="<?php echo esc_url( $greendawn_source['url'] ); ?>"><?php echo esc_html( $greendawn_source['title'] ); ?></a>
				<?php if ( $greendawn_source['publisher'] || $greendawn_source['date'] ) : ?>
					<p><?php echo esc_html( $greendawn_source['publisher'] ); ?>
						<?php if ( $greendawn_source['date'] ) : ?>
							<?php echo $greendawn_source['publisher'] ? ' · ' : ''; ?><?php esc_html_e( 'Checked', 'greendawn' ); ?> <time datetime="<?php echo esc_attr( $greendawn_source['date']['iso'] ); ?>"><?php echo esc_html( $greendawn_source['date']['label'] ); ?></time>
						<?php endif; ?>
					</p>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
