<?php
/**
 * Disposable Business landing content, explicit form and trusted video checks.
 *
 * @package Greendawn
 */

if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'Business fixtures are restricted to disposable WordPress.' );
}
$greendawn_assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
};
$greendawn_module = new \Greendawn_Core\Modules\Greendawn_Business();
$greendawn_schema = $greendawn_module->fields();
$greendawn_pages  = array();
foreach ( array( 'business-ev-charging-installations', 'gd-business-edited', 'gd-business-empty-media', 'gd-business-unassigned', 'gd-business-invalid-form', 'gd-business-no-cf7', 'gd-business-no-core', 'gd-business-invalid-video', 'gd-business-protected' ) as $greendawn_slug ) {
	$greendawn_id = wp_insert_post(
		array(
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'post_name'     => $greendawn_slug,
			'post_title'    => $greendawn_slug,
			'post_content'  => '<h1>Do not append this legacy layout</h1><p>Preserved Business import backup.</p>',
			'post_password' => 'gd-business-protected' === $greendawn_slug ? 'secret' : '',
		)
	);
	update_post_meta( $greendawn_id, '_wp_page_template', 'templates/greendawn-business-installations.php' );
	$greendawn_pages[ $greendawn_slug ] = $greendawn_id;
}
$greendawn_default = $greendawn_pages['business-ev-charging-installations'];
foreach ( $greendawn_schema as $greendawn_field ) {
	if ( isset( $greendawn_field['default_value'] ) ) {
		$greendawn_assert( get_field( $greendawn_field['key'], $greendawn_default ) === $greendawn_field['default_value'], 'SCF must display all source service/testimonial defaults before saving.' );
		$greendawn_assert( ! metadata_exists( 'post', $greendawn_default, $greendawn_field['name'] ), 'Displaying source defaults must not write page metadata.' );
	}
}
$greendawn_assert( false === use_block_editor_for_post( get_post( $greendawn_default ) ), 'The fixed landing template must disable layout editing.' );
$greendawn_assert( use_block_editor_for_post( get_page_by_path( 'policy-example' ) ), 'Ordinary Pages must retain their existing editor.' );
$greendawn_group = acf_get_local_field_group( 'group_greendawn_business' );
$greendawn_assert( 'templates/greendawn-business-installations.php' === $greendawn_group['location'][0][0]['value'], 'Business fields must be scoped to the new fixed template.' );
$greendawn_assert( empty( get_field( 'field_greendawn_business_form', $greendawn_default ) ), 'A form must not be inherited from Enquire, the Customiser or the drawer.' );
$greendawn_poster = (int) get_field( 'field_greendawn_business_testimonial_1_image', $greendawn_default );
$greendawn_assert( $greendawn_poster > 0, 'The local Johnsons reference image must resolve as a native attachment.' );
$greendawn_form_id = (int) get_post_meta( get_page_by_path( 'enquire' )->ID, 'greendawn_enquire_form', true );
$greendawn_assert( $greendawn_form_id > 0, 'The reviewed detailed Enquire fixture must already exist.' );
$greendawn_form_before = get_post_meta( $greendawn_form_id, '_form', true );
$greendawn_mail_before = get_post_meta( $greendawn_form_id, '_mail', true );
update_field( 'field_greendawn_business_form', $greendawn_form_id, $greendawn_default );
$greendawn_assert( (int) get_field( 'field_greendawn_business_form', $greendawn_default ) === $greendawn_form_id, 'The same reviewed form may be selected explicitly on both Pages.' );
set_post_thumbnail( $greendawn_default, $greendawn_poster );
$greendawn_edited = $greendawn_pages['gd-business-edited'];
update_field( 'field_greendawn_business_heading', 'Charging for our business.', $greendawn_edited );
update_field( 'field_greendawn_business_accent', '', $greendawn_edited );
update_field( 'field_greendawn_business_intro', '', $greendawn_edited );
update_field( 'field_greendawn_business_service_2_copy', 'Reviewed installation description.', $greendawn_edited );
update_field( 'field_greendawn_business_testimonial_1_quote', 'Edited quote <strong>retained as text</strong>.', $greendawn_edited );
update_field( 'field_greendawn_business_testimonial_1_attribution', 'Reviewed client attribution', $greendawn_edited );
update_field( 'field_greendawn_business_testimonial_1_video', '', $greendawn_edited );
update_field( 'field_greendawn_business_form', 0, $greendawn_edited );
$greendawn_assert( '' === get_field( 'field_greendawn_business_testimonial_1_video', $greendawn_edited ), 'Cleared saved videos must not restore a default.' );
$greendawn_assert( get_field( 'field_greendawn_business_testimonial_1_quote', $greendawn_default ) === $greendawn_schema['testimonial_1_quote']['default_value'], 'Testimonial edits must remain Page-owned.' );
$greendawn_assert( str_contains( get_post( $greendawn_edited )->post_content, 'Preserved Business import backup.' ), 'Structured editing must retain the imported body backup.' );
foreach ( range( 1, 3 ) as $greendawn_number ) {
	update_post_meta( $greendawn_pages['gd-business-empty-media'], 'greendawn_business_testimonial_' . $greendawn_number . '_image', 0 );
	update_post_meta( $greendawn_pages['gd-business-empty-media'], 'greendawn_business_testimonial_' . $greendawn_number . '_video', '' );
	update_post_meta( $greendawn_pages['gd-business-invalid-video'], 'greendawn_business_testimonial_' . $greendawn_number . '_video', 'https://www.youtube.com.untrusted.example/embed/t96HrzEREM0' );
}
$greendawn_assert( empty( get_field( 'field_greendawn_business_testimonial_1_image', $greendawn_pages['gd-business-empty-media'] ) ), 'Cleared saved local images must stay cleared.' );
update_post_meta( $greendawn_pages['gd-business-invalid-form'], 'greendawn_business_form', $greendawn_poster );
update_post_meta( $greendawn_pages['gd-business-no-cf7'], 'greendawn_business_form', $greendawn_form_id );
update_post_meta( $greendawn_pages['gd-business-protected'], 'greendawn_business_intro', 'Protected Business content must stay private.' );
$greendawn_expected_video = array(
	'url'   => 'https://youtu.be/t96HrzEREM0',
	'embed' => 'https://www.youtube-nocookie.com/embed/t96HrzEREM0',
);
foreach ( array( 'https://youtu.be/t96HrzEREM0', 'https://www.youtube.com/watch?v=t96HrzEREM0&feature=share', 'https://www.youtube.com/embed/t96HrzEREM0?feature=oembed', 'https://www.youtube-nocookie.com/embed/t96HrzEREM0', 'https://youtube.com/shorts/t96HrzEREM0' ) as $greendawn_url ) {
	$greendawn_assert( $greendawn_module->video_value( array(), $greendawn_url ) === $greendawn_expected_video, 'Supported source video URLs must normalise to fixed trusted destinations.' );
	$greendawn_assert( true === $greendawn_module->validate_video( true, $greendawn_url ), 'The editor must accept supported YouTube URLs.' );
}
foreach ( array( '', 'javascript:alert(1)', 'http://youtu.be/t96HrzEREM0', 'https://www.youtube.com.untrusted.example/embed/t96HrzEREM0', 'https://youtu.be@untrusted.example/t96HrzEREM0', 'https://user@youtu.be/t96HrzEREM0', 'https://youtube.com:444/embed/t96HrzEREM0', 'https://youtube.com/watch?v[]=t96HrzEREM0', 'https://youtu.be/not-a-valid-id', 'https://vimeo.com/123456' ) as $greendawn_url ) {
	$greendawn_assert( array() === $greendawn_module->video_value( array(), $greendawn_url ), 'Arbitrary hosts, credentials, schemes, malformed IDs and extra ports must not create embeds.' );
	$greendawn_assert( '' === $greendawn_url || true !== $greendawn_module->validate_video( true, $greendawn_url ), 'Non-empty unsupported URLs must be rejected in the editor.' );
}
$greendawn_assert( get_post_meta( $greendawn_form_id, '_form', true ) === $greendawn_form_before && get_post_meta( $greendawn_form_id, '_mail', true ) === $greendawn_mail_before, 'Page assignment/rendering must not rewrite existing forms or recipients.' );
$greendawn_assert( (int) get_post_meta( get_page_by_path( 'enquire' )->ID, 'greendawn_enquire_form', true ) === $greendawn_form_id, 'Business Page edits must not change Enquire’s assignment.' );
WP_CLI::success( 'Business source defaults, complete testimonials, independent/shared form selection, trusted video URLs, saved clears and import preservation verified.' );
