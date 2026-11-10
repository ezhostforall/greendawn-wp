<?php
/**
 * Managed CF7 upgrades must preserve reviewed recipients and saved form properties.
 *
 * @package Greendawn
 */

if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'Attribution fixtures are restricted to disposable WordPress.' );
}
$greendawn_assert  = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
};
$greendawn_form_id = absint( get_option( 'greendawn_core_callback_form', 0 ) );
$greendawn_assert( $greendawn_form_id > 0, 'A configured callback form must exist.' );
$greendawn_legacy_form = get_post_meta( $greendawn_form_id, '_form', true );
$greendawn_legacy_mail = get_post_meta( $greendawn_form_id, '_mail', true );
foreach ( array( 'page-url', 'first_landing', 'first_referrer', 'utmTerm', 'utmContent', 'chargerId', 'chargerTitle', 'chargerURL' ) as $greendawn_field ) {
	$greendawn_legacy_form         = str_replace( '[hidden ' . $greendawn_field . ']', '', $greendawn_legacy_form );
	$greendawn_legacy_mail['body'] = preg_replace( '/^' . preg_quote( $greendawn_field, '/' ) . ':.*\R?/m', '', $greendawn_legacy_mail['body'] );
}
foreach ( array( 'utmSource', 'utmMedium', 'utmCampaign' ) as $greendawn_field ) {
	$greendawn_legacy_form = str_replace( '[hidden ' . $greendawn_field . ']', '[text ' . $greendawn_field . ']', $greendawn_legacy_form );
}
$greendawn_legacy_mail['subject'] = 'Reviewed callback subject';
$greendawn_legacy_mail['body']    = "Reviewed callback introduction\n" . $greendawn_legacy_mail['body'];
update_post_meta( $greendawn_form_id, '_form', $greendawn_legacy_form );
update_post_meta( $greendawn_form_id, '_mail', $greendawn_legacy_mail );
$greendawn_form = wpcf7_contact_form( $greendawn_form_id );
foreach ( \Greendawn_Core\Modules\Lead_Capture::attribution_fields() as $greendawn_field ) {
	$greendawn_assert( 1 === substr_count( $greendawn_form->prop( 'mail' )['body'], '[' . $greendawn_field . ']' ), 'Every attribution mail tag must be included once.' );
	$greendawn_assert( str_contains( $greendawn_form->prop( 'form' ), $greendawn_field . ']' ), 'Existing managed forms must acquire missing transport fields.' );
}
$greendawn_assert( get_post_meta( $greendawn_form_id, '_form', true ) === $greendawn_legacy_form, 'Loading must not rewrite the saved form.' );
$greendawn_assert( get_post_meta( $greendawn_form_id, '_mail', true ) === $greendawn_legacy_mail, 'Loading must not rewrite the saved mail settings.' );
foreach ( array( 'recipient', 'sender', 'subject', 'additional_headers', 'use_html', 'attachments' ) as $greendawn_property ) {
	$greendawn_assert( $greendawn_form->prop( 'mail' )[ $greendawn_property ] === $greendawn_legacy_mail[ $greendawn_property ], 'Reviewed delivery properties must be preserved.' );
}
$greendawn_assert( str_starts_with( $greendawn_form->prop( 'mail' )['body'], 'Reviewed callback introduction' ), 'Custom email copy must remain intact.' );
$greendawn_assert( (bool) get_option( 'greendawn_core_callback_enabled' ), 'Extending attribution must not disable the existing reviewed drawer.' );
$greendawn_ordinary = WPCF7_ContactForm::get_template();
$greendawn_ordinary->set_title( 'Unrelated form fixture' );
$greendawn_ordinary_markup = '<label>Email [email* ordinary-email]</label>[submit "Send"]';
$greendawn_ordinary->set_properties( array( 'form' => $greendawn_ordinary_markup ) );
$greendawn_ordinary_id = $greendawn_ordinary->save();
$greendawn_assert( wpcf7_contact_form( $greendawn_ordinary_id )->prop( 'form' ) === $greendawn_ordinary_markup, 'Unrelated forms must not acquire managed drawer fields.' );
WP_CLI::success( 'Legacy managed forms gain attribution without changing stored forms, recipients or custom copy.' );
