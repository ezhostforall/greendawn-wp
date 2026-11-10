<?php
/**
 * Disposable Workplace editing/defaults and native relationship checks.
 *
 * @package Greendawn
 */

if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'Workplace fixtures are restricted to disposable WordPress.' );
}
$greendawn_assert    = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
};
$greendawn_module    = new \Greendawn_Core\Modules\Greendawn_Workplace();
$greendawn_schema    = $greendawn_module->fields();
$greendawn_slugs     = array( 'hellonext-h1-22', 'project-ev-ac-pro-earth-dual-7-22kw', 'simpson-partners-7-22kw', 'sync-ev-22kw', 'sync-ev-22kw-2', 'easee-charger-22kw' );
$greendawn_chargers  = array_map( static fn( string $slug ): int => get_page_by_path( $slug, OBJECT, 'evcharger' )->ID, $greendawn_slugs );
$greendawn_image     = attachment_url_to_postid( wp_get_upload_dir()['baseurl'] . '/2026/11/commercial-ev-workplace-charging-1200.webp' );
$greendawn_engineers = attachment_url_to_postid( wp_get_upload_dir()['baseurl'] . '/2026/11/greendawn-engineers-electrical-distribution-1600.webp' );
$greendawn_assert( $greendawn_image > 0 && $greendawn_engineers > 0, 'Both existing local media fixtures must be present.' );
$greendawn_form = WPCF7_ContactForm::get_template();
$greendawn_form->set_title( 'Workplace fixture form' );
$greendawn_form->set_properties( array( 'form' => file_get_contents( '/tmp/cf7-project-enquiry-form.html' ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reads the copied local fixture.
$greendawn_form_id = $greendawn_form->save();
$greendawn_pages   = array();
foreach ( array( 'workplace-charging', 'gd-workplace-edited', 'gd-workplace-empty', 'gd-workplace-invalid-form', 'gd-workplace-no-core' ) as $greendawn_slug ) {
	$greendawn_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $greendawn_slug,
			'post_name'    => $greendawn_slug,
			'post_content' => '<h1>Do not append this legacy layout</h1><p>Preserved Workplace import backup.</p>',
		)
	);
	update_post_meta( $greendawn_id, '_wp_page_template', 'templates/greendawn-workplace.php' );
	$greendawn_pages[ $greendawn_slug ] = $greendawn_id;
}
$greendawn_default = $greendawn_pages['workplace-charging'];
$greendawn_assert( 'EV Charging For' === get_field( 'field_greendawn_workplace_heading', $greendawn_default ), 'SCF must prefill source copy before saving.' );
$greendawn_assert( ! metadata_exists( 'post', $greendawn_default, 'greendawn_workplace_heading' ), 'Reading defaults must not seed saved content.' );
$greendawn_assert( get_field( 'field_greendawn_workplace_chargers', $greendawn_default ) === $greendawn_chargers, 'Default charger selection must preserve all six source destinations in order.' );
$greendawn_posts = get_field( 'field_greendawn_workplace_posts', $greendawn_default );
$greendawn_assert( 10 === count( $greendawn_posts ), 'All ten source article destinations must be retained.' );
$greendawn_assert( (int) get_field( 'field_greendawn_workplace_hero_image', $greendawn_default ) === $greendawn_image, 'Hero defaults must resolve local attachments.' );
$greendawn_assert( empty( get_field( 'field_greendawn_workplace_form', $greendawn_default ) ), 'The page must not inherit another page’s CF7 form.' );
$greendawn_assert( empty( $greendawn_schema['form_heading']['required'] ), 'The source has no form heading; adding one remains optional.' );
$greendawn_assert( false === use_block_editor_for_post( get_post( $greendawn_default ) ), 'The fixed template must disable layout editing.' );
$greendawn_assert( use_block_editor_for_post( get_page_by_path( 'policy-example' ) ), 'Normal page editing must remain unchanged.' );
foreach ( array(
	'relationship' => 'chargers',
	'post_object'  => 'form',
) as $greendawn_type => $greendawn_name ) {
	$greendawn_choices = apply_filters( 'acf/fields/' . $greendawn_type . '/query/key=field_greendawn_workplace_' . $greendawn_name, array( 'post_status' => 'any' ) ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores,WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- SCF’s existing hook contract.
	$greendawn_assert( 'publish' === $greendawn_choices['post_status'] && false === $greendawn_choices['has_password'], 'Editor searches must exclude unpublished and password-protected records.' );
}
update_field( 'field_greendawn_workplace_form', $greendawn_form_id, $greendawn_default );
// Use genuine local fixture media for visual checks; these are not source-photo parity evidence.
update_field( 'field_greendawn_workplace_process_image_1', $greendawn_engineers, $greendawn_default );
update_field( 'field_greendawn_workplace_process_image_2', $greendawn_image, $greendawn_default );
$greendawn_edited = $greendawn_pages['gd-workplace-edited'];
update_field( 'field_greendawn_workplace_heading', 'Charging for our team.', $greendawn_edited );
update_field( 'field_greendawn_workplace_accent', '', $greendawn_edited );
update_field( 'field_greendawn_workplace_intro', '', $greendawn_edited );
update_field( 'field_greendawn_workplace_cost_copy', 'Reviewed workplace cost description.', $greendawn_edited );
update_field( 'field_greendawn_workplace_consult_title', 'Assess', $greendawn_edited );
update_field( 'field_greendawn_workplace_form_heading', 'Discuss your workplace', $greendawn_edited );
update_field( 'field_greendawn_workplace_form', $greendawn_form_id, $greendawn_edited );
update_field( 'field_greendawn_workplace_cta_url', 'https://www.greendawn.co.uk/enquire/?from=workplace#project', $greendawn_edited );
$greendawn_hidden = get_posts(
	array(
		'post_type'      => 'evcharger',
		'post_status'    => array( 'draft', 'private', 'publish' ),
		'title'          => 'Hidden product',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);
// Simulate imported/stale metadata, including duplicates and wrong content types.
update_post_meta( $greendawn_edited, 'greendawn_workplace_chargers', array_merge( array( $greendawn_chargers[2] ), $greendawn_hidden, array( $greendawn_posts[0], $greendawn_chargers[0], $greendawn_chargers[2] ) ) );
update_post_meta( $greendawn_edited, 'greendawn_workplace_posts', array( $greendawn_posts[9], $greendawn_chargers[0], $greendawn_posts[0], $greendawn_posts[9] ) );
$greendawn_assert( '' === get_field( 'field_greendawn_workplace_accent', $greendawn_edited ), 'Saved blanks must not restore copy defaults.' );
$greendawn_assert( str_contains( get_post( $greendawn_edited )->post_content, 'Preserved Workplace import backup.' ), 'Structured editing must preserve the legacy body.' );
$greendawn_assert( 'EV Charging For' === get_field( 'field_greendawn_workplace_heading', $greendawn_default ), 'Editing one page must not change another’s defaults.' );
$greendawn_assert( 'EV Charging' === get_field( 'field_greendawn_about_heading', get_page_by_path( 'gd-about-default' )->ID ), 'Workplace editing must not change About fields.' );
$greendawn_empty = $greendawn_pages['gd-workplace-empty'];
foreach ( array( 'hero_image', 'process_image_1', 'process_image_2' ) as $greendawn_name ) {
	update_post_meta( $greendawn_empty, 'greendawn_workplace_' . $greendawn_name, 0 );
}
foreach ( array( 'chargers', 'posts' ) as $greendawn_name ) {
	update_post_meta( $greendawn_empty, 'greendawn_workplace_' . $greendawn_name, array() );
}
update_post_meta( $greendawn_pages['gd-workplace-invalid-form'], 'greendawn_workplace_form', $greendawn_posts[0] );
WP_CLI::success( 'Workplace defaults, six/ten destinations, independent CF7 assignment, saved blanks, editor scope and import preservation verified.' );
