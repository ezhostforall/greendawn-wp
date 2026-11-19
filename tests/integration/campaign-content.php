<?php
/**
 * Migration and SCF storage checks in the disposable WordPress only.
 *
 * @package Greendawn
 */

use Greendawn_Core\Modules\Campaigns;
use Greendawn_Core\Modules\Campaign_Content;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'local' !== wp_get_environment_type() || 'http://localhost:8080' !== home_url() ) {
	return;
}

/**
 * Fail after the fixture's finally block has restored the campaign.
 *
 * @param bool   $condition Expected outcome.
 * @param string $message   Failure explanation.
 * @throws RuntimeException When the expectation fails.
 */
function greendawn_campaign_check( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( esc_html( $message ) );
	}
}

$greendawn_post     = get_page_by_path( Campaigns::WORKPLACE_SLUG, OBJECT, Campaigns::TYPE );
$greendawn_id       = $greendawn_post->ID;
$greendawn_snapshot = get_post_meta( $greendawn_id );
$greendawn_featured = get_option( Campaigns::FEATURED_OPTION );
$greendawn_other    = wp_insert_post(
	array(
		'post_type'   => Campaigns::TYPE,
		'post_status' => 'publish',
		'post_title'  => 'Unrelated migration fixture',
	)
);
$greendawn_original = require GREENDAWN_CORE_DIR . 'includes/campaign-workplace-charging.php';
$greendawn_error    = '';

try {
	foreach ( array_keys( $greendawn_snapshot ) as $greendawn_key ) {
		if ( str_contains( $greendawn_key, 'greendawn_campaign_' ) ) {
			delete_post_meta( $greendawn_id, $greendawn_key );
		}
	}
	Campaign_Content::write( $greendawn_id, $greendawn_original );
	Campaign_Content::write(
		$greendawn_id,
		array(
			'hero_image'       => '123',
			'planning'         => '20270210',
			'panel_hint'       => '',
			'guide_title'      => 'An edited campaign heading',
			'faq_question'     => 'Our edited grant question?',
			'faq_answer'       => 'Keep our edited grant answer.',
			'voucher_question' => 'Our edited voucher question?',
			'voucher_answer'   => 'Keep our edited voucher answer.',
		)
	);
	Campaign_Content::upgrade( $greendawn_id );
	$greendawn_content = Campaigns::campaign( $greendawn_id );
	greendawn_campaign_check( 3 === count( $greendawn_content['metrics'] ) && 4 === count( $greendawn_content['eligibility'] ), 'New sections were not populated.' );
	greendawn_campaign_check( 'An edited campaign heading' === $greendawn_content['guide_title'] && '' === $greendawn_content['panel_hint'], 'Migration overwrote edited or deliberately empty content.' );
	greendawn_campaign_check( '123' === $greendawn_content['hero_image'] && '20270210' === $greendawn_content['planning'], 'Migration changed the image or dates.' );
	greendawn_campaign_check( 5 === count( $greendawn_content['faqs'] ) && 'Keep our edited voucher answer.' === $greendawn_content['faqs'][1]['answer'], 'Migration lost an edited legacy FAQ.' );
	greendawn_campaign_check( str_contains( $greendawn_content['faqs'][3]['question'], '10 February' ), 'Repeater date tokens were not expanded.' );
	greendawn_campaign_check( get_option( Campaigns::FEATURED_OPTION ) === $greendawn_featured && home_url( '/workplace-charging-grant/' ) === get_permalink( $greendawn_id ), 'Migration changed the featured selection or URL.' );
	Campaign_Content::upgrade( $greendawn_other );
	greendawn_campaign_check( ! metadata_exists( 'post', $greendawn_other, 'greendawn_campaign_metrics' ), 'Migration changed an unrelated campaign.' );
	Campaign_Content::write(
		$greendawn_other,
		array(
			'faq_question' => 'An unrelated question?',
			'faq_answer'   => 'An unrelated answer.',
		)
	);
	Campaign_Content::upgrade_legacy_faqs( $greendawn_other );
	$greendawn_other_content = Campaigns::content( $greendawn_other );
	greendawn_campaign_check( 'An unrelated answer.' === $greendawn_other_content['faqs'][0]['answer'] && ! metadata_exists( 'post', $greendawn_other, 'greendawn_campaign_metrics' ), 'Legacy FAQ editing added unrelated campaign content.' );
	Campaign_Content::write( $greendawn_other, array( 'faqs' => array() ) );
	Campaign_Content::upgrade_legacy_faqs( $greendawn_other );
	greendawn_campaign_check( array() === Campaigns::content( $greendawn_other )['faqs'], 'Cleared FAQs were repopulated from legacy content.' );


	if ( function_exists( 'update_field' ) ) {
		$greendawn_field = get_field_object( 'greendawn_campaign_metrics', $greendawn_id );
		greendawn_campaign_check( 'repeater' === $greendawn_field['type'] && 3 === count( get_field( 'greendawn_campaign_metrics', $greendawn_id ) ), 'SCF cannot edit seeded repeater data.' );
		update_field(
			'greendawn_campaign_metrics',
			array(
				array(
					'prefix' => 'Edited',
					'value'  => '£450',
					'copy'   => "An editor's change.",
				),
			),
			$greendawn_id
		);
	} else {
		Campaign_Content::write(
			$greendawn_id,
			array(
				'metrics' => array(
					array(
						'prefix' => 'Edited',
						'value'  => '£450',
						'copy'   => "An editor's change.",
					),
				),
			)
		);
	}
	Campaign_Content::upgrade( $greendawn_id );
	$greendawn_content = Campaigns::campaign( $greendawn_id );
	greendawn_campaign_check( 1 === count( $greendawn_content['metrics'] ) && "An editor's change." === $greendawn_content['metrics'][0]['copy'], 'Repeated migration reset an edited repeater.' );
	Campaign_Content::write(
		$greendawn_id,
		array(
			'faqs' => array(
				array(
					'question'   => '<script>unsafe</script>',
					'answer'     => 'Plain text.',
					'visibility' => 'always',
				),
			),
		)
	);
	ob_start();
	get_template_part( 'template-parts/campaign/content', null, Campaigns::campaign( $greendawn_id ) );
	$greendawn_html = ob_get_clean();
	greendawn_campaign_check( ! str_contains( $greendawn_html, '<script>unsafe</script>' ) && str_contains( $greendawn_html, '&lt;script&gt;unsafe&lt;/script&gt;' ), 'Campaign fields were not escaped.' );

	delete_post_meta( $greendawn_id, '_greendawn_campaign_content_version' );
	delete_post_meta( $greendawn_id, 'greendawn_campaign_metrics' );
	wp_set_current_user( 0 );
	Campaign_Content::maybe_upgrade();
	greendawn_campaign_check( ! metadata_exists( 'post', $greendawn_id, 'greendawn_campaign_metrics' ), 'Unauthorised request ran the content upgrade.' );
} catch ( Throwable $greendawn_exception ) {
	$greendawn_error = $greendawn_exception->getMessage();
} finally {
	foreach ( array_keys( get_post_meta( $greendawn_id ) ) as $greendawn_key ) {
		if ( str_contains( $greendawn_key, 'greendawn_campaign_' ) ) {
			delete_post_meta( $greendawn_id, $greendawn_key );
		}
	}
	foreach ( $greendawn_snapshot as $greendawn_key => $greendawn_values ) {
		if ( str_contains( $greendawn_key, 'greendawn_campaign_' ) ) {
			foreach ( $greendawn_values as $greendawn_value ) {
				add_post_meta( $greendawn_id, $greendawn_key, wp_slash( maybe_unserialize( $greendawn_value ) ) );
			}
		}
	}
	wp_delete_post( $greendawn_other, true );
	Campaigns::refresh_public_cache();
}
if ( $greendawn_error ) {
	WP_CLI::error( $greendawn_error );
}
WP_CLI::success( 'Campaign migration, editor compatibility, preservation and output escaping verified.' );
