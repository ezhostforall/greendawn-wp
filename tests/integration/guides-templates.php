<?php
/** Disposable template/discovery records. Never run on a hosted site. */
use Greendawn_Core\Modules\Guide_Content;
use Greendawn_Core\Modules\Guide_Discovery;
use Greendawn_Core\Modules\Guides;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'local' !== wp_get_environment_type() || 'http://localhost:8080' !== home_url() ) { return; }
function greendawn_templates_check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
$greendawn_mode = $args[0] ?? 'seed';
$greendawn_fixture = get_option( 'greendawn_templates_fixture', array() );
wp_set_current_user( get_user_by( 'login', 'admin' )->ID );
$greendawn_temp_ids = array();
try {
	set_error_handler( static function ( $severity, $message, $file, $line ) { if ( error_reporting() & $severity ) { throw new ErrorException( $message, 0, $severity, $file, $line ); } return false; } );
	if ( 'seed' === $greendawn_mode ) {
		foreach ( array( 'template-power' => 'Template power', 'template-operation' => 'Template operation', 'template-hidden' => 'Template hidden topic' ) as $slug => $name ) {
			$term = get_term_by( 'slug', $slug, Guides::TAXONOMY );
			$greendawn_fixture['topics'][ $slug ] = $term ? $term->term_id : wp_insert_term( $name, Guides::TAXONOMY, array( 'slug' => $slug ) )['term_id'];
		}
		if ( empty( $greendawn_fixture['image'] ) ) {
			$upload = wp_upload_bits( 'guide-template-engineers.webp', null, file_get_contents( get_template_directory() . '/assets/home/greendawn-engineers-electrical-distribution-1200.webp' ) );
			$image = wp_insert_attachment( array( 'post_title' => 'Template engineers', 'post_mime_type' => 'image/webp', 'post_status' => 'inherit' ), $upload['file'] );
			require_once ABSPATH . 'wp-admin/includes/image.php';
			wp_update_attachment_metadata( $image, wp_generate_attachment_metadata( $image, $upload['file'] ) );
			update_post_meta( $image, '_wp_attachment_image_alt', 'Greendawn engineers inspecting electrical distribution equipment' );
			$greendawn_fixture['image'] = $image;
		}
		for ( $index = 1; $index <= 15; ++$index ) {
			$id = $greendawn_fixture['guides'][ $index ] ?? 0;
			if ( ! $id || ! get_post( $id ) ) { $id = wp_insert_post( array( 'post_type' => Guides::TYPE, 'post_status' => 'draft', 'post_title' => 1 === $index ? 'Template site power guide' : sprintf( 'Template charging guide %02d', $index ), 'post_name' => 'template-guide-' . $index ) ); }
			wp_update_post( array( 'ID' => $id, 'post_status' => 'draft', 'post_password' => '' ) );
			$greendawn_fixture['guides'][ $index ] = $id;
			$topic = $greendawn_fixture['topics'][ $index > 12 ? 'template-hidden' : ( $index <= 8 ? 'template-power' : 'template-operation' ) ];
			wp_set_object_terms( $id, array( $topic ), Guides::TAXONOMY );
			$sections = array(
				array( 'heading' => 'Assess the existing supply', 'nav_label' => 'Existing supply', 'body' => 'An installation starts with the electricity supply and the way the building operates. Record the available capacity and the vehicle requirements before choosing equipment. Section-only marker: switchboardseeker.', 'list_style' => 'checklist', 'list_items' => array( array( 'text' => 'Gather the site address and supply details. List-only marker: parkingwindow.' ), array( 'text' => 'Record vehicle numbers, departure times and future plans.' ) ) ),
				array( 'heading' => 'Match charging to the operation', 'body' => 'Consider the charging window alongside building demand. Load management shares available capacity; it does not create extra energy.', 'table_caption' => 'Planning information', 'columns' => array( array( 'label' => 'Assessment' ), array( 'label' => 'Information' ), array( 'label' => 'Operation' ), array( 'label' => 'Next step' ) ), 'table_rows' => array( array( 'cells' => array( array( 'text' => 'Site load' ), array( 'text' => 'Table-only marker: phasebalance' ), array( 'text' => 'Vehicles and building' ), array( 'text' => 'Review information' ) ) ) ) ),
				array( 'heading' => 'Plan the next phase', 'body' => 'Bring the site information and your operating requirements to the project discussion. Allow for future demand, property permissions and any necessary supply work.', 'image' => $greendawn_fixture['image'], 'caption' => 'Company photography used in the disposable template test.' ),
			);
			greendawn_templates_check( true === Guide_Content::write( $id, array( 'intro' => 'Plan charging around the vehicles, the property and the available electrical capacity.', 'answer' => 'Assess usable site capacity and vehicle energy demand together before deciding on equipment.', 'primary_topic' => $topic, 'sections' => 1 === $index ? $sections : array( array( 'heading' => 'Planning notes', 'body' => 'A concise test record with literal load & supply and 50%_probe punctuation.' ) ), 'image' => 1 === $index ? $greendawn_fixture['image'] : 0, 'reviewed_on' => '', 'faqs' => 1 === $index ? array( array( 'question' => 'Can charging be planned in phases?', 'answer' => 'A phased approach should still support the vehicles. FAQ-only marker: departureenergy.' ) ) : array(), 'sources' => 1 === $index ? array( array( 'title' => 'Official guidance', 'url' => 'https://www.gov.uk/', 'publisher' => 'GOV.UK' ) ) : array(), 'cta_heading' => 'Discuss your site requirements', 'cta_copy' => 'Share the property and vehicle information you have.', 'cta_label' => 'Discuss this site' ) ), 'Guide seed could not save.' );
			wp_update_post( array( 'ID' => $id, 'post_status' => 13 === $index ? 'draft' : ( 14 === $index ? 'private' : 'publish' ), 'post_password' => 15 === $index ? 'template-password' : '' ) );
		}
		Guide_Content::write( $greendawn_fixture['guides'][1], array( 'related' => array( $greendawn_fixture['guides'][3], $greendawn_fixture['guides'][2], $greendawn_fixture['guides'][9] ) ) );
		$hub = array( 'heading' => 'EV charging guides for businesses', 'intro' => 'Clear answers to the decisions ahead, from site power to day-to-day operation.', 'image' => $greendawn_fixture['image'], 'topics' => array( $greendawn_fixture['topics']['template-power'], $greendawn_fixture['topics']['template-operation'] ), 'featured' => $greendawn_fixture['guides'][1], 'search_label' => 'Search guides', 'empty_heading' => 'No matching guides', 'empty_copy' => 'Try another term or clear the filters.', 'trust_heading' => 'Better informed before you commit', 'trust_copy' => 'This disposable page demonstrates the structured guidance templates.', 'enquiry_heading' => 'A project in mind?', 'enquiry_copy' => 'Bring us the site, vehicles and questions.', 'cta_heading' => 'Work out the next step', 'cta_copy' => 'Discuss your property and operating requirements.', 'cta_label' => 'Discuss this site' );
		$region = array( 'name' => 'Midlands', 'heading' => 'Charging that fits your Midlands site', 'intro' => 'Plan workplace and fleet charging around your vehicles, property and available power.', 'image' => $greendawn_fixture['image'], 'services' => array( array( 'title' => 'Workplace charging', 'copy' => 'Consider staff demand and available capacity together.', 'icon' => 'site' ), array( 'title' => 'Fleet charging', 'copy' => 'Work backwards from departure times and energy demand.', 'icon' => 'fleet' ), array( 'title' => 'Commercial properties', 'copy' => 'Coordinate permissions, access and responsibility.', 'icon' => 'property' ) ), 'considerations' => array( array( 'heading' => 'Understand the supply', 'copy' => 'Review building demand, connection limits and planned vehicle use.' ), array( 'heading' => 'Make the layout work', 'copy' => 'Consider cable routes, access and vehicle movements.' ), array( 'heading' => 'Agree how charging will run', 'copy' => 'Clarify permissions, metering and ongoing support.' ) ), 'places' => array( array( 'name' => 'Birmingham' ), array( 'name' => 'Coventry' ), array( 'name' => 'Wolverhampton' ), array( 'name' => 'Nottingham' ) ), 'faqs' => array( array( 'question' => 'Can staff and fleet charging be combined?', 'answer' => 'Assess combined demand and the operation before choosing equipment.' ) ), 'guides' => array( $greendawn_fixture['guides'][1], $greendawn_fixture['guides'][2], $greendawn_fixture['guides'][9] ), 'cta_heading' => 'Plan your Midlands project', 'cta_copy' => 'Tell us where the site is and what the vehicles need.', 'cta_label' => 'Discuss this site' );
		foreach ( array( 'guides' => array( 'hub', $hub ), 'template-region' => array( 'region', $region ), 'template-region-minimal' => array( 'region', array_merge( $region, array( 'image' => 0, 'services' => array(), 'places' => array(), 'faqs' => array(), 'guides' => array(), 'cta_heading' => '', 'cta_label' => '' ) ) ), 'template-hub-minimal' => array( 'hub', array_merge( $hub, array( 'image' => 0, 'featured' => 0, 'enquiry_heading' => '', 'enquiry_copy' => '', 'trust_heading' => '', 'trust_copy' => '', 'cta_heading' => '' ) ) ), 'template-hub-protected' => array( 'hub', $hub ) ) as $slug => $record ) {
			$page = get_page_by_path( $slug );
			$id = $page ? $page->ID : wp_insert_post( array( 'post_type' => 'page', 'post_title' => 'Template ' . $slug, 'post_name' => $slug, 'post_status' => 'draft' ) );
			update_post_meta( $id, '_wp_page_template', Guides::TEMPLATES[ $record[0] ] );
			greendawn_templates_check( true === Guide_Content::write( $id, $record[1] ), 'Structured Page seed could not save.' );
			wp_update_post( array( 'ID' => $id, 'post_status' => 'publish', 'post_password' => 'template-hub-protected' === $slug ? 'template-password' : '' ) );
			$greendawn_fixture['pages'][ $slug ] = $id;
		}
		update_option( 'greendawn_templates_fixture', $greendawn_fixture, false );
		Guide_Content::write( $greendawn_fixture['pages']['template-region'], array( 'proof_heading' => 'Published project experience', 'proof_copy' => 'This disposable test extract demonstrates a supported evidence section, without a local project claim.', 'proof_image' => $greendawn_fixture['image'], 'proof_url' => home_url( '/gallery/' ) ) );
	}
	if ( 'without-core' === $greendawn_mode ) {
		greendawn_templates_check( array() === greendawn_reading_page_data( $greendawn_fixture['pages']['guides'] ), 'Missing Core must return empty Page data.' );
		greendawn_templates_check( array() === greendawn_guide_template_data( $greendawn_fixture['guides'][1] ), 'Missing Core must return empty Guide data.' );
	} else {
		foreach ( array( 'switchboardseeker', 'parkingwindow', 'phasebalance', 'departureenergy' ) as $term ) {
			$result = Guide_Discovery::search( $term );
			greendawn_templates_check( array( $greendawn_fixture['guides'][1] ) === wp_list_pluck( $result->posts, 'ID' ), 'SCF-only text not found: ' . $term );
		}
		greendawn_templates_check( 0 === Guide_Discovery::search( '', 'template-hidden' )->found_posts, 'Non-public records leaked into discovery.' );
		greendawn_templates_check( 0 === Guide_Discovery::search( '', 'unknown-topic' )->found_posts, 'Unknown topic returned unrelated records.' );
		greendawn_templates_check( 0 === Guide_Discovery::search( "' OR 1=1 --" )->found_posts && 0 === Guide_Discovery::search( 'doesnotexist%' )->found_posts, 'Search SQL wildcards/injection were interpreted.' );
		greendawn_templates_check( 7 === Guide_Discovery::search( '50%_probe', 'template-power' )->found_posts, 'Literal wildcard punctuation did not match safely.' );
		$topics = Guide_Discovery::topics( array( $greendawn_fixture['topics']['template-operation'], $greendawn_fixture['topics']['template-power'] ) );
		greendawn_templates_check( $topics[0]['slug'] === 'template-operation' && $topics[0]['count'] === 4 && $topics[1]['count'] === 8 && ! in_array( 'template-hidden', array_column( $topics, 'slug' ), true ), 'Topic order/counts include hidden records.' );
		greendawn_templates_check( 0 === Guide_Discovery::search( 'switchboardseeker', 'template-operation' )->found_posts, 'Search and topic did not intersect.' );
		greendawn_templates_check( '' === get_post( $greendawn_fixture['guides'][1] )->post_content, 'Projection became a duplicate native body.' );
		// A temporary result set checks pagination limits, stable ordering and duplicate exclusion.
		for ( $i = 0; $i < 27; ++$i ) {
			$id = wp_insert_post( array( 'post_type' => Guides::TYPE, 'post_title' => sprintf( 'Pagination %02d', $i ), 'post_status' => 'draft' ) );
			$greendawn_temp_ids[] = $id;
			global $wpdb;
			$wpdb->update( $wpdb->posts, array( 'post_status' => 'publish' ), array( 'ID' => $id ) );
			clean_post_cache( $id );
			update_post_meta( $id, Guide_Content::SEARCH_META, 'Paginationprobe' );
		}
		$first = Guide_Discovery::search( 'paginationprobe' ); $second = Guide_Discovery::search( 'paginationprobe', '', 2 );
		greendawn_templates_check( 27 === $first->found_posts && 24 === count( $first->posts ) && 3 === count( $second->posts ) && ! array_intersect( wp_list_pluck( $first->posts, 'ID' ), wp_list_pluck( $second->posts, 'ID' ) ), 'Pagination lost, duplicated or overran results.' );
	}
} catch ( Throwable $error ) { $greendawn_error = $error->getMessage(); }
finally { restore_error_handler(); foreach ( $greendawn_temp_ids as $id ) { wp_delete_post( $id, true ); } }
if ( ! empty( $greendawn_error ) ) { WP_CLI::error( $greendawn_error ); }
WP_CLI::success( 'Guide templates and discovery verified: ' . $greendawn_mode );
