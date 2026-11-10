<?php
/**
 * Disposable Aftercare fields, FAQ repeater persistence and form ownership checks.
 *
 * @package Greendawn
 */

if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'Aftercare fixtures are restricted to disposable WordPress.' );
}
$greendawn_assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
};
$greendawn_module = new \Greendawn_Core\Modules\Greendawn_Aftercare();
$greendawn_schema = $greendawn_module->fields();
$greendawn_pages  = array();
foreach ( array( 'aftercare', 'gd-aftercare-edited', 'gd-aftercare-empty', 'gd-aftercare-invalid-form', 'gd-aftercare-no-core', 'gd-aftercare-no-cf7', 'gd-aftercare-protected', 'gd-aftercare-blank-answer' ) as $greendawn_slug ) {
	$greendawn_id = wp_insert_post(
		array(
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'post_name'     => $greendawn_slug,
			'post_title'    => $greendawn_slug,
			'post_content'  => '<h1>Do not append this legacy layout</h1><p>Preserved Aftercare import backup.</p>',
			'post_password' => 'gd-aftercare-protected' === $greendawn_slug ? 'secret' : '',
		)
	);
	update_post_meta( $greendawn_id, '_wp_page_template', 'templates/greendawn-aftercare.php' );
	$greendawn_pages[ $greendawn_slug ] = $greendawn_id;
}
$greendawn_default = $greendawn_pages['aftercare'];
$greendawn_assert( 'Aftercare' === get_field( 'field_greendawn_aftercare_accent', $greendawn_default ), 'SCF must prefill Aftercare copy without saving.' );
$greendawn_assert( ! metadata_exists( 'post', $greendawn_default, 'greendawn_aftercare_accent' ), 'Default reads must not write metadata.' );
$greendawn_defaults = $greendawn_module->faq_rows( $greendawn_default );
$greendawn_scf_rows = get_field( 'field_greendawn_aftercare_faqs', $greendawn_default );
$greendawn_assert( is_array( $greendawn_scf_rows ) && 3 === count( $greendawn_scf_rows ), 'SCF must prefill all three FAQ rows before the first save.' );
foreach ( $greendawn_defaults as $greendawn_index => $greendawn_row ) {
	$greendawn_assert( $greendawn_scf_rows[ $greendawn_index ]['question'] === $greendawn_row['question'], 'SCF repeater defaults must format their subfield names correctly.' );
	$greendawn_assert( preg_replace( '/\s+/', '', wp_strip_all_tags( $greendawn_scf_rows[ $greendawn_index ]['answer'] ) ) === preg_replace( '/\s+/', '', wp_strip_all_tags( $greendawn_row['answer'] ) ), 'All source FAQ paragraphs and list items must remain in SCF defaults.' );
}
$greendawn_assert( ! metadata_exists( 'post', $greendawn_default, 'greendawn_aftercare_faqs' ), 'Displaying repeater defaults must not save rows.' );
$greendawn_posts = get_field( 'field_greendawn_aftercare_posts', $greendawn_default );
$greendawn_assert( 10 === count( $greendawn_posts ), 'All ten source article destinations must remain.' );
$greendawn_hero = (int) get_field( 'field_greendawn_aftercare_hero_image', $greendawn_default );
$greendawn_assert( $greendawn_hero > 0, 'The hero must resolve the genuine local electrical-connection attachment.' );
$greendawn_assert( empty( get_field( 'field_greendawn_aftercare_form', $greendawn_default ) ), 'No page may inherit another form or Customiser selection.' );
$greendawn_assert( false === use_block_editor_for_post( get_post( $greendawn_default ) ), 'Aftercare must disable layout editing.' );
$greendawn_assert( use_block_editor_for_post( get_page_by_path( 'policy-example' ) ), 'Ordinary pages keep their editor.' );
$greendawn_group = acf_get_local_field_group( 'group_greendawn_aftercare' );
$greendawn_assert( 'templates/greendawn-aftercare.php' === $greendawn_group['location'][0][0]['value'], 'Fields must be scoped to the fixed Aftercare template.' );
foreach ( $greendawn_schema as $greendawn_field ) {
	$greendawn_assert( ! in_array( $greendawn_field['type'], array( 'flexible_content', 'color_picker' ), true ), 'Editors must not gain layout or design controls.' );
}
$greendawn_form = WPCF7_ContactForm::get_template();
$greendawn_form->set_title( 'Aftercare fixture form' );
$greendawn_form->set_properties( array( 'form' => file_get_contents( '/tmp/cf7-project-enquiry-form.html' ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Copied local fixture.
$greendawn_form_id = $greendawn_form->save();
update_field( 'field_greendawn_aftercare_form', $greendawn_form_id, $greendawn_default );
// The source introduction photograph must be mapped explicitly if it is not imported.
update_field( 'field_greendawn_aftercare_story_image', $greendawn_hero, $greendawn_default );
$greendawn_edited = $greendawn_pages['gd-aftercare-edited'];
update_field( 'field_greendawn_aftercare_heading', 'Care for our charging network.', $greendawn_edited );
update_field( 'field_greendawn_aftercare_accent', '', $greendawn_edited );
update_field( 'field_greendawn_aftercare_intro', '', $greendawn_edited );
update_field( 'field_greendawn_aftercare_story', '<p class="bad" style="color:red" onclick="alert(1)">Reviewed <strong>care content</strong> <a href="javascript:alert(1)">Unsafe link</a></p><iframe src="https://example.com/"></iframe>', $greendawn_edited );
$greendawn_rows = array(
	array(
		'question' => 'Our last question first?',
		'answer'   => '<p>Edited answer with <strong>emphasis</strong>.</p><ul><li>First saved point</li><li>Second saved point</li></ul>',
	),
	$greendawn_defaults[0],
);
update_field( 'field_greendawn_aftercare_faqs', $greendawn_rows, $greendawn_edited );
$greendawn_assert( $greendawn_module->faq_rows( $greendawn_edited ) === $greendawn_rows, 'Saved repeater rows must round-trip via native metadata without SCF.' );
$greendawn_assert( get_field( 'field_greendawn_aftercare_faqs', $greendawn_edited )[0]['question'] === $greendawn_rows[0]['question'], 'SCF must show reordered saved rows.' );
$greendawn_assert( 3 === count( $greendawn_module->faq_rows( $greendawn_default ) ), 'Saved edits must not overwrite another Page’s default rows.' );
$greendawn_hidden     = wp_insert_post(
	array(
		'post_type'   => 'post',
		'post_status' => 'private',
		'post_title'  => 'Hidden Aftercare article',
	)
);
$greendawn_passworded = wp_insert_post(
	array(
		'post_type'     => 'post',
		'post_status'   => 'publish',
		'post_password' => 'secret',
		'post_title'    => 'Protected Aftercare article',
	)
);
update_post_meta( $greendawn_edited, 'greendawn_aftercare_posts', array( $greendawn_posts[9], $greendawn_hidden, $greendawn_passworded, $greendawn_default, $greendawn_posts[0], $greendawn_posts[9] ) );
$greendawn_assert( str_contains( get_post( $greendawn_edited )->post_content, 'Preserved Aftercare import backup.' ), 'Structured editing must preserve the imported body.' );
$greendawn_empty = $greendawn_pages['gd-aftercare-empty'];
set_post_thumbnail( $greendawn_empty, $greendawn_hero );
foreach ( array( 'hero_image', 'story_image', 'form' ) as $greendawn_name ) {
	update_post_meta( $greendawn_empty, 'greendawn_aftercare_' . $greendawn_name, 0 );
}
update_field( 'field_greendawn_aftercare_faqs', array(), $greendawn_empty );
$greendawn_assert( array() === $greendawn_module->faq_rows( $greendawn_empty ), 'Cleared saved FAQ rows must not restore defaults.' );
$greendawn_assert( empty( get_field( 'field_greendawn_aftercare_faqs', $greendawn_empty ) ), 'SCF must retain cleared saved repeaters.' );
$greendawn_assert( empty( get_field( 'field_greendawn_aftercare_hero_image', $greendawn_empty ) ), 'A cleared hero must not restore its featured image.' );
update_post_meta( $greendawn_empty, 'greendawn_aftercare_posts', array() );
update_post_meta( $greendawn_pages['gd-aftercare-invalid-form'], 'greendawn_aftercare_form', $greendawn_posts[0] );
update_post_meta( $greendawn_pages['gd-aftercare-no-cf7'], 'greendawn_aftercare_form', $greendawn_form_id );
update_post_meta( $greendawn_pages['gd-aftercare-protected'], 'greendawn_aftercare_story', '<p>Protected Aftercare content must stay private.</p>' );
update_field(
	'field_greendawn_aftercare_faqs',
	array(
		array(
			'question' => 'An intentionally blank answer?',
			'answer'   => '',
		),
	),
	$greendawn_pages['gd-aftercare-blank-answer']
);
WP_CLI::success( 'Aftercare complete defaults, SCF/native FAQ round-trip, saved clears, independent form, editor scope and import preservation verified.' );
