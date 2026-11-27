<?php
/**
 * Shared PHP component contracts on the disposable WordPress only.
 *
 * @package Greendawn
 */

use Greendawn_Core\Modules\Guide_Content;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'local' !== wp_get_environment_type() || 'http://localhost:8080' !== home_url() ) { return; }

/** Assert without bypassing exception handling. */
function greendawn_reading_check( bool $condition, string $message ): void {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
}
/** Capture a real theme partial. */
function greendawn_reading_render( string $part, array $args ): string {
	ob_start();
	get_template_part( 'template-parts/components/' . $part, null, $args );
	return ob_get_clean();
}

$greendawn_mode = $args[0] ?? 'seed';
$greendawn_fixture = get_option( 'greendawn_reading_fixture', array() );
$greendawn_error = '';
wp_set_current_user( get_user_by( 'login', 'admin' )->ID );
try {
	set_error_handler( static function ( $severity, $message, $file, $line ) {
		if ( error_reporting() & $severity ) { throw new ErrorException( $message, 0, $severity, $file, $line ); }
		return false;
	} );
	if ( 'seed' === $greendawn_mode ) {
		$greendawn_image_page = get_page_by_path( 'page-with-featured-image' );
		$greendawn_fixture['image'] = $greendawn_image_page ? get_post_thumbnail_id( $greendawn_image_page ) : ( $greendawn_fixture['image'] ?? 0 );
		if ( ! $greendawn_fixture['image'] ) {
			$upload = wp_upload_bits( 'reading-fixture.png', null, base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j7S8AAAAASUVORK5CYII=' ) );
			$greendawn_fixture['image'] = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => 'Reading fixture image', 'post_status' => 'inherit' ), $upload['file'] );
		}
		$greendawn_term = wp_insert_term( 'Component site power', 'gdguide_topic' );
		$greendawn_fixture['term'] = is_wp_error( $greendawn_term ) ? get_term_by( 'name', 'Component site power', 'gdguide_topic' )->term_id : $greendawn_term['term_id'];
		foreach ( array( 'guide' => 'Component guide', 'guide_two' => 'Component planning guide', 'guide_three' => 'Component funding guide', 'draft' => 'Hidden component draft', 'protected' => 'Hidden component password' ) as $key => $title ) {
			$id = ! empty( $greendawn_fixture[ $key ] ) && get_post( $greendawn_fixture[ $key ] ) ? $greendawn_fixture[ $key ] : wp_insert_post( array( 'post_type' => 'gdguide', 'post_status' => 'draft', 'post_title' => $title ) );
			wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) );
			$greendawn_fixture[ $key ] = $id;
			wp_set_object_terms( $id, array( $greendawn_fixture['term'] ), 'gdguide_topic' );
			$result = Guide_Content::write( $id, array(
				'intro' => 'Practical site capacity guidance from the structured record.', 'answer' => 'Start with the building load and the charging demand.', 'primary_topic' => $greendawn_fixture['term'], 'image' => $greendawn_fixture['image'], 'image_position' => 'upper', 'reviewed_on' => '20240101',
				'sections' => array(
					array( 'heading' => 'Supply assessment', 'body' => 'Check the supply and operating demand before choosing equipment.', 'list_style' => 'checklist', 'list_items' => array( array( 'text' => 'Understand the existing load.' ), array( 'text' => 'Record the expected charging demand.' ) ), 'table_caption' => 'Site planning comparison', 'columns' => array( array( 'label' => 'Option' ), array( 'label' => 'Supply' ), array( 'label' => 'Operation' ), array( 'label' => 'Next step' ) ), 'table_rows' => array( array( 'cells' => array( array( 'text' => 'Managed charging' ), array( 'text' => 'Existing supply' ), array( 'text' => 'Coordinate available power' ), array( 'text' => 'Assess the site' ) ) ) ), 'image' => $greendawn_fixture['image'], 'caption' => 'Company image used for component testing.' ),
					array( 'heading' => 'Supply assessment', 'nav_label' => 'The next stage', 'body' => 'Prepare the information needed for an informed discussion.' ),
					array( 'heading' => 'Supply assessment title', 'body' => 'A heading that must not collide with the first heading ID.' ),
				),
				'faqs' => array( array( 'question' => 'Can I plan in phases?', 'answer' => 'The site assessment can consider a phased approach.' ) ),
				'sources' => array( array( 'title' => 'Official guidance', 'url' => 'https://www.gov.uk/', 'publisher' => 'GOV.UK', 'checked_on' => '20240101' ) ),
			) );
			greendawn_reading_check( true === $result, 'Fixture content could not save.' );
			if ( 'draft' !== $key ) { wp_update_post( array( 'ID' => $id, 'post_status' => 'publish', 'post_password' => 'protected' === $key ? 'fixture-password' : '' ) ); }
		}
		$greendawn_fixture['post'] = ! empty( $greendawn_fixture['post'] ) && get_post( $greendawn_fixture['post'] ) ? $greendawn_fixture['post'] : wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Component legacy article', 'post_date' => '2010-01-01 12:00:00' ) );
		foreach ( array( 'reading-components', 'reading-components-empty', 'reading-components-legacy' ) as $slug ) {
			if ( ! get_page_by_path( $slug ) ) { wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => 'Shared component review' ) ); }
		}
		update_option( 'greendawn_reading_fixture', $greendawn_fixture, false );
	}
	$sections = greendawn_reading_sections( array( array( 'heading' => 'Power', 'body' => 'One.' ), array( 'heading' => 'Power', 'body' => 'Two.' ), array( 'heading' => 'Power title', 'body' => 'Three.' ) ) );
	$ids = array();
	foreach ( $sections as $section ) { array_push( $ids, $section['id'], $section['id'] . '-title', $section['id'] . '-table-caption' ); }
	greendawn_reading_check( count( $ids ) === count( array_unique( $ids ) ) && $sections === greendawn_reading_sections( $sections ), 'Section anchors collide or change when reused.' );
	foreach ( array( 'direct-answer', 'reading-contents', 'reading-sections', 'source-list', 'coverage-list', 'editorial-meta' ) as $part ) {
		greendawn_reading_check( '' === trim( greendawn_reading_render( $part, array() ) ), 'An empty component left a visible shell: ' . $part );
	}
	$html = greendawn_reading_render( 'direct-answer', array( 'answer' => '<script>unsafe()</script><strong>Safe emphasis</strong>' ) );
	greendawn_reading_check( ! str_contains( $html, '<script>' ) && str_contains( $html, '<strong>Safe emphasis</strong>' ), 'Prose allowlisting failed.' );
	greendawn_reading_check( '' === greendawn_component_icon( '" onload="unsafe' ) && array() === greendawn_editorial_date( '20990101' ), 'Unknown icon/date inputs were accepted.' );
	$html = greendawn_reading_render( 'source-list', array( 'items' => array( array( 'title' => 'Bad link', 'url' => 'javascript:unsafe()' ) ) ) );
	greendawn_reading_check( '' === trim( $html ), 'Unsafe evidence URLs were rendered.' );
	greendawn_reading_check( '' === trim( greendawn_reading_render( 'article-card', array( 'variant' => 'guide-featured' ) ) ), 'An unavailable featured Guide left an empty link.' );
	$bad = greendawn_reading_sections( array( array( 'heading' => 'Bad table', 'table_caption' => 'Mismatch', 'columns' => array( array( 'label' => 'One' ), array( 'label' => 'Two' ) ), 'table_rows' => array( array( 'cells' => array( array( 'text' => 'Only one cell' ) ) ) ) ) ) );
	greendawn_reading_check( ! str_contains( greendawn_reading_render( 'reading-sections', array( 'sections' => $bad ) ), '<table' ), 'Malformed table cells produced misleading columns.' );
	$plain = greendawn_reading_render( 'cta-band', array( 'heading' => 'Legacy action', 'label' => 'Contact', 'url' => home_url( '/contact/' ) ) );
	$drawer = greendawn_reading_render( 'cta-band', array( 'heading' => 'New action', 'label' => 'Discuss', 'action' => 'enquiry', 'intent' => 'ev-charging', 'entry_point' => 'final-cta', 'url' => home_url( '/contact/' ) ) );
	greendawn_reading_check( ! str_contains( $plain, 'data-lead-capture-open' ) && str_contains( $drawer, 'data-lead-capture-open' ) && str_contains( $drawer, 'data-lead-entry-point="final-cta"' ), 'CTA default or opt-in drawer contract changed.' );
	greendawn_reading_check( ! str_contains( greendawn_reading_render( 'page-hero', array( 'heading' => 'Legacy hero' ) ), 'gd-editorial-meta' ), 'Default heroes acquired Guide metadata.' );
	greendawn_reading_check( str_contains( greendawn_reading_render( 'feature-grid', array( 'heading' => 'Default', 'items' => array( array( 'title' => 'One' ) ) ) ), 'gd-feature-number' ), 'Default feature numbering disappeared.' );
	greendawn_reading_check( array() === greendawn_article_card_data( get_post( $greendawn_fixture['draft'] ) ) && array() === greendawn_article_card_data( get_post( $greendawn_fixture['protected'] ) ), 'Non-public Guide cards leaked.' );
	$card = greendawn_article_card_data( get_post( $greendawn_fixture['guide'] ) );
	greendawn_reading_check( 'without-core' === $greendawn_mode ? ! $card : 'guide' === ( $card['variant'] ?? '' ) && 'Practical site capacity guidance from the structured record.' === $card['summary'], 'Guide adapter requires the wrong dependency or duplicates card content.' );
	greendawn_reading_check( 'Component legacy article' === greendawn_article_card_data( get_post( $greendawn_fixture['post'] ) )['title'], 'Native Post cards broke.' );
} catch ( Throwable $error ) {
	$greendawn_error = $error->getMessage();
} finally {
	restore_error_handler();
}
if ( $greendawn_error ) { WP_CLI::error( $greendawn_error ); }
WP_CLI::success( 'Reading component contracts verified: ' . $greendawn_mode );
