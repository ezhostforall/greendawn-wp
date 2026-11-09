<?php
/**
 * Real WordPress content-model/import checks for the disposable CI installation.
 *
 * @package Greendawn
 */

if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'Content foundation fixtures may run only in the disposable local environment.' );
}

$greendawn_mode   = $args[0] ?? 'native';
$greendawn_assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
};

foreach ( array( 'evcharger', 'team_member' ) as $greendawn_type ) {
	$greendawn_model = get_post_type_object( $greendawn_type );
	$greendawn_assert( null !== $greendawn_model, 'Content type must register on an empty site.' );
	$greendawn_assert( $greendawn_model->can_export, 'Content must remain exportable.' );
	$greendawn_assert( ! post_type_supports( $greendawn_type, 'editor' ), 'CPTs must not expose a layout editor.' );
	$greendawn_assert( ! use_block_editor_for_post_type( $greendawn_type ), 'Gutenberg must stay disabled for these records.' );
	$greendawn_assert( ! $greendawn_model->show_in_rest, 'Structured records must not expose a REST collection.' );
	foreach ( array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ) as $greendawn_role ) {
		$greendawn_allowed = in_array( $greendawn_role, array( 'administrator', 'editor' ), true );
		$greendawn_assert( get_role( $greendawn_role )->has_cap( $greendawn_model->cap->edit_posts ) === $greendawn_allowed, 'Content editing must follow Page capabilities.' );
		$greendawn_assert( get_role( $greendawn_role )->has_cap( $greendawn_model->cap->publish_posts ) === $greendawn_allowed, 'Publishing must follow Page capabilities.' );
	}
}
$greendawn_assert( ! is_post_type_viewable( 'team_member' ), 'Team profiles must not be publicly queryable.' );
$greendawn_assert( false === get_post_type_object( 'team_member' )->has_archive, 'Team must have no archive.' );
$greendawn_assert( false === get_post_type_object( 'evcharger' )->has_archive, 'Do not introduce a charger archive.' );
$greendawn_assert( is_object_in_taxonomy( 'evcharger', 'brands' ) && is_object_in_taxonomy( 'evcharger', 'power_outputs' ), 'Import taxonomy associations must exist.' );
$greendawn_assert( is_taxonomy_hierarchical( 'brands' ) && ! is_taxonomy_hierarchical( 'power_outputs' ), 'Initial hierarchy must match the supplied CPT UI configuration.' );

if ( 'native' === $greendawn_mode ) {
	$greendawn_assert( ! function_exists( 'acf_add_local_field_group' ), 'Native scenario must run without SCF.' );
	( new \Greendawn_Core\Modules\EV_Chargers() )->register_fields();
	( new \Greendawn_Core\Modules\Team_Members() )->register_fields();
	$greendawn_assert( 0 === (int) wp_count_posts( 'evcharger' )->publish, 'Registration must not seed chargers.' );
	$greendawn_assert( 0 === (int) wp_count_posts( 'team_member' )->publish, 'Registration must not seed team members.' );
	WP_CLI::success( 'Native content models work without SCF or seeded posts.' );
	return;
}

$greendawn_charger = get_page_by_path( 'charger-import-fixture', OBJECT, 'evcharger' );
$greendawn_assert( $greendawn_charger instanceof WP_Post, 'WXR import must create the existing evcharger type.' );
$greendawn_id     = $greendawn_charger->ID;
$greendawn_schema = json_decode( file_get_contents( GREENDAWN_CORE_DIR . 'fields/ev-charger-specification.json' ), true )[0]; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reads the bundled local schema.
foreach ( $greendawn_schema['fields'] as $greendawn_field ) {
	$greendawn_assert( metadata_exists( 'post', $greendawn_id, $greendawn_field['name'] ), 'WXR value metadata must survive.' );
	$greendawn_assert( get_post_meta( $greendawn_id, '_' . $greendawn_field['name'], true ) === $greendawn_field['key'], 'ACF field reference metadata must survive.' );
}
$greendawn_assert( '3.75' === get_post_meta( $greendawn_id, 'weight', true ), 'Decimal weight must survive without receiving the schema default.' );
$greendawn_assert( array( 'WiFi', 'RFID' ) === get_post_meta( $greendawn_id, 'connectivity', true ), 'Serialized checkbox values must remain an array.' );
$greendawn_assert( '1,250.00 + installation' === get_post_meta( $greendawn_id, 'price', true ), 'Price must remain text.' );
$greendawn_assert( '150 - 750V DC x2' === get_post_meta( $greendawn_id, 'supply_voltage', true ), 'Voltage ranges must remain text.' );
$greendawn_assert( str_contains( $greendawn_charger->post_content, '[et_pb_section]' ), 'Legacy imported body must remain stored for later mapping.' );
foreach ( array(
	'brands'        => 'syncev',
	'power_outputs' => '7-22kw',
) as $greendawn_taxonomy => $greendawn_slug ) {
	$greendawn_assert( has_term( $greendawn_slug, $greendawn_taxonomy, $greendawn_id ), 'Imported terms must remain assigned.' );
}
$greendawn_assert( str_ends_with( get_permalink( $greendawn_id ), '/evcharger/charger-import-fixture/' ), 'Charger URLs must have no blog front prefix.' );

if ( 'imported' === $greendawn_mode ) {
	WP_CLI::success( 'WXR value/reference metadata, terms, decimal and text formats survived without SCF.' );
	return;
}

$greendawn_assert( function_exists( 'acf_get_field' ), 'SCF must be active for the field scenario.' );
foreach ( $greendawn_schema['fields'] as $greendawn_field ) {
	$greendawn_registered = acf_get_field( $greendawn_field['key'] );
	$greendawn_assert( $greendawn_registered && $greendawn_registered['name'] === $greendawn_field['name'] && $greendawn_registered['type'] === $greendawn_field['type'], 'SCF must resolve the imported field keys and types.' );
	$greendawn_assert( get_field( $greendawn_field['key'], $greendawn_id, false ) === get_post_meta( $greendawn_id, $greendawn_field['name'], true ), 'SCF must resolve the imported value without replacement defaults.' );
}
update_field( 'field_63e76a8eac64c', '4.25', $greendawn_id );
$greendawn_assert( '4.25' === get_post_meta( $greendawn_id, 'weight', true ), 'Editing a decimal must retain its value.' );
$greendawn_assert( 'field_63e76a8eac64c' === get_post_meta( $greendawn_id, '_weight', true ), 'Editing must retain its original field reference.' );
$greendawn_team_id = wp_insert_post(
	array(
		'post_type'   => 'team_member',
		'post_status' => 'draft',
		'post_title'  => 'Team fixture',
		'menu_order'  => 3,
	),
	true
);
$greendawn_assert( ! is_wp_error( $greendawn_team_id ), 'A native team record must save.' );
update_field( 'field_greendawn_team_role', 'Project delivery', $greendawn_team_id );
update_field( 'field_greendawn_team_biography', "First paragraph.\n\nSecond paragraph.", $greendawn_team_id );
update_field( 'field_greendawn_team_linkedin', 'https://www.linkedin.com/in/example/', $greendawn_team_id );
$greendawn_assert( 'Project delivery' === get_post_meta( $greendawn_team_id, 'greendawn_team_role', true ), 'Team structured fields must save.' );
$greendawn_assert( 3 === get_post( $greendawn_team_id )->menu_order, 'Native team ordering must survive.' );
$greendawn_assert( '' === get_post_meta( $greendawn_team_id, 'greendawn_team_connections', true ), 'Connection label must stay optional.' );
$greendawn_refreshes = 0;
add_action(
	'generate_rewrite_rules',
	static function () use ( &$greendawn_refreshes ) {
		++$greendawn_refreshes;
	}
);
$greendawn_existing_rules = get_option( 'rewrite_rules' );
$greendawn_charger_module = new \Greendawn_Core\Modules\EV_Chargers();
$greendawn_charger_module->refresh_rewrites();
$greendawn_assert( 0 === $greendawn_refreshes && get_option( 'rewrite_rules' ) === $greendawn_existing_rules, 'Ordinary requests must not flush rewrite rules.' );
delete_option( 'greendawn_core_charger_rewrite_version' );
$greendawn_charger_module->refresh_rewrites();
$greendawn_charger_module->refresh_rewrites();
$greendawn_assert( 1 === $greendawn_refreshes, 'An already-active plugin update must refresh rules once only.' );
$greendawn_assert( str_contains( get_post( $greendawn_id )->post_content, '[et_pb_section]' ), 'SCF editing must not erase the imported body.' );
WP_CLI::success( 'SCF resolves imported fields; structured edits and native ordering preserve content.' );
