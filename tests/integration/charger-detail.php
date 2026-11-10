<?php
/**
 * Disposable product/detail data checks, including absence of schema-default facts.
 *
 * @package Greendawn
 */

if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'Charger detail fixtures are restricted to disposable WordPress.' );
}
$greendawn_assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
};
$greendawn_module = new \Greendawn_Core\Modules\EV_Chargers();
$greendawn_slugs  = array( 'hellonext-h1-22', 'project-ev-ac-pro-earth-dual-7-22kw', 'simpson-partners-7-22kw', 'sync-ev-22kw', 'sync-ev-22kw-2', 'easee-charger-22kw' );
$greendawn_image  = attachment_url_to_postid( wp_get_upload_dir()['baseurl'] . '/2026/11/greendawn-engineers-electrical-distribution-1600.webp' );
$greendawn_body   = '<p>Original imported product description with <strong>useful detail</strong>.</p><p>A second paragraph retained in full.</p>';
$greendawn_specs  = array(
	'brand'                    => 'HelloNext',
	'charging_power'           => '7–22kW',
	'mains_supply'             => 'Three Phase',
	'supply_voltage'           => '150 - 750V DC x2',
	'dimensions'               => '465 x 300 x 170',
	'weight'                   => '0',
	'mounting_options'         => 'Wall and Floor',
	'max_current_amps'         => '32A Three Phase',
	'no_of_output_connections' => '2',
	'output_connector_types'   => 'Type 2 (Socket), Tethered',
	'connectivity'             => array( 'WiFi', 'RFID' ),
	'price'                    => '1,250.00 + installation',
);
foreach ( $greendawn_slugs as $greendawn_index => $greendawn_slug ) {
	$greendawn_id = wp_insert_post(
		array(
			'post_type'    => 'evcharger',
			'post_status'  => 'publish',
			'post_title'   => 'Charger fixture ' . ( $greendawn_index + 1 ),
			'post_name'    => $greendawn_slug,
			'post_content' => $greendawn_body,
		)
	);
	set_post_thumbnail( $greendawn_id, $greendawn_image );
	foreach ( $greendawn_specs as $greendawn_name => $greendawn_value ) {
		update_post_meta( $greendawn_id, $greendawn_name, $greendawn_value );
	}
	$greendawn_assert( str_ends_with( get_permalink( $greendawn_id ), '/evcharger/' . $greendawn_slug . '/' ), 'Every existing audience destination must resolve without a new slug.' );
	$greendawn_assert( str_contains( get_field( 'field_greendawn_charger_description', $greendawn_id ), 'A second paragraph retained in full.' ), 'SCF must prefill native imported copy before the first save.' );
	$greendawn_assert( ! metadata_exists( 'post', $greendawn_id, 'greendawn_charger_description' ), 'Read-only defaults must never write replacement content.' );
	if ( 5 === $greendawn_index ) {
		update_field( 'field_greendawn_charger_description', 'Edited structured product description.', $greendawn_id );
		$greendawn_assert( 'Edited structured product description.' === $greendawn_module->view_data( array(), $greendawn_id )['description'], 'Saved structured copy must take precedence.' );
		$greendawn_assert( get_post( $greendawn_id )->post_content === $greendawn_body, 'Editing must preserve the imported body backup.' );
	}
}
$greendawn_empty = wp_insert_post(
	array(
		'post_type'    => 'evcharger',
		'post_status'  => 'publish',
		'post_title'   => 'Charger empty fixture',
		'post_name'    => 'charger-empty',
		'post_content' => $greendawn_body,
	)
);
update_post_meta( $greendawn_empty, 'greendawn_charger_description', '' );
$greendawn_data = $greendawn_module->view_data( array(), $greendawn_empty );
$greendawn_assert( '' === $greendawn_data['description'], 'A saved blank description must stay blank.' );
$greendawn_assert( array() === $greendawn_data['specifications'], 'Missing fields must not invent facts from SCF defaults.' );
$greendawn_assert( ! metadata_exists( 'post', $greendawn_empty, 'weight' ), 'A missing weight must remain missing.' );
$greendawn_builder = wp_insert_post(
	array(
		'post_type'    => 'evcharger',
		'post_status'  => 'publish',
		'post_title'   => 'Legacy builder charger',
		'post_name'    => 'charger-builder',
		'post_content' => '[et_pb_section][et_pb_text]Legacy builder copy[/et_pb_text][/et_pb_section]',
	)
);
$greendawn_assert( '' === $greendawn_module->view_data( array(), $greendawn_builder )['description'], 'Builder content must require explicit mapping, never execution.' );
foreach ( array( 'draft', 'private', 'publish' ) as $greendawn_status ) {
	$greendawn_hidden = wp_insert_post(
		array(
			'post_type'     => 'evcharger',
			'post_status'   => $greendawn_status,
			'post_title'    => 'Hidden product',
			'post_password' => 'publish' === $greendawn_status ? 'secret' : '',
		)
	);
	$greendawn_assert( array() === \Greendawn_Core\Modules\EV_Chargers::quote_context( $greendawn_hidden ), 'Quote context must reject non-public products.' );
}
$greendawn_assert( array() === \Greendawn_Core\Modules\EV_Chargers::quote_context( get_option( 'greendawn_core_callback_form' ) ), 'Quote context must reject the wrong content type.' );
$greendawn_enquire = get_page_by_path( 'enquire' );
if ( ! $greendawn_enquire ) {
	wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Enquire',
			'post_name'    => 'enquire',
			'post_content' => '<p>Native enquiry fallback destination.</p>',
		)
	);
}
WP_CLI::success( 'Product URLs, raw specifications, copy defaults/blanks and public quote context verified.' );
