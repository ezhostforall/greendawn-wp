<?php
/**
 * Disposable Customer Care defaults, support form ownership and safe destinations.
 *
 * @package Greendawn
 */

if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'Customer Care fixtures are restricted to disposable WordPress.' );
}
$greendawn_assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
};
$greendawn_module = new \Greendawn_Core\Modules\Greendawn_Customer_Care();
$greendawn_schema = $greendawn_module->fields();
$greendawn_pages  = array();
foreach ( array( 'customer-care', 'gd-care-edited', 'gd-care-cleared', 'gd-care-unassigned', 'gd-care-invalid-form', 'gd-care-draft-form', 'gd-care-no-cf7', 'gd-care-no-core', 'gd-care-invalid-whatsapp', 'gd-care-protected' ) as $greendawn_slug ) {
	$greendawn_id = wp_insert_post(
		array(
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'post_name'     => $greendawn_slug,
			'post_title'    => 'customer-care' === $greendawn_slug ? 'Customer Care' : $greendawn_slug,
			'post_content'  => '<h1>Do not append this legacy support layout</h1><p>Preserved Customer Care import backup.</p>',
			'post_password' => 'gd-care-protected' === $greendawn_slug ? 'secret' : '',
		)
	);
	update_post_meta( $greendawn_id, '_wp_page_template', 'templates/greendawn-customer-care.php' );
	$greendawn_pages[ $greendawn_slug ] = $greendawn_id;
}
$greendawn_default = $greendawn_pages['customer-care'];
foreach ( $greendawn_schema as $greendawn_field ) {
	if ( isset( $greendawn_field['default_value'] ) ) {
		$greendawn_assert( get_field( $greendawn_field['key'], $greendawn_default ) === $greendawn_field['default_value'], 'SCF must display the complete unsaved Customer Care source defaults.' );
		$greendawn_assert( ! metadata_exists( 'post', $greendawn_default, $greendawn_field['name'] ), 'Reading support defaults must not write metadata.' );
	}
}
$greendawn_assert( empty( get_field( 'field_greendawn_customer_care_form', $greendawn_default ) ), 'Customer Care must not inherit a sales, global Contact or drawer form.' );
$greendawn_assert( ! isset( $greendawn_schema['hero_image'] ), 'The approved support hero must remain text-only.' );
$greendawn_assert( false === use_block_editor_for_post( get_post( $greendawn_default ) ), 'The fixed support Page must not expose layout editing.' );
$greendawn_assert( use_block_editor_for_post( get_page_by_path( 'policy-example' ) ), 'Ordinary Page editing must remain available.' );
$greendawn_group = acf_get_local_field_group( 'group_greendawn_customer_care' );
$greendawn_assert( 'templates/greendawn-customer-care.php' === $greendawn_group['location'][0][0]['value'], 'Support fields must be scoped to their fixed template.' );
$greendawn_choices = $greendawn_module->published_choices( array( 'post_status' => 'any' ) );
$greendawn_assert( 'publish' === $greendawn_choices['post_status'] && false === $greendawn_choices['has_password'], 'Support form choices must exclude unpublished/passworded forms.' );
$greendawn_sales_id     = (int) get_post_meta( get_page_by_path( 'enquire' )->ID, 'greendawn_enquire_form', true );
$greendawn_sales_form   = get_post_meta( $greendawn_sales_id, '_form', true );
$greendawn_sales_mail   = get_post_meta( $greendawn_sales_id, '_mail', true );
$greendawn_contact_form = get_theme_mod( 'greendawn_contact_form', 0 );
$greendawn_form         = WPCF7_ContactForm::get_template();
$greendawn_form->set_title( 'Customer Care support fixture' );
$greendawn_markup            = file_get_contents( '/tmp/cf7-customer-care-form.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Copied local fixture only.
$greendawn_mail              = $greendawn_form->prop( 'mail' );
$greendawn_mail['recipient'] = 'support@example.test';
$greendawn_mail['subject']   = 'Reviewed support subject: [issue-type]';
$greendawn_mail['body']      = "[your-name]\n[your-email]\n[phone]\n[issue-type]\n[issue-summary]\n[page-url]";
$greendawn_form->set_properties(
	array(
		'form' => $greendawn_markup,
		'mail' => $greendawn_mail,
	)
);
$greendawn_form_id = $greendawn_form->save();
$greendawn_assert( $greendawn_form_id !== $greendawn_sales_id, 'Support and sales must use independent form records.' );
update_field( 'field_greendawn_customer_care_form', $greendawn_form_id, $greendawn_default );
$greendawn_assert( (int) get_field( 'field_greendawn_customer_care_form', $greendawn_default ) === $greendawn_form_id, 'The support form selection must persist.' );
$greendawn_draft_form = wp_insert_post(
	array(
		'post_type'   => 'wpcf7_contact_form',
		'post_status' => 'draft',
		'post_title'  => 'Unavailable support fixture',
	)
);
update_post_meta( $greendawn_pages['gd-care-draft-form'], 'greendawn_customer_care_form', $greendawn_draft_form );
update_post_meta( $greendawn_pages['gd-care-invalid-form'], 'greendawn_customer_care_form', $greendawn_default );
update_post_meta( $greendawn_pages['gd-care-no-cf7'], 'greendawn_customer_care_form', $greendawn_form_id );
$greendawn_edited = $greendawn_pages['gd-care-edited'];
update_field( 'field_greendawn_customer_care_heading', 'Support for our customers.', $greendawn_edited );
update_field( 'field_greendawn_customer_care_accent', '', $greendawn_edited );
update_field( 'field_greendawn_customer_care_intro', 'An edited support introduction.', $greendawn_edited );
update_field( 'field_greendawn_customer_care_point_1_title', 'Reviewed customer support', $greendawn_edited );
update_field( 'field_greendawn_customer_care_point_1_copy', 'Edited continuation <strong>rendered as text</strong>.', $greendawn_edited );
update_field( 'field_greendawn_customer_care_contact_heading', 'Contact support', $greendawn_edited );
update_field( 'field_greendawn_customer_care_whatsapp_heading', 'Speak to support on WhatsApp', $greendawn_edited );
update_field( 'field_greendawn_customer_care_whatsapp_label', 'Message support', $greendawn_edited );
update_field( 'field_greendawn_customer_care_whatsapp_url', 'https://wa.me/447700900123?text=Support', $greendawn_edited );
update_field( 'field_greendawn_customer_care_form_heading', 'Ask our support team', $greendawn_edited );
update_field( 'field_greendawn_customer_care_form', $greendawn_form_id, $greendawn_edited );
$greendawn_assert( '' === get_field( 'field_greendawn_customer_care_accent', $greendawn_edited ), 'Saved support clears must not restore defaults.' );
$greendawn_assert( 'Customer' === get_field( 'field_greendawn_customer_care_heading', $greendawn_default ), 'Support edits must remain Page-owned.' );
$greendawn_assert( str_contains( get_post( $greendawn_edited )->post_content, 'Preserved Customer Care import backup.' ), 'Structured editing must preserve the imported body backup.' );
foreach ( array( 'intro', 'whatsapp_heading', 'whatsapp_label', 'whatsapp_url', 'form_heading', 'contact_heading' ) as $greendawn_name ) {
	update_field( 'field_greendawn_customer_care_' . $greendawn_name, '', $greendawn_pages['gd-care-cleared'] );
}
foreach ( range( 1, 4 ) as $greendawn_number ) {
	foreach ( array( 'title', 'copy' ) as $greendawn_part ) {
		update_field( 'field_greendawn_customer_care_point_' . $greendawn_number . '_' . $greendawn_part, '', $greendawn_pages['gd-care-cleared'] );
	}
}
update_post_meta( $greendawn_pages['gd-care-invalid-whatsapp'], 'greendawn_customer_care_whatsapp_url', 'https://wa.me.untrusted.example/443455120044' );
update_post_meta( $greendawn_pages['gd-care-protected'], 'greendawn_customer_care_intro', 'Protected support content must stay private.' );
foreach ( array( 'https://wa.me/443455120044', 'https://wa.me/447700900123?text=Support' ) as $greendawn_url ) {
	$greendawn_assert( $greendawn_url === $greendawn_module->whatsapp_url( '', $greendawn_url ), 'Valid WhatsApp routes must remain native links without an external API request.' );
	$greendawn_assert( true === $greendawn_module->validate_whatsapp( true, $greendawn_url ), 'Valid support destinations must be accepted by SCF.' );
}
foreach ( array( '', 'javascript:alert(1)', 'http://wa.me/443455120044', 'https://wa.me.untrusted.example/443455120044', 'https://user@wa.me/443455120044', 'https://wa.me:444/443455120044', 'https://wa.me/not-a-number', 'https://example.test/enquire/' ) as $greendawn_url ) {
	$greendawn_assert( '' === $greendawn_module->whatsapp_url( '', $greendawn_url ), 'Invalid WhatsApp destinations must not become support buttons.' );
	$greendawn_assert( '' === $greendawn_url || true !== $greendawn_module->validate_whatsapp( true, $greendawn_url ), 'Non-empty invalid support URLs must be rejected in SCF.' );
}
$greendawn_assert( get_post_meta( $greendawn_sales_id, '_form', true ) === $greendawn_sales_form && get_post_meta( $greendawn_sales_id, '_mail', true ) === $greendawn_sales_mail, 'Support setup must not rewrite sales fields or recipient settings.' );
$greendawn_assert( get_post_meta( $greendawn_form_id, '_form', true ) === $greendawn_markup && get_post_meta( $greendawn_form_id, '_mail', true ) === $greendawn_mail, 'Page editing must not rewrite the configured support definition or recipient.' );
$greendawn_assert( get_theme_mod( 'greendawn_contact_form', 0 ) === $greendawn_contact_form, 'The legacy Contact Customiser form must remain unchanged.' );
$greendawn_assert( (int) get_post_meta( get_page_by_path( 'business-ev-charging-installations' )->ID, 'greendawn_business_form', true ) === $greendawn_sales_id, 'Business must retain its reviewed sales form.' );
WP_CLI::success( 'Customer Care source defaults, five-field support form, independent recipients, trusted WhatsApp, saved clears and import preservation verified.' );
