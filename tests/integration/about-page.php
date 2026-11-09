<?php
/**
 * Disposable fixed-page fixtures and SCF/native-data checks; never run on staging.
 *
 * @package Greendawn
 */

if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'About fixtures are restricted to the disposable local environment.' );
}
$greendawn_assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
};
$greendawn_module = new \Greendawn_Core\Modules\Greendawn_About();
$greendawn_schema = $greendawn_module->fields();
$greendawn_image  = attachment_url_to_postid( wp_get_upload_dir()['baseurl'] . '/2026/11/greendawn-engineers-electrical-distribution-1600.webp' );
$greendawn_assert( $greendawn_image > 0, 'The existing homepage media fixture must be available.' );
$greendawn_team = array();
foreach ( array( 'publish', 'publish', 'publish', 'publish', 'publish', 'draft', 'private' ) as $greendawn_index => $greendawn_status ) {
	$greendawn_id = wp_insert_post(
		array(
			'post_type'   => 'team_member',
			'post_status' => $greendawn_status,
			'post_title'  => 'Member ' . ( $greendawn_index + 1 ),
			'menu_order'  => $greendawn_index + 1,
		)
	);
	set_post_thumbnail( $greendawn_id, $greendawn_image );
	update_post_meta( $greendawn_id, 'greendawn_team_role', 'Commercial project delivery' );
	update_post_meta( $greendawn_id, 'greendawn_team_biography', 'A complete biography for member ' . ( $greendawn_index + 1 ) . '. Second sentence retained in full.' );
	update_post_meta( $greendawn_id, 'greendawn_team_linkedin', 'https://www.linkedin.com/in/example-' . $greendawn_index . '/' );
	$greendawn_team[] = $greendawn_id;
}
$greendawn_post_ids = array();
$greendawn_slugs    = array( 'ozev-ev-charger-grants-from-april-2026', 'ev-charging-for-car-dealership', 'ev-charger-earthing', 'greendawn-ev-charging-survey-pre-installation-guide', 'transitioning-to-electric-fleets', 'ev-charging-cable-theft', 'zaptec-status-codes', 'project-ev-fault-codes', 'is-ozev-funding-enough', 'ev-installation-day-and-beyond' );
foreach ( $greendawn_slugs as $greendawn_index => $greendawn_slug ) {
	$greendawn_existing = get_page_by_path( $greendawn_slug, OBJECT, 'post' );
	$greendawn_id       = $greendawn_existing ? $greendawn_existing->ID : wp_insert_post(
		array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => 'Guide fixture ' . ( $greendawn_index + 1 ),
			'post_name'    => $greendawn_slug,
			'post_date'    => '2020-01-01 10:00:00',
			'post_content' => '<p>Guide fixture content.</p>',
		)
	);
	set_post_thumbnail( $greendawn_id, $greendawn_image );
	$greendawn_post_ids[] = $greendawn_id;
}
$greendawn_form = WPCF7_ContactForm::get_template();
$greendawn_form->set_title( 'About fixture form' );
$greendawn_form->set_properties( array( 'form' => '<p><label>Full name [text* full-name]</label></p><p><label>Email [email* your-email]</label></p><p><label>Message [textarea your-message]</label></p><p>[submit "Send fixture enquiry"]</p>' ) );
$greendawn_form_id = $greendawn_form->save();
$greendawn_pages   = array();
foreach ( array( 'gd-about-default', 'gd-about-edited', 'gd-about-empty', 'gd-about-invalid-form', 'gd-about-no-core' ) as $greendawn_slug ) {
	$greendawn_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $greendawn_slug,
			'post_name'    => $greendawn_slug,
			'post_content' => '<h1>Do not append this legacy layout</h1><p>Preserved import backup.</p>',
		)
	);
	update_post_meta( $greendawn_id, '_wp_page_template', 'templates/greendawn-about.php' );
	$greendawn_pages[ $greendawn_slug ] = $greendawn_id;
}
$greendawn_default = $greendawn_pages['gd-about-default'];
update_post_meta( $greendawn_default, 'greendawn_about_story_image', $greendawn_image );
update_field( 'field_greendawn_about_form', $greendawn_form_id, $greendawn_default );
$greendawn_assert( false === use_block_editor_for_post( get_post( $greendawn_default ) ), 'The new About template must disable Gutenberg.' );
$greendawn_assert( use_block_editor_for_post( get_page_by_path( 'policy-example' ) ), 'Normal pages must retain their existing editor.' );
$greendawn_assert( 'EV Charging' === get_field( 'field_greendawn_about_heading', $greendawn_default ), 'SCF must display the source default before the first page save.' );
$greendawn_assert( ! metadata_exists( 'post', $greendawn_default, 'greendawn_about_heading' ), 'Rendering defaults must not seed saved page content.' );
$greendawn_assert( 5 === count( get_field( 'field_greendawn_about_team_members', $greendawn_default ) ), 'Default selection must contain published team records only.' );
$greendawn_assert( 10 === count( get_field( 'field_greendawn_about_posts', $greendawn_default ) ), 'Default articles must retain all ten source destinations.' );
$greendawn_edited = $greendawn_pages['gd-about-edited'];
update_field( 'field_greendawn_about_heading', 'Charging shaped around your organisation.', $greendawn_edited );
update_field( 'field_greendawn_about_accent', '', $greendawn_edited );
update_field( 'field_greendawn_about_intro', '', $greendawn_edited );
update_field( 'field_greendawn_about_consult_title', 'Assess', $greendawn_edited );
update_field( 'field_greendawn_about_cta_url', 'https://www.greendawn.co.uk/enquire/?from=about#project', $greendawn_edited );
update_field( 'field_greendawn_about_hero_image', $greendawn_image, $greendawn_edited );
// Deliberately malformed relationships model imported/stale metadata; rendering must filter them.
update_post_meta( $greendawn_edited, 'greendawn_about_team_members', array( $greendawn_team[1], $greendawn_team[5], $greendawn_team[6], $greendawn_post_ids[0], $greendawn_team[0], $greendawn_team[1] ) );
update_post_meta( $greendawn_edited, 'greendawn_about_posts', array_reverse( array_slice( $greendawn_post_ids, -10 ) ) );
update_post_meta( $greendawn_edited, 'greendawn_about_form', $greendawn_form_id );
$greendawn_assert( '' === get_field( 'field_greendawn_about_accent', $greendawn_edited ), 'A saved blank accent must not restore a default.' );
$greendawn_assert( str_contains( get_post( $greendawn_edited )->post_content, 'Preserved import backup.' ), 'Structured edits must preserve the imported body backup.' );
$greendawn_empty = $greendawn_pages['gd-about-empty'];
foreach ( array( 'hero_image', 'story_image' ) as $greendawn_name ) {
	update_post_meta( $greendawn_empty, 'greendawn_about_' . $greendawn_name, 0 );
}
foreach ( array( 'team_members', 'posts' ) as $greendawn_name ) {
	update_post_meta( $greendawn_empty, 'greendawn_about_' . $greendawn_name, array() );
}
update_post_meta( $greendawn_pages['gd-about-invalid-form'], 'greendawn_about_form', $greendawn_post_ids[0] );
$greendawn_assert( 0 === greendawn_contact_form_id( $greendawn_post_ids[0] ), 'A non-CF7 record must never be executed as a shortcode.' );
$greendawn_choices = apply_filters( 'acf/fields/relationship/query/key=field_greendawn_about_team_members', array( 'post_status' => 'any' ) ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores,WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Exercises SCF’s existing filter.
$greendawn_assert( 'publish' === $greendawn_choices['post_status'], 'Team searches must exclude draft/private records.' );
WP_CLI::success( 'About defaults, explicit blanks, ordered relationships, CF7 assignment and import backup verified.' );
