<?php
/**
 * Disposable Fleet editing/defaults, field ownership and native relationship checks.
 *
 * @package Greendawn
 */

if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'Fleet fixtures are restricted to disposable WordPress.' );
}
$greendawn_assert   = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
};
$greendawn_module   = new \Greendawn_Core\Modules\Greendawn_Fleet();
$greendawn_schema   = $greendawn_module->fields();
$greendawn_slugs    = array( 'hellonext-h1-22', 'project-ev-ac-pro-earth-dual-7-22kw', 'simpson-partners-7-22kw', 'sync-ev-22kw', 'sync-ev-22kw-2', 'easee-charger-22kw' );
$greendawn_chargers = array_map( static fn( string $slug ): int => get_page_by_path( $slug, OBJECT, 'evcharger' )->ID, $greendawn_slugs );
$greendawn_image    = attachment_url_to_postid( wp_get_upload_dir()['baseurl'] . '/2026/11/commercial-ev-fleet-charging-bays-1200.webp' );
if ( ! $greendawn_image ) {
	$greendawn_image = attachment_url_to_postid( wp_get_upload_dir()['baseurl'] . '/2026/11/commercial-ev-fleet-charging-bays-1200-scaled.webp' );
}
$greendawn_engineers = attachment_url_to_postid( wp_get_upload_dir()['baseurl'] . '/2026/11/greendawn-engineers-electrical-distribution-1600.webp' );
$greendawn_assert( $greendawn_image > 0 && $greendawn_engineers > 0, 'Both genuine local media fixtures must be present.' );
$greendawn_pages = array();
foreach ( array( 'fleet-charging-solutions', 'gd-fleet-edited', 'gd-fleet-empty', 'gd-fleet-no-core', 'gd-fleet-protected' ) as $greendawn_slug ) {
	$greendawn_id = wp_insert_post(
		array(
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'post_title'    => $greendawn_slug,
			'post_name'     => $greendawn_slug,
			'post_content'  => '<h1>Do not append this legacy layout</h1><p>Preserved Fleet import backup.</p>',
			'post_password' => 'gd-fleet-protected' === $greendawn_slug ? 'secret' : '',
		)
	);
	update_post_meta( $greendawn_id, '_wp_page_template', 'templates/greendawn-fleet.php' );
	$greendawn_pages[ $greendawn_slug ] = $greendawn_id;
}
$greendawn_default = $greendawn_pages['fleet-charging-solutions'];
$greendawn_assert( 'EV Charging For' === get_field( 'field_greendawn_fleet_heading', $greendawn_default ), 'SCF must prefill Fleet source copy before saving.' );
$greendawn_assert( 'Your Fleets' === get_field( 'field_greendawn_fleet_accent', $greendawn_default ), 'Fleet must not inherit Workplace’s accent.' );
$greendawn_assert( ! metadata_exists( 'post', $greendawn_default, 'greendawn_fleet_heading' ), 'Rendering defaults must not seed saved page content.' );
$greendawn_assert( get_field( 'field_greendawn_fleet_chargers', $greendawn_default ) === $greendawn_chargers, 'All six charger destinations must retain their source order.' );
$greendawn_posts = get_field( 'field_greendawn_fleet_posts', $greendawn_default );
$greendawn_assert( 10 === count( $greendawn_posts ), 'All ten source article destinations must be retained.' );
$greendawn_assert( (int) get_field( 'field_greendawn_fleet_hero_image', $greendawn_default ) === $greendawn_image, 'Default media must resolve to native attachments, including scaled originals.' );
$greendawn_assert( ! isset( $greendawn_schema['form'] ) && ! isset( $greendawn_schema['form_heading'] ), 'Fleet must not invent an inline form or import another page’s selection.' );
$greendawn_assert( false === use_block_editor_for_post( get_post( $greendawn_default ) ), 'The fixed Fleet template must disable Gutenberg.' );
$greendawn_assert( use_block_editor_for_post( get_page_by_path( 'policy-example' ) ), 'Ordinary page editing must be unaffected.' );
$greendawn_group = acf_get_local_field_group( 'group_greendawn_fleet' );
$greendawn_assert( 'templates/greendawn-fleet.php' === $greendawn_group['location'][0][0]['value'], 'Fleet fields must be scoped to their own template.' );
foreach ( array( 'chargers', 'posts' ) as $greendawn_name ) {
	$greendawn_choices = apply_filters( 'acf/fields/relationship/query/key=field_greendawn_fleet_' . $greendawn_name, array( 'post_status' => 'any' ) ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores,WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- SCF’s existing hook contract.
	$greendawn_assert( 'publish' === $greendawn_choices['post_status'] && false === $greendawn_choices['has_password'], 'Editor searches must exclude unpublished/passworded selections.' );
}
// Genuine local test media verifies composition; it is not source-photo parity evidence.
update_field( 'field_greendawn_fleet_process_image_1', $greendawn_image, $greendawn_default );
update_field( 'field_greendawn_fleet_process_image_2', $greendawn_engineers, $greendawn_default );
$greendawn_edited = $greendawn_pages['gd-fleet-edited'];
set_post_thumbnail( $greendawn_edited, $greendawn_engineers );
$greendawn_assert( (int) get_field( 'field_greendawn_fleet_hero_image', $greendawn_edited ) === $greendawn_engineers, 'An unset hero must honour the Page’s featured image.' );
update_field( 'field_greendawn_fleet_heading', 'Charging that fits our fleet.', $greendawn_edited );
update_field( 'field_greendawn_fleet_accent', '', $greendawn_edited );
update_field( 'field_greendawn_fleet_intro', '', $greendawn_edited );
update_field( 'field_greendawn_fleet_cost_copy', 'Reviewed fleet operating costs.', $greendawn_edited );
update_field( 'field_greendawn_fleet_consult_title', 'Assess our fleet', $greendawn_edited );
update_field( 'field_greendawn_fleet_cta_url', 'https://www.greendawn.co.uk/enquire/?from=fleet#project', $greendawn_edited );
$greendawn_hidden          = get_posts(
	array(
		'post_type'      => 'evcharger',
		'post_status'    => array( 'draft', 'private', 'publish' ),
		'title'          => 'Hidden product',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);
$greendawn_hidden_articles = array();
foreach ( array( 'draft', 'private', 'publish' ) as $greendawn_status ) {
	$greendawn_hidden_articles[] = wp_insert_post(
		array(
			'post_type'     => 'post',
			'post_status'   => $greendawn_status,
			'post_title'    => 'Hidden Fleet article',
			'post_date'     => '2020-01-01 10:00:00',
			'post_password' => 'publish' === $greendawn_status ? 'secret' : '',
		)
	);
}
// Imported/stale metadata must not expose drafts, wrong types, duplicates or protected records.
update_post_meta( $greendawn_edited, 'greendawn_fleet_chargers', array_merge( array( $greendawn_chargers[5] ), $greendawn_hidden, array( $greendawn_posts[0], $greendawn_chargers[0], $greendawn_chargers[5] ) ) );
update_post_meta( $greendawn_edited, 'greendawn_fleet_posts', array_merge( array( $greendawn_posts[9] ), $greendawn_hidden_articles, array( $greendawn_chargers[0], $greendawn_posts[0], $greendawn_posts[9] ) ) );
$greendawn_assert( '' === get_field( 'field_greendawn_fleet_accent', $greendawn_edited ), 'Saved blank copy must remain blank.' );
$greendawn_assert( str_contains( get_post( $greendawn_edited )->post_content, 'Preserved Fleet import backup.' ), 'Structured edits must preserve imported body content.' );
$greendawn_assert( 'Your Fleets' === get_field( 'field_greendawn_fleet_accent', $greendawn_default ), 'Editing one Page must not change another Page’s defaults.' );
$greendawn_assert( 'Your Workplace' === get_field( 'field_greendawn_workplace_accent', get_page_by_path( 'workplace-charging' )->ID ), 'Fleet edits must not change Workplace content.' );
$greendawn_workplace = new \Greendawn_Core\Modules\Greendawn_Workplace();
$greendawn_assert( $greendawn_schema['consult_copy']['default_value'] !== $greendawn_workplace->fields()['consult_copy']['default_value'], 'Fleet must retain its own process wording.' );
$greendawn_empty = $greendawn_pages['gd-fleet-empty'];
set_post_thumbnail( $greendawn_empty, $greendawn_engineers );
foreach ( array( 'hero_image', 'process_image_1', 'process_image_2' ) as $greendawn_name ) {
	update_post_meta( $greendawn_empty, 'greendawn_fleet_' . $greendawn_name, 0 );
}
foreach ( array( 'chargers', 'posts' ) as $greendawn_name ) {
	update_post_meta( $greendawn_empty, 'greendawn_fleet_' . $greendawn_name, array() );
}
$greendawn_assert( empty( get_field( 'field_greendawn_fleet_hero_image', $greendawn_empty ) ), 'A cleared saved hero must not fall back to the Page’s featured image.' );
update_post_meta( $greendawn_pages['gd-fleet-protected'], 'greendawn_fleet_intro', 'Protected Fleet content must stay private.' );
WP_CLI::success( 'Fleet defaults, independent source process, six/ten selections, saved blanks, editor scope and import preservation verified.' );
