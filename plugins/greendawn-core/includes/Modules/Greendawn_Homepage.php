<?php
/**
 * Field registration and editing controls for the Greendawn homepage.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/** Registers the client homepage field group. */
final class Greendawn_Homepage implements Module_Interface {

	private const TEMPLATE    = 'templates/greendawn-homepage.php';
	private const PUBLIC_SITE = 'https://greendawn.co.uk';

	/**
	 * The translated field definitions for this request.
	 *
	 * @var array|null
	 */
	private ?array $fields = null;

	/** Hooks the homepage editor. */
	public function register(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'greendawn_home_field_value', array( $this, 'field_value' ), 10, 2 );
		add_filter( 'use_block_editor_for_post', array( $this, 'use_block_editor' ), 10, 2 );
		add_action( 'add_meta_boxes_page', array( $this, 'hide_body_editor' ) );
	}

	/**
	 * Disables the block editor when the structured fields are available.
	 *
	 * @param bool     $use_editor Existing block editor decision.
	 * @param \WP_Post $post       Current post.
	 */
	public function use_block_editor( bool $use_editor, \WP_Post $post ): bool {
		return $use_editor && ! $this->uses_fields( $post );
	}

	/**
	 * Hides the classic body editor for the branded homepage.
	 *
	 * @param \WP_Post $post Current page.
	 */
	public function hide_body_editor( \WP_Post $post ): void {
		if ( $this->uses_fields( $post ) ) {
			remove_post_type_support( 'page', 'editor' );
		}
	}

	/**
	 * Determines whether to use the structured fields.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function uses_fields( \WP_Post $post ): bool {
		return 'page' === $post->post_type
			&& self::TEMPLATE === get_page_template_slug( $post )
			&& function_exists( 'acf_add_local_field_group' );
	}

	/** Returns the editorial schema shared by the editor and frontend. */
	private function fields(): array {
		if ( null !== $this->fields ) {
			return $this->fields;
		}

		$copy = array(
			'eyebrow'                       => array( __( 'Eyebrow', 'greendawn-core' ), __( 'Commercial EV charging infrastructure', 'greendawn-core' ), 60 ),
			'coverage'                      => array( __( 'Coverage', 'greendawn-core' ), __( 'England · Scotland · Wales', 'greendawn-core' ), 60 ),
			'headline'                      => array( __( 'Headline first line', 'greendawn-core' ), __( 'EV charging.', 'greendawn-core' ), 28 ),
			'headline_accent'               => array( __( 'Headline accent line', 'greendawn-core' ), __( 'Handled.', 'greendawn-core' ), 16 ),
			'lead'                          => array( __( 'Introduction', 'greendawn-core' ), __( 'Greendawn plans, installs and supports commercial EV charging around your vehicles, users and available power—from a single site to a coordinated estate programme.', 'greendawn-core' ), 280, 'textarea' ),
			'projects_label'                => array( __( 'Projects link label', 'greendawn-core' ), __( 'Explore our projects', 'greendawn-core' ), 45 ),
			'projects_url'                  => array( __( 'Projects destination', 'greendawn-core' ), self::PUBLIC_SITE . '/gallery/', 0, 'url' ),
			'assurance_requirements'        => array( __( 'Site requirements assurance', 'greendawn-core' ), __( 'Site requirements first', 'greendawn-core' ), 70 ),
			'assurance_equipment'           => array( __( 'Equipment assurance', 'greendawn-core' ), __( 'Equipment chosen for the operation', 'greendawn-core' ), 70 ),
			'assurance_support'             => array( __( 'Support assurance', 'greendawn-core' ), __( 'Agreed support after installation', 'greendawn-core' ), 70 ),
			'consult_label'                 => array( __( 'Consult stage label', 'greendawn-core' ), __( 'Consult', 'greendawn-core' ), 30 ),
			'consult_caption'               => array( __( 'Consult stage caption', 'greendawn-core' ), __( 'Understand your requirement', 'greendawn-core' ), 75 ),
			'design_label'                  => array( __( 'Design stage label', 'greendawn-core' ), __( 'Design', 'greendawn-core' ), 30 ),
			'design_caption'                => array( __( 'Design stage caption', 'greendawn-core' ), __( 'Resolve power, layout and scope', 'greendawn-core' ), 75 ),
			'install_label'                 => array( __( 'Install stage label', 'greendawn-core' ), __( 'Install', 'greendawn-core' ), 30 ),
			'install_caption'               => array( __( 'Install stage caption', 'greendawn-core' ), __( 'Coordinate works and commissioning', 'greendawn-core' ), 75 ),
			'manage_label'                  => array( __( 'Manage stage label', 'greendawn-core' ), __( 'Manage', 'greendawn-core' ), 30 ),
			'manage_caption'                => array( __( 'Manage stage caption', 'greendawn-core' ), __( 'Support the charging operation', 'greendawn-core' ), 75 ),
			'trust_label'                   => array( __( 'Commercial delivery label', 'greendawn-core' ), __( 'Commercial delivery', 'greendawn-core' ), 45 ),
			'trust_intro'                   => array( __( 'Commercial delivery summary', 'greendawn-core' ), __( 'Experience across dealership, fleet, workplace and destination sites.', 'greendawn-core' ), 160, 'textarea' ),
			'footprint_label'               => array( __( 'Footprint label', 'greendawn-core' ), __( 'GB', 'greendawn-core' ), 20 ),
			'footprint_caption'             => array( __( 'Footprint caption', 'greendawn-core' ), __( 'England, Scotland and Wales', 'greendawn-core' ), 75 ),
			'equipment_label'               => array( __( 'Equipment label', 'greendawn-core' ), __( 'AC–DC', 'greendawn-core' ), 20 ),
			'equipment_caption'             => array( __( 'Equipment caption', 'greendawn-core' ), __( 'Equipment selected around the requirement', 'greendawn-core' ), 75 ),
			'problem_eyebrow'               => array( __( 'Problem eyebrow', 'greendawn-core' ), __( 'Why Greendawn', 'greendawn-core' ), 60 ),
			'problem_title'                 => array( __( 'Problem heading', 'greendawn-core' ), __( 'One charging project. Coordinated from the start.', 'greendawn-core' ), 100 ),
			'problem_intro'                 => array( __( 'Problem introduction', 'greendawn-core' ), __( 'A charging project brings together electrical work, groundworks, equipment, software and support. Decisions in one area affect the others.', 'greendawn-core' ), 250, 'textarea' ),
			'problem_summary'               => array( __( 'Coordination summary', 'greendawn-core' ), __( 'Greendawn coordinates the specialists and dependencies, so you have a clear route through the project—not separate conversations to piece together yourself.', 'greendawn-core' ), 260, 'textarea' ),
			'problem_model_label'           => array( __( 'Model label', 'greendawn-core' ), __( 'What needs to come together', 'greendawn-core' ), 65 ),
			'problem_fragment_requirements' => array( __( 'Requirements fragment', 'greendawn-core' ), __( 'Operational requirements', 'greendawn-core' ), 48 ),
			'problem_fragment_power'        => array( __( 'Power fragment', 'greendawn-core' ), __( 'Available power', 'greendawn-core' ), 48 ),
			'problem_fragment_groundworks'  => array( __( 'Groundworks fragment', 'greendawn-core' ), __( 'Groundworks', 'greendawn-core' ), 48 ),
			'problem_fragment_equipment'    => array( __( 'Equipment fragment', 'greendawn-core' ), __( 'Charging equipment', 'greendawn-core' ), 48 ),
			'problem_fragment_software'     => array( __( 'Software fragment', 'greendawn-core' ), __( 'Software & payments', 'greendawn-core' ), 48 ),
			'problem_fragment_support'      => array( __( 'Support fragment', 'greendawn-core' ), __( 'Ongoing support', 'greendawn-core' ), 48 ),
			'problem_answer_label'          => array( __( 'Approach label', 'greendawn-core' ), __( 'The Greendawn approach', 'greendawn-core' ), 60 ),
			'problem_answer_title'          => array( __( 'Approach headline', 'greendawn-core' ), __( 'One coordinated delivery relationship.', 'greendawn-core' ), 80 ),
			'problem_answer_copy'           => array( __( 'Approach description', 'greendawn-core' ), __( 'Clear responsibilities from the first site discussion through installation, commissioning and agreed support.', 'greendawn-core' ), 200, 'textarea' ),
			'system_eyebrow'                => array( __( 'System eyebrow', 'greendawn-core' ), __( 'Start with the site', 'greendawn-core' ), 60 ),
			'system_title'                  => array( __( 'System heading', 'greendawn-core' ), __( 'The charger is only one part of the system.', 'greendawn-core' ), 100 ),
			'system_intro'                  => array( __( 'System introduction', 'greendawn-core' ), __( 'Start with what the charging needs to achieve. Then plan the power, equipment and operating arrangements around it.', 'greendawn-core' ), 240, 'textarea' ),
			'system_power_detail'           => array( __( 'Power layer detail', 'greendawn-core' ), __( 'Capacity before equipment', 'greendawn-core' ), 55 ),
			'system_power_title'            => array( __( 'Power layer title', 'greendawn-core' ), __( 'Power & infrastructure', 'greendawn-core' ), 65 ),
			'system_power_copy'             => array( __( 'Power layer description', 'greendawn-core' ), __( 'Assess charging demand, available power, cable routes and groundworks. Identify any connection work needed to support the design.', 'greendawn-core' ), 240, 'textarea' ),
			'system_equipment_detail'       => array( __( 'Equipment layer detail', 'greendawn-core' ), __( 'Chosen for the operation', 'greendawn-core' ), 55 ),
			'system_equipment_title'        => array( __( 'Equipment layer title', 'greendawn-core' ), __( 'Charging equipment', 'greendawn-core' ), 65 ),
			'system_equipment_copy'         => array( __( 'Equipment layer description', 'greendawn-core' ), __( 'Select AC or DC equipment around the vehicles, parking time and energy required—not the headline power rating alone.', 'greendawn-core' ), 240, 'textarea' ),
			'system_software_detail'        => array( __( 'Software layer detail', 'greendawn-core' ), __( 'Access, payments and visibility', 'greendawn-core' ), 55 ),
			'system_software_title'         => array( __( 'Software layer title', 'greendawn-core' ), __( 'Software & operation', 'greendawn-core' ), 65 ),
			'system_software_copy'          => array( __( 'Software layer description', 'greendawn-core' ), __( 'Plan how people use the chargers, how tariffs are managed and what your team needs to monitor. Set out the software and support arrangements.', 'greendawn-core' ), 240, 'textarea' ),
			'system_technology_label'       => array( __( 'Technology guide label', 'greendawn-core' ), __( 'Technology', 'greendawn-core' ), 45 ),
			'system_technology_title'       => array( __( 'Technology guide heading', 'greendawn-core' ), __( 'AC or DC follows the operational need.', 'greendawn-core' ), 80 ),
			'system_technology_copy'        => array( __( 'Technology guide summary', 'greendawn-core' ), __( 'Vehicles, energy demand and the time available to charge shape the equipment choice.', 'greendawn-core' ), 180, 'textarea' ),
			'system_technology_link'        => array( __( 'Technology guide link text', 'greendawn-core' ), __( 'Understand AC and DC charging', 'greendawn-core' ), 65 ),
			'system_technology_url'         => array( __( 'Technology guide URL', 'greendawn-core' ), self::PUBLIC_SITE . '/guides/ac-vs-dc-ev-charging/', 0, 'url' ),
			'system_site_power_label'       => array( __( 'Site power guide label', 'greendawn-core' ), __( 'Site power', 'greendawn-core' ), 45 ),
			'system_site_power_title'       => array( __( 'Site power guide heading', 'greendawn-core' ), __( 'Make informed decisions about site power.', 'greendawn-core' ), 80 ),
			'system_site_power_copy'        => array( __( 'Site power guide summary', 'greendawn-core' ), __( 'Assess demand and load management before deciding whether additional supply is needed.', 'greendawn-core' ), 180, 'textarea' ),
			'system_site_power_link'        => array( __( 'Site power guide link text', 'greendawn-core' ), __( 'Explore site power planning', 'greendawn-core' ), 65 ),
			'system_site_power_url'         => array( __( 'Site power guide URL', 'greendawn-core' ), self::PUBLIC_SITE . '/guides/can-my-building-handle-ev-charging/', 0, 'url' ),
			'process_title'                 => array( __( 'Process heading', 'greendawn-core' ), __( 'A clear route from requirement to operation.', 'greendawn-core' ), 100 ),
			'process_intro'                 => array( __( 'Process introduction', 'greendawn-core' ), __( 'You do not need a finished specification to start. Each stage establishes the decisions and information needed for the next.', 'greendawn-core' ), 220, 'textarea' ),
			'consult_description'           => array( __( 'Consult description', 'greendawn-core' ), __( 'Tell us about your vehicles, users, sites and objectives. We establish the requirement and recommend the next step.', 'greendawn-core' ), 240, 'textarea' ),
			'design_description'            => array( __( 'Design description', 'greendawn-core' ), __( 'Assess power, layout and delivery constraints. Where a technical survey is needed, agree its scope before developing the design and proposal.', 'greendawn-core' ), 240, 'textarea' ),
			'install_description'           => array( __( 'Install description', 'greendawn-core' ), __( 'Coordinate the agreed electrical and civil works, equipment, testing and commissioning. Plan access and handover with your team.', 'greendawn-core' ), 240, 'textarea' ),
			'manage_description'            => array( __( 'Manage description', 'greendawn-core' ), __( 'Put the agreed software, monitoring, maintenance and fault-support arrangements in place, with clear ongoing responsibilities.', 'greendawn-core' ), 240, 'textarea' ),
			'featured_eyebrow'              => array( __( 'Project eyebrow', 'greendawn-core' ), __( 'Commercial project experience', 'greendawn-core' ), 60 ),
			'featured_title'                => array( __( 'Project heading', 'greendawn-core' ), __( 'A dealership programme coordinated site by site.', 'greendawn-core' ), 100 ),
			'featured_caption'              => array( __( 'Project photograph caption', 'greendawn-core' ), __( 'Johnsons Cars · Dealership charging programme', 'greendawn-core' ), 100 ),
			'featured_lead'                 => array( __( 'Project introduction', 'greendawn-core' ), __( 'Johnsons Cars needed charging across an estate with different electrical constraints, layouts and manufacturer requirements.', 'greendawn-core' ), 250, 'textarea' ),
			'featured_sites_value'          => array( __( 'Sites figure', 'greendawn-core' ), __( '136', 'greendawn-core' ), 20 ),
			'featured_sites_label'          => array( __( 'Sites label', 'greendawn-core' ), __( 'sites in the programme', 'greendawn-core' ), 50 ),
			'featured_chargepoints_value'   => array( __( 'Chargepoints figure', 'greendawn-core' ), __( '454', 'greendawn-core' ), 20 ),
			'featured_chargepoints_label'   => array( __( 'Chargepoints label', 'greendawn-core' ), __( 'chargepoints', 'greendawn-core' ), 50 ),
			'featured_brands_value'         => array( __( 'Brands figure', 'greendawn-core' ), __( '11', 'greendawn-core' ), 20 ),
			'featured_brands_label'         => array( __( 'Brands label', 'greendawn-core' ), __( 'automotive brands', 'greendawn-core' ), 50 ),
			'featured_review_note'          => array( __( 'Programme review note', 'greendawn-core' ), __( 'Programme figures reviewed September 2026.', 'greendawn-core' ), 120 ),
			'featured_copy'                 => array( __( 'Project description', 'greendawn-core' ), __( 'Greendawn coordinated the programme around those site-specific needs, bringing together infrastructure, charging equipment and installation.', 'greendawn-core' ), 280, 'textarea' ),
			'featured_link'                 => array( __( 'Project link text', 'greendawn-core' ), __( 'Explore our project experience', 'greendawn-core' ), 65 ),
			'survey_eyebrow'                => array( __( 'Survey eyebrow', 'greendawn-core' ), __( 'Technical site surveys', 'greendawn-core' ), 60 ),
			'survey_title'                  => array( __( 'Survey heading', 'greendawn-core' ), __( 'Replace assumptions with a buildable plan.', 'greendawn-core' ), 100 ),
			'survey_intro'                  => array( __( 'Survey introduction', 'greendawn-core' ), __( 'Start with a conversation. If a paid technical survey is the right next step, we agree what needs checking and what you will receive before booking.', 'greendawn-core' ), 280, 'textarea' ),
			'survey_price_label'            => array( __( 'Survey price label', 'greendawn-core' ), __( 'Site surveys from', 'greendawn-core' ), 45 ),
			'survey_price'                  => array( __( 'Survey starting price', 'greendawn-core' ), __( '£200', 'greendawn-core' ), 20 ),
			'survey_tax_label'              => array( __( 'Survey tax label', 'greendawn-core' ), __( '+ VAT', 'greendawn-core' ), 30 ),
			'survey_price_scope'            => array( __( 'Survey price scope', 'greendawn-core' ), __( 'The scope and final price depend on the site and requirement. Both are confirmed before booking, including any further assessments or third-party costs that may apply.', 'greendawn-core' ), 320, 'textarea' ),
			'survey_outcomes_title'         => array( __( 'Survey outcomes heading', 'greendawn-core' ), __( 'What the survey helps establish', 'greendawn-core' ), 80 ),
			'survey_outcome_1'              => array( __( 'Survey outcome 1', 'greendawn-core' ), __( 'Charging demand and available site capacity', 'greendawn-core' ), 140 ),
			'survey_outcome_2'              => array( __( 'Survey outcome 2', 'greendawn-core' ), __( 'Charger locations, cable routes and access constraints', 'greendawn-core' ), 140 ),
			'survey_outcome_3'              => array( __( 'Survey outcome 3', 'greendawn-core' ), __( 'Electrical, civil and network dependencies', 'greendawn-core' ), 140 ),
			'survey_outcome_4'              => array( __( 'Survey outcome 4', 'greendawn-core' ), __( 'The information needed for the design and proposal', 'greendawn-core' ), 140 ),
			'survey_summary'                => array( __( 'Survey scope summary', 'greendawn-core' ), __( 'A survey is recommended only where it adds useful technical certainty. Its scope sets out what will be checked, what you will receive and which questions may need further assessment.', 'greendawn-core' ), 340, 'textarea' ),
			'survey_cta_label'              => array( __( 'Survey enquiry link text', 'greendawn-core' ), __( 'Discuss a site survey', 'greendawn-core' ), 60 ),
			'survey_guide_label'            => array( __( 'Survey guide link text', 'greendawn-core' ), __( 'What a technical survey should establish', 'greendawn-core' ), 80 ),
			'survey_guide_url'              => array( __( 'Survey guide URL', 'greendawn-core' ), self::PUBLIC_SITE . '/guides/greendawn-ev-charging-survey-pre-installation-guide/', 0, 'url' ),
		);

		$fields = array();
		foreach ( $copy as $name => $settings ) {
			if ( 'featured_eyebrow' === $name ) {
				$fields['featured_image'] = array(
					'key'            => 'field_greendawn_home_featured_image',
					'name'           => 'greendawn_home_featured_image',
					'label'          => __( 'Project photograph', 'greendawn-core' ),
					'instructions'   => __( 'Select the Johnsons dealership photograph from the Media Library. Upload the supplied reference image first and set its alt text.', 'greendawn-core' ),
					'type'           => 'image',
					'return_format'  => 'id',
					'default_upload' => '2026/11/johnsons-dealership-ev-charging-1200.webp',
				);
			}
			$fields[ $name ] = array(
				'key'           => 'field_greendawn_home_' . $name,
				'name'          => 'greendawn_home_' . $name,
				'label'         => $settings[0],
				'default_value' => $settings[1],
				'maxlength'     => $settings[2],
				'type'          => $settings[3] ?? 'text',
			);
		}
		$fields = array(
			'hero_image' => array(
				'key'            => 'field_greendawn_home_hero_image',
				'name'           => 'greendawn_home_hero_image',
				'label'          => __( 'Hero photograph', 'greendawn-core' ),
				'instructions'   => __( 'Defaults to the uploaded engineers photograph if present. Set alt text for the chosen photograph in the Media Library.', 'greendawn-core' ),
				'type'           => 'image',
				'return_format'  => 'id',
				'default_upload' => '2026/11/greendawn-engineers-electrical-distribution-1600.webp',
			),
		) + $fields;

		$this->fields = $fields;
		return $this->fields;
	}

	/**
	 * Gets a homepage field by its registered key, including before its first save.
	 *
	 * @param mixed  $fallback Value from the theme when the field is unavailable.
	 * @param string $name     Short field name.
	 * @return mixed
	 */
	public function field_value( $fallback, string $name ) {
		$field = $this->fields()[ $name ] ?? null;
		if ( ! $field ) {
			return $fallback;
		}

		$post_id = get_the_ID();
		if ( function_exists( 'get_field' ) ) {
			return get_field( $field['key'], $post_id );
		}

		return $this->load_default( get_post_meta( $post_id, $field['name'], true ), $post_id, $field );
	}

	/**
	 * Applies defaults to unset fields and existing fields that were saved blank.
	 *
	 * @param mixed      $value   Stored field value.
	 * @param int|string $post_id Object identifier passed by SCF.
	 * @param array      $field   Field definition.
	 * @return mixed
	 */
	public function load_default( $value, $post_id, array $field ) {
		if ( 'image' === $field['type'] ) {
			$image_id = absint( $value );
			if ( $image_id && wp_attachment_is_image( $image_id ) ) {
				return $image_id;
			}

			$default_upload = $field['default_upload'] ?? '';
			if ( '' === $default_upload ) {
				return 0;
			}
			$uploads = wp_get_upload_dir();
			return attachment_url_to_postid( trailingslashit( $uploads['baseurl'] ) . $default_upload );
		}

		if ( null === $value || false === $value || '' === $value || ( 'url' === $field['type'] && '#' === $value ) ) {
			return $field['default_value'];
		}

		return $value;
	}

	/** Registers labelled fields and defaults for the homepage sections. */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		$tabs   = array(
			'hero_image'      => __( 'Hero', 'greendawn-core' ),
			'trust_label'     => __( 'Trust', 'greendawn-core' ),
			'problem_eyebrow' => __( 'Problem', 'greendawn-core' ),
			'system_eyebrow'  => __( 'System', 'greendawn-core' ),
			'process_title'   => __( 'Process', 'greendawn-core' ),
			'featured_image'  => __( 'Featured project', 'greendawn-core' ),
			'survey_eyebrow'  => __( 'Surveys', 'greendawn-core' ),
		);
		$fields = array();
		foreach ( $this->fields() as $name => $field ) {
			if ( isset( $tabs[ $name ] ) ) {
				$fields[] = array(
					'key'   => 'field_greendawn_home_tab_' . $name,
					'label' => $tabs[ $name ],
					'type'  => 'tab',
				);
			}
			$fields[] = $field;
			add_filter( 'acf/load_value/key=' . $field['key'], array( $this, 'load_default' ), 10, 3 );
		}

		acf_add_local_field_group(
			array(
				'key'         => 'group_greendawn_homepage',
				'title'       => __( 'Greendawn homepage', 'greendawn-core' ),
				'description' => __( 'Empty fields use the Greendawn defaults. Set the enquiry destination under Appearance → Customise → Header.', 'greendawn-core' ),
				'location'    => array(
					array(
						array(
							'param'    => 'page_template',
							'operator' => '==',
							'value'    => self::TEMPLATE,
						),
					),
				),
				'fields'      => $fields,
			)
		);
	}
}
