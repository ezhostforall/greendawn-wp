<?php
/**
 * Disposable Gallery defaults, attachment order and saved field coverage.
 *
 * @package Greendawn
 */

if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'Gallery fixtures are restricted to disposable WordPress.' );
}
$greendawn_assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
};

$greendawn_source = require GREENDAWN_CORE_DIR . 'includes/gallery-defaults.php';
$greendawn_assert( array( 8, 3, 3, 4, 1 ) === array_map( static fn( array $row ): int => count( $row['images'] ), $greendawn_source['stories'] ), 'All 19 project photo positions must be represented.' );
$greendawn_assert( 39 === count( $greendawn_source['photos'] ), 'All 39 source gallery positions must be represented.' );
$greendawn_assert( '2026/04/20260220_112809.avif' === $greendawn_source['photos'][0] && '2024/11/Air-Bnb-EV-Charger.png' === $greendawn_source['photos'][38], 'The gallery should retain its source order.' );

require_once ABSPATH . 'wp-admin/includes/image.php';
$greendawn_uploads = wp_get_upload_dir();
$greendawn_images  = array();
// Use existing local project photographs to exercise path matching and native responsive output.
foreach (
	array(
		'Johnsons-Cars-Project-EV-Charger-Mazda-showroom.png' => array( 'johnsons-dealership-ev-charging-1200.webp', 'Johnsons dealership charging' ),
		'Johnsons-Cars-Ford-CTEK-Charger-scaled.png' => array( 'commercial-ev-dealership-charging-1200.webp', 'Ford dealership charging' ),
		'Salvation-Army-Charging-Array.png'          => array( 'salvation-army-ev-charging-666.webp', 'Salvation Army charging bays' ),
	) as $greendawn_filename => $greendawn_fixture
) {
	$greendawn_file   = $greendawn_uploads['basedir'] . '/2024/11/' . $greendawn_filename;
	$greendawn_editor = wp_get_image_editor( $greendawn_uploads['basedir'] . '/2026/11/' . $greendawn_fixture[0] );
	$greendawn_assert( ! is_wp_error( $greendawn_editor ), 'The local project image fixture must open.' );
	wp_mkdir_p( dirname( $greendawn_file ) );
	$greendawn_saved = $greendawn_editor->save( $greendawn_file, 'image/png' );
	$greendawn_assert( ! is_wp_error( $greendawn_saved ), 'The local project image fixture must save.' );
	$greendawn_attachment = wp_insert_attachment(
		array(
			'post_title'     => $greendawn_fixture[1],
			'post_excerpt'   => $greendawn_fixture[1] . ' caption.',
			'post_mime_type' => 'image/png',
		),
		$greendawn_file
	);
	wp_update_attachment_metadata( $greendawn_attachment, wp_generate_attachment_metadata( $greendawn_attachment, $greendawn_file ) );
	update_post_meta( $greendawn_attachment, '_wp_attachment_image_alt', $greendawn_fixture[1] );
	$greendawn_images[] = $greendawn_attachment;
}
$greendawn_pages = array();
foreach ( array( 'gallery', 'gd-gallery-edited', 'gd-gallery-empty', 'gd-gallery-protected' ) as $greendawn_slug ) {
	$greendawn_page = wp_insert_post(
		array(
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'post_name'     => $greendawn_slug,
			'post_title'    => 'gallery' === $greendawn_slug ? 'Gallery' : $greendawn_slug,
			'post_content'  => '<h1>Legacy Gallery backup</h1><p>Keep the imported body in storage.</p>',
			'post_password' => 'gd-gallery-protected' === $greendawn_slug ? 'secret' : '',
		)
	);
	update_post_meta( $greendawn_page, '_wp_page_template', 'templates/greendawn-gallery.php' );
	$greendawn_pages[ $greendawn_slug ] = $greendawn_page;
}

$greendawn_default = $greendawn_pages['gallery'];
$greendawn_rows    = get_field( 'field_greendawn_gallery_stories', $greendawn_default );
$greendawn_assert( 5 === count( $greendawn_rows ), 'The SCF editor must show all five initial stories.' );
$greendawn_assert( array_slice( $greendawn_images, 0, 2 ) === array_map( 'intval', $greendawn_rows[0]['images'] ), 'The lead and scaled source images must resolve in story order.' );
$greendawn_assert( array_reverse( $greendawn_images ) === array_map( 'intval', get_field( 'field_greendawn_gallery_photos', $greendawn_default ) ), 'Photo defaults must resolve in gallery order.' );
$greendawn_assert( ! metadata_exists( 'post', $greendawn_default, 'greendawn_gallery_stories' ) && ! metadata_exists( 'post', $greendawn_default, 'greendawn_gallery_photos' ), 'Showing defaults must not write page metadata.' );
$greendawn_assert( ! use_block_editor_for_post( get_post( $greendawn_default ) ) && use_block_editor_for_post( get_page_by_path( 'policy-example' ) ), 'Structured editing must be scoped to Gallery.' );

$greendawn_edited = $greendawn_pages['gd-gallery-edited'];
update_field( 'field_greendawn_gallery_heading', 'Selected projects', $greendawn_edited );
update_field( 'field_greendawn_gallery_accent', '', $greendawn_edited );
update_field(
	'field_greendawn_gallery_stories',
	array(
		array(
			'field_greendawn_gallery_story_title'  => 'Edited project',
			'field_greendawn_gallery_story_facts'  => "Edited fact\nSecond fact",
			'field_greendawn_gallery_story_body'   => '<p style="color:red">Edited <strong>project copy.</strong></p>',
			'field_greendawn_gallery_story_status' => '',
			'field_greendawn_gallery_story_images' => array( $greendawn_images[1], $greendawn_images[0] ),
		),
	),
	$greendawn_edited
);
update_field( 'field_greendawn_gallery_photos', array( $greendawn_images[0], $greendawn_default, $greendawn_images[2] ), $greendawn_edited );
$greendawn_empty = $greendawn_pages['gd-gallery-empty'];
update_field( 'field_greendawn_gallery_stories', array(), $greendawn_empty );
update_field( 'field_greendawn_gallery_photos', array(), $greendawn_empty );
update_field( 'field_greendawn_gallery_cta_heading', '', $greendawn_empty );
$greendawn_assert( str_contains( get_post( $greendawn_edited )->post_content, 'Keep the imported body in storage.' ), 'The imported body must remain stored.' );
WP_CLI::success( 'Gallery story defaults, scaled uploads, saved ordering and cleared selections verified.' );
