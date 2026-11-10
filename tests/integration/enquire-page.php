<?php
/**
 * Disposable Enquire fields, selected form ownership and public product transport.
 *
 * @package Greendawn
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Exercises Contact Form 7’s existing hook contract.

if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'Enquire fixtures are restricted to disposable WordPress.' );
}
$greendawn_assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
};
$greendawn_module = new \Greendawn_Core\Modules\Greendawn_Enquire();
$greendawn_schema = $greendawn_module->fields();
$greendawn_pages  = array();
foreach ( array( 'enquire', 'gd-enquire-edited', 'gd-enquire-unassigned', 'gd-enquire-invalid-form', 'gd-enquire-no-cf7', 'gd-enquire-no-core', 'gd-enquire-protected', 'gd-enquire-shared-contact', 'gd-enquire-contacts-cleared', 'gd-enquire-long-contact' ) as $greendawn_slug ) {
	$greendawn_existing = get_page_by_path( $greendawn_slug );
	$greendawn_id       = wp_insert_post(
		array(
			'ID'            => $greendawn_existing ? $greendawn_existing->ID : 0,
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'post_name'     => $greendawn_slug,
			'post_title'    => $greendawn_slug,
			'post_content'  => '<h1>Do not append this legacy layout</h1><p>Preserved Enquire import backup.</p>',
			'post_password' => 'gd-enquire-protected' === $greendawn_slug ? 'secret' : '',
		)
	);
	update_post_meta( $greendawn_id, '_wp_page_template', 'templates/greendawn-enquire.php' );
	$greendawn_pages[ $greendawn_slug ] = $greendawn_id;
}
$greendawn_default = $greendawn_pages['enquire'];
foreach ( $greendawn_schema as $greendawn_name => $greendawn_field ) {
	if ( isset( $greendawn_field['default_value'] ) ) {
		$greendawn_assert( get_field( $greendawn_field['key'], $greendawn_default ) === $greendawn_field['default_value'], 'Every Enquire content field must show its unsaved default in SCF.' );
		$greendawn_assert( ! metadata_exists( 'post', $greendawn_default, $greendawn_field['name'] ), 'Default reads must not write page content.' );
	}
}
$greendawn_assert( empty( get_field( 'field_greendawn_enquire_form', $greendawn_default ) ), 'Enquire must not inherit a form from another Page, Customiser or drawer.' );
$greendawn_assert( ! isset( $greendawn_schema['hero_image'], $greendawn_schema['phone'], $greendawn_schema['email'], $greendawn_schema['address'] ), 'The text-only hero/shared contacts must not gain duplicate image/contact controls.' );
$greendawn_group = acf_get_local_field_group( 'group_greendawn_enquire' );
$greendawn_assert( 'templates/greendawn-enquire.php' === $greendawn_group['location'][0][0]['value'], 'SCF fields must belong only to the Enquire template.' );
$greendawn_assert( false === use_block_editor_for_post( get_post( $greendawn_default ) ), 'The fixed Enquire Page must not expose layout editing.' );
$greendawn_assert( use_block_editor_for_post( get_page_by_path( 'policy-example' ) ), 'Ordinary page editing must remain unchanged.' );
$greendawn_choices = apply_filters( 'acf/fields/post_object/query/key=field_greendawn_enquire_form', array( 'post_status' => 'any' ) ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores,WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing SCF filter contract.
$greendawn_assert( 'publish' === $greendawn_choices['post_status'] && false === $greendawn_choices['has_password'], 'Form choices must exclude unpublished/passworded records.' );
$greendawn_form = WPCF7_ContactForm::get_template();
$greendawn_form->set_title( 'Detailed sales enquiry fixture' );
$greendawn_markup            = file_get_contents( '/tmp/cf7-detailed-enquiry-form.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Copied local fixture, no external request.
$greendawn_mail              = $greendawn_form->prop( 'mail' );
$greendawn_mail['recipient'] = 'sales@example.test';
$greendawn_mail['subject']   = 'Reviewed detailed enquiry subject';
$greendawn_mail['body']      = "Existing reviewed mail body\n[fullname]\n[chargerId]\n[chargerTitle]\n[chargerURL]";
$greendawn_form->set_properties(
	array(
		'form' => $greendawn_markup,
		'mail' => $greendawn_mail,
	)
);
$greendawn_form_id = $greendawn_form->save();
update_field( 'field_greendawn_enquire_form', $greendawn_form_id, $greendawn_default );
$greendawn_assert( (int) get_field( 'field_greendawn_enquire_form', $greendawn_default ) === $greendawn_form_id, 'SCF must persist the explicit native form selection.' );
$greendawn_hero = attachment_url_to_postid( wp_get_upload_dir()['baseurl'] . '/2026/11/greendawn-engineers-electrical-distribution-1600.webp' );
set_post_thumbnail( $greendawn_default, $greendawn_hero );
$greendawn_edited = $greendawn_pages['gd-enquire-edited'];
update_field( 'field_greendawn_enquire_heading', 'Talk to our project team.', $greendawn_edited );
update_field( 'field_greendawn_enquire_accent', '', $greendawn_edited );
update_field( 'field_greendawn_enquire_intro', 'An edited introduction.', $greendawn_edited );
update_field( 'field_greendawn_enquire_consultation_title', 'Discuss the requirement', $greendawn_edited );
update_field( 'field_greendawn_enquire_full_quote_copy', '', $greendawn_edited );
update_field( 'field_greendawn_enquire_form_heading', 'Discuss your project', $greendawn_edited );
update_field( 'field_greendawn_enquire_form', $greendawn_form_id, $greendawn_edited );
$greendawn_assert( '' === get_field( 'field_greendawn_enquire_full_quote_copy', $greendawn_edited ), 'Cleared saved process copy must stay empty.' );
$greendawn_assert( 'Full Quote' === get_field( 'field_greendawn_enquire_full_quote_title', $greendawn_default ), 'Page-owned process edits must not rewrite another Page.' );
$greendawn_assert( str_contains( get_post( $greendawn_edited )->post_content, 'Preserved Enquire import backup.' ), 'Structured changes must retain the original imported body.' );
update_post_meta( $greendawn_pages['gd-enquire-invalid-form'], 'greendawn_enquire_form', $greendawn_hero );
update_post_meta( $greendawn_pages['gd-enquire-no-cf7'], 'greendawn_enquire_form', $greendawn_form_id );
update_post_meta( $greendawn_pages['gd-enquire-protected'], 'greendawn_enquire_intro', 'Protected Enquire content must stay private.' );
foreach ( array( 'contact_heading', 'contact_copy' ) as $greendawn_name ) {
	update_post_meta( $greendawn_pages['gd-enquire-contacts-cleared'], 'greendawn_enquire_' . $greendawn_name, '' );
}
// The real CF7 renderer must fill native hidden values before JavaScript can run.
$greendawn_product    = get_page_by_path( 'hellonext-h1-22', OBJECT, 'evcharger' );
$greendawn_context    = \Greendawn_Core\Modules\EV_Chargers::quote_context( $greendawn_product->ID );
$greendawn_page_query = new WP_Query( array( 'page_id' => $greendawn_default ) );
$greendawn_page_query->the_post();
$greendawn_context_filter = static fn(): array => $greendawn_context;
add_filter( 'greendawn_selected_charger', $greendawn_context_filter, 20 );
$greendawn_native         = do_shortcode( '[contact-form-7 id="' . $greendawn_form_id . '"]' );
$greendawn_processor      = new WP_HTML_Tag_Processor( $greendawn_native );
$greendawn_native_values  = array();
while ( $greendawn_processor->next_tag( 'input' ) ) {
	$greendawn_name = $greendawn_processor->get_attribute( 'name' );
	if ( in_array( $greendawn_name, array( 'chargerId', 'chargerTitle', 'chargerURL' ), true ) ) {
		$greendawn_native_values[ $greendawn_name ] = $greendawn_processor->get_attribute( 'value' );
	}
}
$greendawn_expected_values = array(
	'chargerId'    => (string) $greendawn_product->ID,
	'chargerTitle' => $greendawn_context['title'],
	'chargerURL'   => $greendawn_context['url'],
);
$greendawn_assert( $greendawn_native_values === $greendawn_expected_values, 'The native CF7 render must include server-resolved product fields before JavaScript.' );
remove_filter( 'greendawn_selected_charger', $greendawn_context_filter, 20 );
wp_reset_postdata();
// Bind product transport through saved Page/form relationships, independent of client container IDs.
wpcf7_contact_form( $greendawn_form_id );
$greendawn_data = apply_filters(
	'wpcf7_posted_data',
	array(
		'chargerId'    => (string) $greendawn_product->ID,
		'chargerTitle' => 'Forged title',
		'chargerURL'   => 'https://untrusted.example/',
		'fullname'     => 'Unchanged name',
	)
);
$greendawn_assert( $greendawn_data['chargerTitle'] === $greendawn_context['title'] && $greendawn_data['chargerURL'] === $greendawn_context['url'] && 'Unchanged name' === $greendawn_data['fullname'], 'Native sales submissions must resolve identity on the server without trusting a submitted title, URL or container.' );
$greendawn_hidden    = wp_insert_post(
	array(
		'post_type'   => 'evcharger',
		'post_status' => 'private',
		'post_title'  => 'Private enquiry product',
	)
);
$greendawn_protected = wp_insert_post(
	array(
		'post_type'     => 'evcharger',
		'post_status'   => 'publish',
		'post_password' => 'secret',
		'post_title'    => 'Protected enquiry product',
	)
);
foreach ( array( $greendawn_hidden, $greendawn_protected, $greendawn_default, 0, array( $greendawn_product->ID ) ) as $greendawn_invalid ) {
	$greendawn_data = apply_filters(
		'wpcf7_posted_data',
		array(
			'chargerId'    => $greendawn_invalid,
			'chargerTitle' => 'Forged title',
			'chargerURL'   => 'https://untrusted.example/',
		)
	);
	$greendawn_assert( '' === $greendawn_data['chargerId'] && '' === $greendawn_data['chargerTitle'] && '' === $greendawn_data['chargerURL'], 'Invalid, wrong-type, private and passworded selections must not supply product mail data.' );
}
$greendawn_unrelated = WPCF7_ContactForm::get_template();
$greendawn_unrelated->set_title( 'Unrelated sales-context fixture' );
$greendawn_unrelated_id = $greendawn_unrelated->save();
wpcf7_contact_form( $greendawn_unrelated_id );
$greendawn_original = array(
	'chargerId'    => 'unrelated',
	'chargerTitle' => 'Existing unrelated value',
	'chargerURL'   => 'https://example.test/',
);
$greendawn_assert( apply_filters( 'wpcf7_posted_data', $greendawn_original ) === $greendawn_original, 'Unrelated forms must not acquire the Enquire product policy.' );
$greendawn_form = wpcf7_contact_form( $greendawn_form_id );
$greendawn_assert( get_post_meta( $greendawn_form_id, '_form', true ) === $greendawn_markup, 'Selected form rendering must not rewrite saved definitions.' );
$greendawn_assert( $greendawn_form->prop( 'mail' )['recipient'] === $greendawn_mail['recipient'] && $greendawn_form->prop( 'mail' )['body'] === $greendawn_mail['body'], 'Configured recipients and reviewed email content must remain intact.' );
WP_CLI::success( 'Enquire source/defaults, independent form, public product transport, saved clears and import preservation verified.' );
