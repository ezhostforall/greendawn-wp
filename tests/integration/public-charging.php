<?php
/**
 * Disposable Public Charging defaults, canonical language and native content checks.
 *
 * @package Greendawn
 */

if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'Public Charging fixtures are restricted to disposable WordPress.' );
}
$greendawn_assert    = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
};
$greendawn_module    = new \Greendawn_Core\Modules\Greendawn_Public_Charging();
$greendawn_schema    = $greendawn_module->fields();
$greendawn_canonical = \Greendawn_Core\Modules\Greendawn_Process::defaults();
$greendawn_legacy    = \Greendawn_Core\Modules\Greendawn_Process::homepage_defaults();
$greendawn_slugs     = array( 'hellonext-h1-22', 'project-ev-ac-pro-earth-dual-7-22kw', 'simpson-partners-7-22kw', 'sync-ev-22kw', 'sync-ev-22kw-2', 'easee-charger-22kw' );
$greendawn_chargers  = array_map( static fn( string $slug ): int => get_page_by_path( $slug, OBJECT, 'evcharger' )->ID, $greendawn_slugs );
$greendawn_image     = attachment_url_to_postid( wp_get_upload_dir()['baseurl'] . '/2026/11/commercial-ev-destination-hospitality-1200.webp' );
if ( ! $greendawn_image ) {
	$greendawn_image = attachment_url_to_postid( wp_get_upload_dir()['baseurl'] . '/2026/11/commercial-ev-destination-hospitality-1200-scaled.webp' );
}
$greendawn_assert( $greendawn_image > 0, 'The genuine local hospitality hero fixture must be present.' );
$greendawn_pages = array();
foreach ( array( 'public-charging', 'gd-public-edited', 'gd-public-empty', 'gd-public-no-core', 'gd-public-protected' ) as $greendawn_slug ) {
	$greendawn_id = wp_insert_post(
		array(
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'post_title'    => $greendawn_slug,
			'post_name'     => $greendawn_slug,
			'post_content'  => '<h1>Do not append this legacy layout</h1><h2>Our Process</h2><p>We begin with a consultation and site survey, carried out on-site.</p><h2>Our Process</h2><p>Preserved Public import backup.</p>',
			'post_password' => 'gd-public-protected' === $greendawn_slug ? 'secret' : '',
		)
	);
	update_post_meta( $greendawn_id, '_wp_page_template', 'templates/greendawn-public-charging.php' );
	$greendawn_pages[ $greendawn_slug ] = $greendawn_id;
}
$greendawn_default = $greendawn_pages['public-charging'];
$greendawn_assert( 'Public EV' === get_field( 'field_greendawn_public_heading', $greendawn_default ), 'SCF must prefill Public source copy before saving.' );
$greendawn_assert( ! metadata_exists( 'post', $greendawn_default, 'greendawn_public_heading' ), 'Reading defaults must not write saved metadata.' );
$greendawn_assert( get_field( 'field_greendawn_public_chargers', $greendawn_default ) === $greendawn_chargers, 'All six native charger destinations must retain source order.' );
$greendawn_posts = get_field( 'field_greendawn_public_posts', $greendawn_default );
$greendawn_assert( 10 === count( $greendawn_posts ), 'All ten source article destinations must remain.' );
$greendawn_assert( (int) get_field( 'field_greendawn_public_hero_image', $greendawn_default ) === $greendawn_image, 'Hero defaults must resolve local native/scaled attachments.' );
$greendawn_assert( ! isset( $greendawn_schema['form'] ) && ! isset( $greendawn_schema['process_image_1'] ) && ! isset( $greendawn_schema['process_image_2'] ), 'The approved layout must not add a form or legacy process-photo fields.' );
$greendawn_assert( false === use_block_editor_for_post( get_post( $greendawn_default ) ), 'The fixed Public template must disable Gutenberg.' );
$greendawn_assert( use_block_editor_for_post( get_page_by_path( 'policy-example' ) ), 'Ordinary page editing must remain unchanged.' );
$greendawn_group = acf_get_local_field_group( 'group_greendawn_public' );
$greendawn_assert( 'templates/greendawn-public-charging.php' === $greendawn_group['location'][0][0]['value'], 'Public fields must be scoped to their own template.' );
foreach ( $greendawn_canonical as $greendawn_name => $greendawn_value ) {
	$greendawn_assert( $greendawn_schema[ $greendawn_name ]['default_value'] === $greendawn_value, 'Public process defaults must use the canonical definition.' );
	$greendawn_assert( get_field( 'field_greendawn_public_' . $greendawn_name, $greendawn_default ) === $greendawn_value, 'Every canonical process field must be registered and available in SCF.' );
	$greendawn_assert( ! metadata_exists( 'post', $greendawn_default, 'greendawn_public_' . $greendawn_name ), 'Canonical default reads must not seed saved page copy.' );
}
$greendawn_home = wp_insert_post(
	array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'post_title'  => 'Saved homepage process',
		'post_name'   => 'gd-process-saved-home',
	)
);
update_post_meta( $greendawn_home, '_wp_page_template', 'templates/greendawn-homepage.php' );
foreach ( $greendawn_legacy as $greendawn_name => $greendawn_value ) {
	$greendawn_assert( get_field( 'field_greendawn_home_' . $greendawn_name, $greendawn_home ) === $greendawn_value, 'Homepage defaults must map through existing keys.' );
}
update_field( 'field_greendawn_home_process_title', 'Our saved homepage route.', $greendawn_home );
update_field( 'field_greendawn_home_consult_label', 'Saved consultation', $greendawn_home );
update_field( 'field_greendawn_home_consult_description', 'Existing homepage consultation copy.', $greendawn_home );
$greendawn_assert( 'Our saved homepage route.' === get_field( 'field_greendawn_home_process_title', $greendawn_home ), 'Canonical extraction must preserve existing homepage edits and keys.' );
$greendawn_edited = $greendawn_pages['gd-public-edited'];
update_field( 'field_greendawn_public_heading', 'Public charging for our visitors.', $greendawn_edited );
update_field( 'field_greendawn_public_accent', '', $greendawn_edited );
update_field( 'field_greendawn_public_intro', '', $greendawn_edited );
update_field( 'field_greendawn_public_ac_copy', 'Reviewed AC guidance for this site.', $greendawn_edited );
update_field( 'field_greendawn_public_process_intro', '', $greendawn_edited );
update_field( 'field_greendawn_public_consult_copy', '', $greendawn_edited );
update_field( 'field_greendawn_public_design_title', 'Assess the site', $greendawn_edited );
update_field( 'field_greendawn_public_cta_url', 'https://www.greendawn.co.uk/enquire/?from=public#project', $greendawn_edited );
update_field( 'field_greendawn_public_final_cta_url', 'https://greendawn.co.uk/enquire/?from=public-final#consultation', $greendawn_edited );
$greendawn_hidden          = get_posts(
	array(
		'post_type'      => 'evcharger',
		'post_status'    => array( 'draft', 'private', 'publish' ),
		'title'          => 'Hidden product',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);
$greendawn_hidden_articles = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => array( 'draft', 'private', 'publish' ),
		'title'          => 'Hidden Fleet article',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);
update_post_meta( $greendawn_edited, 'greendawn_public_chargers', array_merge( array( $greendawn_chargers[3] ), $greendawn_hidden, array( $greendawn_posts[0], $greendawn_chargers[0], $greendawn_chargers[3] ) ) );
update_post_meta( $greendawn_edited, 'greendawn_public_posts', array_merge( array( $greendawn_posts[9] ), $greendawn_hidden_articles, array( $greendawn_chargers[0], $greendawn_posts[0], $greendawn_posts[9] ) ) );
$greendawn_assert( '' === get_field( 'field_greendawn_public_consult_copy', $greendawn_edited ), 'Cleared saved Public process copy must stay empty.' );
$greendawn_assert( str_contains( get_post( $greendawn_edited )->post_content, 'Preserved Public import backup.' ), 'Structured edits must preserve the legacy process/body backup.' );
$greendawn_assert( get_field( 'field_greendawn_public_consult_copy', $greendawn_default ) === $greendawn_canonical['consult_copy'], 'Public edits must not change other Pages/defaults.' );
$greendawn_assert( 'Existing homepage consultation copy.' === get_field( 'field_greendawn_home_consult_description', $greendawn_home ), 'Public edits must not rewrite saved homepage process content.' );
$greendawn_fleet     = new \Greendawn_Core\Modules\Greendawn_Fleet();
$greendawn_workplace = new \Greendawn_Core\Modules\Greendawn_Workplace();
$greendawn_assert( $greendawn_fleet->fields()['consult_copy']['default_value'] !== $greendawn_canonical['consult_copy'] && $greendawn_workplace->fields()['consult_copy']['default_value'] !== $greendawn_canonical['consult_copy'], 'Existing audience processes must keep their own wording.' );
$greendawn_empty = $greendawn_pages['gd-public-empty'];
set_post_thumbnail( $greendawn_empty, $greendawn_image );
update_post_meta( $greendawn_empty, 'greendawn_public_hero_image', 0 );
update_post_meta( $greendawn_empty, 'greendawn_public_chargers', array() );
update_post_meta( $greendawn_empty, 'greendawn_public_posts', array() );
$greendawn_assert( empty( get_field( 'field_greendawn_public_hero_image', $greendawn_empty ) ), 'A saved cleared hero must not restore the featured image.' );
update_post_meta( $greendawn_pages['gd-public-protected'], 'greendawn_public_intro', 'Protected Public content must stay private.' );
WP_CLI::success( 'Public defaults, canonical/homepage compatibility, independent edits, six/ten selections and import preservation verified.' );
