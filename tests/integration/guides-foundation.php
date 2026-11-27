<?php
/**
 * Real WordPress/SCF content lifecycle checks, restricted to disposable CI only.
 *
 * @package Greendawn
 */

use Greendawn_Core\Modules\Guides;
use Greendawn_Core\Modules\Guide_Content;
use Greendawn_Core\Modules\Guide_Fields;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'local' !== wp_get_environment_type() || 'http://localhost:8080' !== home_url() ) {
	return;
}

/**
 * Throw without bypassing fixture cleanup.
 *
 * @param bool   $condition Expected outcome.
 * @param string $message Failure message.
 * @throws RuntimeException When an expectation fails.
 */
function greendawn_guides_check( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( esc_html( $message ) );
	}
}

/**
 * Serialize real form input using SCF field keys, including nested repeaters.
 *
 * @param mixed $value Fixture value.
 * @param array $field Field definition.
 * @return mixed
 */
function greendawn_guides_form_value( $value, array $field ) {
	if ( 'repeater' !== $field['type'] ) {
		return $value;
	}
	$rows = array();
	foreach ( $value as $row ) {
		$input = array();
		foreach ( $field['sub_fields'] as $child ) {
			if ( array_key_exists( $child['name'], $row ) ) {
				$input[ $child['key'] ] = greendawn_guides_form_value( $row[ $child['name'] ], $child );
			}
		}
		$rows[] = $input;
	}
	return $rows;
}

$greendawn_phase   = $args[0] ?? 'scf';
$greendawn_records = get_option( 'greendawn_guides_fixture_records', array() );
$greendawn_error   = '';
$greendawn_user    = get_user_by( 'login', 'admin' );
wp_set_current_user( $greendawn_user->ID );

try {
	if ( 'scf' === $greendawn_phase ) {
		greendawn_guides_check( function_exists( 'update_field' ), 'This phase requires SCF.' );
		$greendawn_type = get_post_type_object( Guides::TYPE );
		greendawn_guides_check( $greendawn_type && ! $greendawn_type->has_archive && ! $greendawn_type->show_in_rest && post_type_supports( Guides::TYPE, 'revisions' ) && ! post_type_supports( Guides::TYPE, 'editor' ), 'Guide registration or editor ownership is wrong.' );
		greendawn_guides_check( ! get_taxonomy( Guides::TAXONOMY )->publicly_queryable, 'Topics must not expose empty archives.' );
		$greendawn_records['hub'] = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Guides fixture', 'post_name' => 'guides' ) );
		$greendawn_records['region'] = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'Region fixture' ) );
		update_post_meta( $greendawn_records['hub'], '_wp_page_template', Guides::TEMPLATES['hub'] );
		update_post_meta( $greendawn_records['region'], '_wp_page_template', Guides::TEMPLATES['region'] );
		$greendawn_records['guide'] = wp_insert_post( array( 'post_type' => Guides::TYPE, 'post_status' => 'draft', 'post_title' => 'Site capacity fixture', 'post_name' => 'capacity-fixture' ) );
		$greendawn_records['other'] = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Unrelated blog fixture', 'post_content' => 'Keep this body.' ) );
		$greendawn_records['private'] = wp_insert_post( array( 'post_type' => Guides::TYPE, 'post_status' => 'draft', 'post_title' => 'Unpublished Guide' ) );
		$greendawn_id = $greendawn_records['guide'];
		$greendawn_term = wp_insert_term( 'Site power fixture', Guides::TAXONOMY );
		$greendawn_records['term'] = $greendawn_term['term_id'];
		wp_set_object_terms( $greendawn_id, array( $greendawn_records['term'] ), Guides::TAXONOMY );
		$greendawn_upload = wp_upload_bits( 'guide-fixture.png', null, base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j7S8AAAAASUVORK5CYII=' ) );
		$greendawn_records['image'] = wp_insert_attachment( array( 'post_title' => 'Guide fixture image', 'post_mime_type' => 'image/png', 'post_status' => 'inherit' ), $greendawn_upload['file'] );
		$greendawn_values = array(
			'intro' => "A company's capacity question.",
			'answer' => 'Assess the building before selecting equipment.',
			'primary_topic' => $greendawn_records['term'],
			'image' => $greendawn_records['image'],
			'reviewed_on' => '20240101',
			'sections' => array(
				array( 'heading' => 'Supply assessment', 'body' => "Keep the engineer's C:\\site\\notes as plain text.", 'list_style' => 'checklist', 'list_items' => array( array( 'text' => 'Check the unique switchgear marker.' ) ), 'table_caption' => 'Capacity options', 'columns' => array( array( 'label' => 'Option' ), array( 'label' => 'Consideration' ) ), 'table_rows' => array( array( 'cells' => array( array( 'text' => 'Load management' ), array( 'text' => 'Unique nested cell marker' ) ) ) ), 'image' => $greendawn_records['image'], 'caption' => 'Company photography.' ),
			),
			'faqs' => array( array( 'question' => 'What about connectivity?', 'answer' => 'A unique telemetry marker.' ) ),
			'sources' => array( array( 'title' => 'Official guidance', 'publisher' => 'Published source', 'url' => 'https://www.gov.uk/', 'checked_on' => '20240101' ) ),
		);
		greendawn_guides_check( true === Guide_Content::write( $greendawn_id, $greendawn_values ), 'Valid structured content did not save.' );
		$greendawn_read = Guide_Content::read( $greendawn_id );
		greendawn_guides_check( $greendawn_values['intro'] === $greendawn_read['intro'] && $greendawn_values['sections'][0]['body'] === $greendawn_read['sections'][0]['body'], 'Quotes or backslashes changed on save.' );
		greendawn_guides_check( 'Unique nested cell marker' === $greendawn_read['sections'][0]['table_rows'][0]['cells'][1]['text'], 'Nested SCF table cells did not round-trip.' );
		greendawn_guides_check( 'repeater' === get_field_object( 'greendawn_guide_sections', $greendawn_id )['type'] && 1 === count( get_field( 'greendawn_guide_sections', $greendawn_id ) ), 'SCF cannot edit the native content.' );
		greendawn_guides_check( str_contains( get_post_meta( $greendawn_id, Guide_Content::SEARCH_META, true ), 'unique switchgear marker' ) && str_contains( get_post_meta( $greendawn_id, Guide_Content::SEARCH_META, true ), 'Unique nested cell marker' ), 'SCF-only copy was omitted from search projection.' );
		greendawn_guides_check( 1 === (int) get_post_meta( $greendawn_id, Guide_Content::MINUTES_META, true ) && '' === get_post_field( 'post_content', $greendawn_id ), 'Reading time must come from SCF without duplicating native body content.' );
		greendawn_guides_check( array() === Guide_Content::public_data( $greendawn_id ), 'A draft Guide was exposed.' );
		$greendawn_records['legacy'] = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $greendawn_records['hub'], 'post_name' => 'legacy-guide', 'post_title' => 'Existing guide Page' ) );
		$greendawn_query = apply_filters( 'request', array( Guides::TYPE => 'legacy-guide' ) );
		greendawn_guides_check( 'guides/legacy-guide' === ( $greendawn_query['pagename'] ?? '' ) && ! isset( $greendawn_query[ Guides::TYPE ] ), 'New CPT rules shadow an existing Page route.' );
		wp_update_post( array( 'ID' => $greendawn_id, 'post_status' => 'publish' ) );
		greendawn_guides_check( 'publish' === get_post_status( $greendawn_id ) && Guide_Content::AUTHOR === Guide_Content::public_data( $greendawn_id )['author'], 'Valid publication or corporate author failed.' );
		$greendawn_records['collision'] = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $greendawn_records['hub'], 'post_name' => 'capacity-fixture', 'post_title' => 'Conflicting Page' ) );
		greendawn_guides_check( 'draft' === get_post_status( $greendawn_records['collision'] ), 'A new Page took over a published Guide URL.' );
		$greendawn_permalink = get_option( 'permalink_structure' );
		update_option( 'permalink_structure', '/news/%postname%/' );
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules( false );
		greendawn_guides_check( home_url( '/guides/capacity-fixture/' ) === get_permalink( $greendawn_id ) && $greendawn_records['hub'] === url_to_postid( home_url( '/guides/' ) ), 'Guide singles or hub routing changed under a blog prefix: ' . get_permalink( $greendawn_id ) . ', hub ' . $greendawn_records['hub'] . '/' . url_to_postid( home_url( '/guides/' ) ) );
		update_option( 'permalink_structure', $greendawn_permalink );
		$GLOBALS['wp_rewrite']->init();
		$greendawn_form_sections = greendawn_guides_form_value( $greendawn_values['sections'], Guide_Fields::find( 'field_greendawn_guide_sections' ) );
		$_POST = array( '_acf_nonce' => wp_create_nonce( 'post' ), '_acf_post_id' => $greendawn_id, 'post_ID' => $greendawn_id, 'post_type' => Guides::TYPE, 'post_status' => 'publish', 'acf' => wp_slash( array( 'field_greendawn_guide_intro' => "An editor's saved form change.", 'field_greendawn_guide_sections' => $greendawn_form_sections ) ) );
		wp_update_post( array( 'ID' => $greendawn_id ) );
		$_POST = array();
		greendawn_guides_check( "An editor's saved form change." === Guide_Content::read( $greendawn_id )['intro'] && 'publish' === get_post_status( $greendawn_id ), 'A verified SCF post form could not save pending values.' );
		Guide_Content::write( $greendawn_id, array( 'intro' => $greendawn_values['intro'] ) );

		$greendawn_revisions = wp_get_post_revisions( $greendawn_id );
		$greendawn_revision = reset( $greendawn_revisions )->ID;
		$greendawn_original_revision = get_metadata( 'post', $greendawn_revision, Guides::SNAPSHOT, true );
		greendawn_guides_check( ! isset( $greendawn_original_revision['meta'][ Guide_Content::SEARCH_META ] ), 'Derived metadata leaked into editable revision snapshots.' );
		greendawn_guides_check( is_wp_error( Guide_Content::write( $greendawn_id, array( 'reviewed_on' => '20990101' ) ) ) && '20240101' === Guide_Content::read( $greendawn_id )['reviewed_on'], 'Future review dates must fail without changing saved content.' );
		greendawn_guides_check( is_wp_error( Guide_Content::write( $greendawn_id, array( 'sources' => array( array( 'title' => 'Bad link', 'url' => 'javascript:alert(1)' ) ) ) ) ), 'Non-HTTP source links were accepted.' );
		greendawn_guides_check( is_wp_error( Guide_Content::write( $greendawn_id, array( 'related' => array( $greendawn_records['private'] ) ) ) ), 'Unpublished Guide relationships were accepted.' );
		greendawn_guides_check( is_wp_error( Guide_Content::write( $greendawn_id, array( 'image' => $greendawn_records['other'] ) ) ), 'Non-image records were accepted as images.' );
		$greendawn_bad_sections = $greendawn_values['sections'];
		$greendawn_bad_sections[0]['table_rows'][0]['cells'][] = array( 'text' => 'Extra cell' );
		greendawn_guides_check( is_wp_error( Guide_Content::write( $greendawn_id, array( 'sections' => $greendawn_bad_sections ) ) ), 'Malformed table dimensions were accepted.' );
		greendawn_guides_check( is_wp_error( Guide_Content::write( $greendawn_id, array( 'answer' => '' ) ) ), 'A published Guide lost its required opening answer.' );
		$_POST = array( '_acf_post_id' => $greendawn_id, 'post_status' => 'publish', 'acf' => array( 'field_greendawn_guide_answer' => '' ) );
		acf_reset_validation_errors();
		( new Guides() )->validate_editor();
		greendawn_guides_check( (bool) acf_get_validation_errors(), 'SCF validation ignored the pending field values after nonce consumption.' );
		acf_reset_validation_errors();
		$_POST = array();
		wp_update_term( $greendawn_records['term'], Guides::TAXONOMY, array( 'name' => 'Renamed supply topic' ) );
		Guides::flush();
		greendawn_guides_check( str_contains( get_post_meta( $greendawn_id, Guide_Content::SEARCH_META, true ), 'Renamed supply topic' ), 'Topic rename left a stale search projection.' );
		$greendawn_new_sections = array( array( 'heading' => 'Edited section', 'body' => str_repeat( 'Updated ', 410 ) ) );
		greendawn_guides_check( true === Guide_Content::write( $greendawn_id, array( 'sections' => $greendawn_new_sections, 'reviewed_on' => '', 'image' => 0, 'faqs' => array() ) ), 'Optional clears or shorter repeater could not save.' );
		greendawn_guides_check( ! metadata_exists( 'post', $greendawn_id, 'greendawn_guide_sections_0_table_rows_0_cells_1_text' ) && 3 === (int) get_post_meta( $greendawn_id, Guide_Content::MINUTES_META, true ), 'Deleted nested rows survived, or reading time was stale.' );
		$greendawn_count = count( wp_get_post_revisions( $greendawn_id ) );
		Guides::commit( $greendawn_id );
		greendawn_guides_check( $greendawn_count === count( wp_get_post_revisions( $greendawn_id ) ), 'An unchanged commit duplicated revisions.' );
		wp_restore_post_revision( $greendawn_revision );
		greendawn_guides_check( $greendawn_read === Guide_Content::read( $greendawn_id ) && $greendawn_original_revision === get_metadata( 'post', $greendawn_revision, Guides::SNAPSHOT, true ), 'Restore lost nested fields/clears or mutated the original snapshot.' );
		greendawn_guides_check( str_contains( get_post_meta( $greendawn_id, Guide_Content::SEARCH_META, true ), 'unique switchgear marker' ) && 1 === (int) get_post_meta( $greendawn_id, Guide_Content::MINUTES_META, true ), 'Restore did not rebuild derived data.' );
		$greendawn_module = new Guides();
		Guide_Content::write( $greendawn_id, array( 'intro' => 'Keep the current version during a denied restore.' ) );
		wp_set_current_user( 0 );
		greendawn_guides_check( is_wp_error( Guide_Content::write( $greendawn_id, array( 'intro' => 'Unauthorised change' ) ) ), 'Unauthorised writes were allowed.' );
		$greendawn_module->restore_revision( $greendawn_id, $greendawn_revision );
		greendawn_guides_check( 'Keep the current version during a denied restore.' === Guide_Content::read( $greendawn_id )['intro'], 'Unauthorised restore changed structured fields.' );
		wp_set_current_user( $greendawn_user->ID );
		wp_restore_post_revision( $greendawn_revision );
		wp_update_post( array( 'ID' => $greendawn_records['private'], 'post_status' => 'publish' ) );
		greendawn_guides_check( 'draft' === get_post_status( $greendawn_records['private'] ), 'Native API bypassed publication requirements.' );
		wp_update_post( array( 'ID' => $greendawn_id, 'post_password' => 'local-fixture' ) );
		greendawn_guides_check( array() === Guide_Content::public_data( $greendawn_id ), 'Password-protected content was exposed.' );
		wp_update_post( array( 'ID' => $greendawn_id, 'post_password' => '' ) );
		greendawn_guides_check( true === Guide_Content::write( $greendawn_records['region'], array( 'name' => 'Midlands', 'heading' => 'Regional heading', 'intro' => 'Regional introduction', 'considerations' => array( array( 'heading' => 'Operating pattern', 'copy' => 'Assess local operating needs.' ) ), 'places' => array( array( 'name' => 'Birmingham' ) ) ) ), 'Regional fields did not save.' );
		greendawn_guides_check( true === Guide_Content::write( $greendawn_records['hub'], array( 'heading' => 'Guides', 'intro' => 'Practical explanations', 'search_label' => 'Search guides', 'empty_heading' => 'No guides found', 'empty_copy' => 'Try another topic.', 'featured' => $greendawn_id, 'topics' => array( $greendawn_records['term'] ) ) ), 'Hub fields or ordered topic/featured references did not save.' );
		greendawn_guides_check( ! $greendawn_module->use_block_editor( true, get_post( $greendawn_id ) ) && ! $greendawn_module->use_block_editor( true, get_post( $greendawn_records['hub'] ) ) && $greendawn_module->use_block_editor( true, get_post( $greendawn_records['other'] ) ), 'Editor restrictions affected unrelated content.' );
		greendawn_guides_check( 'Keep this body.' === get_post_field( 'post_content', $greendawn_records['other'] ), 'Unrelated blog content changed.' );
		update_option( 'greendawn_guides_fixture_records', $greendawn_records, false );
	} else {
		greendawn_guides_check( ! function_exists( 'get_field' ) && ! empty( $greendawn_records['guide'] ), 'Native phase must run after the SCF phase with SCF skipped.' );
		$greendawn_id = $greendawn_records['guide'];
		greendawn_guides_check( 'Unique nested cell marker' === Guide_Content::read( $greendawn_id )['sections'][0]['table_rows'][0]['cells'][1]['text'] && Guide_Content::AUTHOR === Guide_Content::public_data( $greendawn_id )['author'], 'Native reader lost content or company identity without SCF.' );
		greendawn_guides_check( 'Birmingham' === Guide_Content::read( $greendawn_records['region'] )['places'][0]['name'] && $greendawn_id === Guide_Content::read( $greendawn_records['hub'] )['featured'], 'Page field readers depend on SCF.' );
		$greendawn_revisions = wp_get_post_revisions( $greendawn_id );
		$greendawn_revision = reset( $greendawn_revisions )->ID;
		update_post_meta( $greendawn_id, 'greendawn_guide_intro', 'Changed without the editor plugin.' );
		Guides::flush();
		wp_restore_post_revision( $greendawn_revision );
		greendawn_guides_check( "A company's capacity question." === Guide_Content::read( $greendawn_id )['intro'], 'Native revisions failed with SCF inactive.' );
		greendawn_guides_check( is_wp_error( Guide_Content::write( $greendawn_id, array( 'intro' => 'No editor plugin' ) ) ), 'The editing API should explain its SCF dependency.' );
	}
} catch ( Throwable $greendawn_exception ) {
	$greendawn_error = $greendawn_exception->getMessage();
} finally {
	if ( isset( $greendawn_permalink ) ) {
		update_option( 'permalink_structure', $greendawn_permalink );
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules( false );
	}
	if ( 'scf' !== $greendawn_phase || $greendawn_error ) {
		foreach ( $greendawn_records as $greendawn_key => $greendawn_record ) {
			if ( 'term' === $greendawn_key ) {
				wp_delete_term( $greendawn_record, Guides::TAXONOMY );
			} elseif ( 'image' === $greendawn_key ) {
				wp_delete_attachment( $greendawn_record, true );
			} else {
				wp_delete_post( $greendawn_record, true );
			}
		}
		delete_option( 'greendawn_guides_fixture_records' );
	}
}
if ( $greendawn_error ) {
	WP_CLI::error( $greendawn_error );
}
WP_CLI::success( 'Guides foundation, ' . $greendawn_phase . ' phase verified.' );
