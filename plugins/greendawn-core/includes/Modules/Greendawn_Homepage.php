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

	private const TEMPLATE = 'templates/greendawn-homepage.php';

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
			'projects_url'                  => array( __( 'Projects destination', 'greendawn-core' ), home_url( '/gallery/' ), 0, 'url' ),
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
			'system_technology_url'         => array( __( 'Technology guide URL', 'greendawn-core' ), home_url( '/guides/ac-vs-dc-ev-charging/' ), 0, 'url' ),
			'system_site_power_label'       => array( __( 'Site power guide label', 'greendawn-core' ), __( 'Site power', 'greendawn-core' ), 45 ),
			'system_site_power_title'       => array( __( 'Site power guide heading', 'greendawn-core' ), __( 'Make informed decisions about site power.', 'greendawn-core' ), 80 ),
			'system_site_power_copy'        => array( __( 'Site power guide summary', 'greendawn-core' ), __( 'Assess demand and load management before deciding whether additional supply is needed.', 'greendawn-core' ), 180, 'textarea' ),
			'system_site_power_link'        => array( __( 'Site power guide link text', 'greendawn-core' ), __( 'Explore site power planning', 'greendawn-core' ), 65 ),
			'system_site_power_url'         => array( __( 'Site power guide URL', 'greendawn-core' ), home_url( '/guides/can-my-building-handle-ev-charging/' ), 0, 'url' ),
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
			'survey_guide_url'              => array( __( 'Survey guide URL', 'greendawn-core' ), home_url( '/guides/greendawn-ev-charging-survey-pre-installation-guide/' ), 0, 'url' ),
			'solutions_eyebrow'             => array( __( 'Services eyebrow', 'greendawn-core' ), __( 'Who we help', 'greendawn-core' ), 60 ),
			'solutions_title'               => array( __( 'Services heading', 'greendawn-core' ), __( 'Charging built around your operation.', 'greendawn-core' ), 100 ),
			'solutions_intro'               => array( __( 'Services introduction', 'greendawn-core' ), __( 'Different sites need charging to do different jobs. Start with the users, vehicles, parking time and available power.', 'greendawn-core' ), 240, 'textarea' ),
			'solution_fleet_title'          => array( __( 'Fleet & depot charging: title', 'greendawn-core' ), __( 'Fleet & depot charging', 'greendawn-core' ), 65 ),
			'solution_fleet_copy'           => array( __( 'Fleet & depot charging: description', 'greendawn-core' ), __( 'Plan around daily energy demand, charging windows and when vehicles need to leave.', 'greendawn-core' ), 240, 'textarea' ),
			'solution_fleet_link'           => array( __( 'Fleet & depot charging: link text', 'greendawn-core' ), __( 'Explore fleet charging', 'greendawn-core' ), 70 ),
			'solution_fleet_url'            => array( __( 'Fleet & depot charging: destination', 'greendawn-core' ), home_url( '/fleet-charging-solutions/' ), 0, 'url' ),
			'solution_workplace_title'      => array( __( 'Workplace charging: title', 'greendawn-core' ), __( 'Workplace charging', 'greendawn-core' ), 65 ),
			'solution_workplace_copy'       => array( __( 'Workplace charging: description', 'greendawn-core' ), __( 'Match provision to staff, company vehicles and visitors, with capacity and access considered from the start.', 'greendawn-core' ), 240, 'textarea' ),
			'solution_workplace_link'       => array( __( 'Workplace charging: link text', 'greendawn-core' ), __( 'Explore workplace charging', 'greendawn-core' ), 70 ),
			'solution_workplace_url'        => array( __( 'Workplace charging: destination', 'greendawn-core' ), home_url( '/workplace-charging/' ), 0, 'url' ),
			'solution_public_title'         => array( __( 'Public & destination charging: title', 'greendawn-core' ), __( 'Public & destination charging', 'greendawn-core' ), 65 ),
			'solution_public_copy'          => array( __( 'Public & destination charging: description', 'greendawn-core' ), __( 'Plan charging around the visitor experience, parking time, payment and ongoing support.', 'greendawn-core' ), 240, 'textarea' ),
			'solution_public_link'          => array( __( 'Public & destination charging: link text', 'greendawn-core' ), __( 'Explore public and destination charging', 'greendawn-core' ), 70 ),
			'solution_public_url'           => array( __( 'Public & destination charging: destination', 'greendawn-core' ), home_url( '/public-charging/' ), 0, 'url' ),
			'solution_multi_site_title'     => array( __( 'Multi-site programmes: title', 'greendawn-core' ), __( 'Multi-site programmes', 'greendawn-core' ), 65 ),
			'solution_multi_site_copy'      => array( __( 'Multi-site programmes: description', 'greendawn-core' ), __( 'Coordinate shared requirements while accounting for the different constraints at each location.', 'greendawn-core' ), 240, 'textarea' ),
			'solution_multi_site_link'      => array( __( 'Multi-site programmes: link text', 'greendawn-core' ), __( 'Explore commercial project delivery', 'greendawn-core' ), 70 ),
			'solution_multi_site_url'       => array( __( 'Multi-site programmes: destination', 'greendawn-core' ), home_url( '/business-ev-charging-installations/' ), 0, 'url' ),
			'solution_dealership_title'     => array( __( 'Dealerships & motor retail: title', 'greendawn-core' ), __( 'Dealerships & motor retail', 'greendawn-core' ), 65 ),
			'solution_dealership_copy'      => array( __( 'Dealerships & motor retail: description', 'greendawn-core' ), __( 'Plan for vehicle preparation, workshops, demonstrators, handovers and customer charging.', 'greendawn-core' ), 240, 'textarea' ),
			'solution_dealership_link'      => array( __( 'Dealerships & motor retail: link text', 'greendawn-core' ), __( 'Read the dealership planning guide', 'greendawn-core' ), 70 ),
			'solution_dealership_url'       => array( __( 'Dealerships & motor retail: destination', 'greendawn-core' ), home_url( '/guides/ev-charging-for-car-dealership/' ), 0, 'url' ),
			'aftercare_eyebrow'             => array( __( 'Aftercare eyebrow', 'greendawn-core' ), __( 'Aftercare', 'greendawn-core' ), 60 ),
			'aftercare_title'               => array( __( 'Aftercare heading', 'greendawn-core' ), __( 'EV infrastructure needs to keep working.', 'greendawn-core' ), 100 ),
			'aftercare_intro'               => array( __( 'Aftercare introduction', 'greendawn-core' ), __( 'Installation is the start of the charging operation, not the end of the relationship.', 'greendawn-core' ), 240, 'textarea' ),
			'aftercare_copy'                => array( __( 'Aftercare scope', 'greendawn-core' ), __( 'With an agreed aftercare package, Greendawn coordinates monitoring, maintenance and fault support. Your proposal makes clear what is included and how to get help.', 'greendawn-core' ), 340, 'textarea' ),
			'aftercare_cta_label'           => array( __( 'Aftercare link text', 'greendawn-core' ), __( 'Explore aftercare', 'greendawn-core' ), 60 ),
			'aftercare_cta_url'             => array( __( 'Aftercare destination', 'greendawn-core' ), home_url( '/aftercare/' ), 0, 'url' ),
			'aftercare_support_prompt'      => array( __( 'Customer support prompt', 'greendawn-core' ), __( 'Need help with an existing charger?', 'greendawn-core' ), 160 ),
			'aftercare_support_label'       => array( __( 'Customer support link text', 'greendawn-core' ), __( 'Contact customer support', 'greendawn-core' ), 80 ),
			'aftercare_support_url'         => array( __( 'Customer support destination', 'greendawn-core' ), home_url( '/customer-care/' ), 0, 'url' ),
			'aftercare_monitoring_title'    => array( __( 'Monitoring service title', 'greendawn-core' ), __( 'Monitoring & reporting', 'greendawn-core' ), 80 ),
			'aftercare_testing_title'       => array( __( 'Testing service title', 'greendawn-core' ), __( 'Inspection & testing', 'greendawn-core' ), 80 ),
			'aftercare_maintenance_title'   => array( __( 'Maintenance service title', 'greendawn-core' ), __( 'Maintenance & fault support', 'greendawn-core' ), 80 ),
			'aftercare_users_title'         => array( __( 'User support service title', 'greendawn-core' ), __( 'Driver & user support', 'greendawn-core' ), 80 ),
			'aftercare_statement'           => array( __( 'Aftercare closing statement', 'greendawn-core' ), __( 'Support that continues beyond installation.', 'greendawn-core' ), 140 ),
			'proof_eyebrow'                 => array( __( 'Supporting proof eyebrow', 'greendawn-core' ), __( 'Fleet programme experience', 'greendawn-core' ), 60 ),
			'proof_title'                   => array( __( 'Supporting proof heading', 'greendawn-core' ), __( 'Different locations. A coordinated programme.', 'greendawn-core' ), 100 ),
			'proof_link'                    => array( __( 'Gallery link text', 'greendawn-core' ), __( 'Explore our project experience', 'greendawn-core' ), 70 ),
			'proof_caption'                 => array( __( 'Project photograph caption', 'greendawn-core' ), __( 'Fleet programme experience', 'greendawn-core' ), 100 ),
			'proof_project_title'           => array( __( 'Supporting project name', 'greendawn-core' ), __( 'The Salvation Army', 'greendawn-core' ), 80 ),
			'proof_result'                  => array( __( 'Project result and figures', 'greendawn-core' ), __( '50 sites · 71 chargepoints', 'greendawn-core' ), 100 ),
			'proof_copy'                    => array( __( 'Supporting project description', 'greendawn-core' ), __( 'Fleet charging across a varied estate, with different site requirements brought together within one coordinated programme.', 'greendawn-core' ), 280, 'textarea' ),
			'insights_eyebrow'              => array( __( 'Insights eyebrow', 'greendawn-core' ), __( 'Commercial EV charging guidance', 'greendawn-core' ), 70 ),
			'insights_title'                => array( __( 'Insights heading', 'greendawn-core' ), __( 'Make better-informed charging decisions.', 'greendawn-core' ), 100 ),
			'insights_link'                 => array( __( 'Guidance index link text', 'greendawn-core' ), __( 'Browse commercial charging guidance', 'greendawn-core' ), 80 ),
			'insights_url'                  => array( __( 'Guidance index destination', 'greendawn-core' ), home_url( '/blog/' ), 0, 'url' ),
			'insight_power_label'           => array( __( 'Power guide: label', 'greendawn-core' ), __( 'Power & capacity', 'greendawn-core' ), 60 ),
			'insight_power_title'           => array( __( 'Power guide: title', 'greendawn-core' ), __( 'Understanding load management', 'greendawn-core' ), 100 ),
			'insight_power_url'             => array( __( 'Power guide: destination', 'greendawn-core' ), home_url( '/guides/load-balancing-explained/' ), 0, 'url' ),
			'insight_survey_label'          => array( __( 'Survey guide: label', 'greendawn-core' ), __( 'Project planning', 'greendawn-core' ), 60 ),
			'insight_survey_title'          => array( __( 'Survey guide: title', 'greendawn-core' ), __( 'What a technical survey should establish', 'greendawn-core' ), 100 ),
			'insight_survey_url'            => array( __( 'Survey guide: destination', 'greendawn-core' ), home_url( '/guides/greendawn-ev-charging-survey-pre-installation-guide/' ), 0, 'url' ),
			'insight_fleet_label'           => array( __( 'Fleet guide: label', 'greendawn-core' ), __( 'Fleet planning', 'greendawn-core' ), 60 ),
			'insight_fleet_title'           => array( __( 'Fleet guide: title', 'greendawn-core' ), __( 'Planning the transition to an electric fleet', 'greendawn-core' ), 100 ),
			'insight_fleet_url'             => array( __( 'Fleet guide: destination', 'greendawn-core' ), home_url( '/guides/transitioning-to-electric-fleets/' ), 0, 'url' ),
			'closing_eyebrow'               => array( __( 'Final enquiry eyebrow', 'greendawn-core' ), __( 'Start with the requirement', 'greendawn-core' ), 70 ),
			'closing_title'                 => array( __( 'Final enquiry heading', 'greendawn-core' ), __( 'Tell us what your site needs.', 'greendawn-core' ), 100 ),
			'closing_intro'                 => array( __( 'Final enquiry introduction', 'greendawn-core' ), __( 'A rough outline is enough to start. Tell us who needs charging, what you want it to achieve and what you know about the site.', 'greendawn-core' ), 280, 'textarea' ),
			'closing_copy'                  => array( __( 'Final enquiry next step', 'greendawn-core' ), __( 'We’ll help identify the next step, including whether a technical survey is appropriate.', 'greendawn-core' ), 240, 'textarea' ),
			'closing_cta_label'             => array( __( 'Enquiry button text', 'greendawn-core' ), __( 'Discuss your site', 'greendawn-core' ), 70 ),
			'closing_phone_prompt'          => array( __( 'Telephone prompt', 'greendawn-core' ), __( 'Prefer to speak first?', 'greendawn-core' ), 100 ),
			'closing_phone'                 => array( __( 'Telephone number', 'greendawn-core' ), (string) get_theme_mod( 'greendawn_footer_phone', Greendawn_Footer::defaults()['phone'] ), 40 ),
		);

		$solution_images = array(
			'solution_fleet_title'      => array( 'solution_fleet_image', __( 'Fleet & depot charging: photograph', 'greendawn-core' ), 'commercial-ev-fleet-charging-bays-1200.webp' ),
			'solution_workplace_title'  => array( 'solution_workplace_image', __( 'Workplace charging: photograph', 'greendawn-core' ), 'commercial-ev-workplace-charging-1200.webp' ),
			'solution_public_title'     => array( 'solution_public_image', __( 'Public & destination charging: photograph', 'greendawn-core' ), 'commercial-ev-destination-hospitality-1200.webp' ),
			'solution_multi_site_title' => array( 'solution_multi_site_image', __( 'Multi-site programmes: photograph', 'greendawn-core' ), 'commercial-ev-multi-site-charging-768.webp' ),
			'solution_dealership_title' => array( 'solution_dealership_image', __( 'Dealerships & motor retail: photograph', 'greendawn-core' ), 'commercial-ev-dealership-charging-1200.webp' ),
		);

		$insight_images = array(
			'insight_power_label'  => array( 'insight_power_image', __( 'Power guide: photograph', 'greendawn-core' ), 'commercial-ev-electrical-connection-1200.webp' ),
			'insight_survey_label' => array( 'insight_survey_image', __( 'Survey guide: photograph', 'greendawn-core' ), 'commercial-ev-site-survey-1024.webp' ),
			'insight_fleet_label'  => array( 'insight_fleet_image', __( 'Fleet guide: photograph', 'greendawn-core' ), 'commercial-ev-fleet-charging-bays-1200.webp' ),
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
			if ( 'proof_eyebrow' === $name ) {
				$fields['proof_image'] = array(
					'key'            => 'field_greendawn_home_proof_image',
					'name'           => 'greendawn_home_proof_image',
					'label'          => __( 'Supporting project photograph', 'greendawn-core' ),
					'instructions'   => __( 'Upload the supplied Salvation Army photograph and select it here. Set its alt text in the Media Library.', 'greendawn-core' ),
					'type'           => 'image',
					'return_format'  => 'id',
					'default_upload' => '2026/11/salvation-army-ev-charging-666.webp',
				);
			}
			if ( isset( $solution_images[ $name ] ) ) {
				$image      = $solution_images[ $name ];
				$image_name = $image[0];

				$fields[ $image_name ] = array(
					'key'            => 'field_greendawn_home_' . $image_name,
					'name'           => 'greendawn_home_' . $image_name,
					'label'          => $image[1],
					'instructions'   => __( 'Upload the supplied service photograph and select it here. The homepage preview is decorative; its caption follows the service title.', 'greendawn-core' ),
					'type'           => 'image',
					'return_format'  => 'id',
					'default_upload' => '2026/11/' . $image[2],
				);
			}
			if ( isset( $insight_images[ $name ] ) ) {
				$image      = $insight_images[ $name ];
				$image_name = $image[0];

				$fields[ $image_name ] = array(
					'key'            => 'field_greendawn_home_' . $image_name,
					'name'           => 'greendawn_home_' . $image_name,
					'label'          => $image[1],
					'instructions'   => __( 'Upload the supplied guide photograph and select it here. Set its alt text in the Media Library.', 'greendawn-core' ),
					'type'           => 'image',
					'return_format'  => 'id',
					'default_upload' => '2026/11/' . $image[2],
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
			if ( 'closing_cta_label' === $name ) {
				$fields[ $name ]['instructions'] = __( 'Uses the shared enquiry destination under Appearance → Customise → Header.', 'greendawn-core' );
			}
			if ( 'closing_phone' === $name ) {
				$fields[ $name ]['instructions'] = __( 'Defaults to the sitewide Footer telephone number. Save a different number only for a homepage-specific override. Use an optional leading + and country code.', 'greendawn-core' );
			}
			if ( 'proof_link' === $name ) {
				$fields[ $name ]['instructions'] = __( 'Uses the Projects destination in the Hero tab.', 'greendawn-core' );
			}
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
			$uploads  = wp_get_upload_dir();
			$image_id = attachment_url_to_postid( trailingslashit( $uploads['baseurl'] ) . $default_upload );
			if ( $image_id ) {
				return $image_id;
			}

			// WordPress scales photographs larger than its big-image threshold on upload.
			$scaled_upload = preg_replace( '/(\.[^\/.]+)$/', '-scaled$1', $default_upload );
			return attachment_url_to_postid( trailingslashit( $uploads['baseurl'] ) . $scaled_upload );
		}

		if ( null === $value || false === $value || '' === $value || ( 'url' === $field['type'] && '#' === $value ) ) {
			$value = $field['default_value'];
		}

		// Existing imported links should follow the current WordPress environment.
		if ( 'url' === $field['type'] && is_string( $value ) ) {
			$parts = wp_parse_url( $value );
			if ( is_array( $parts ) && in_array( strtolower( $parts['host'] ?? '' ), array( 'greendawn.co.uk', 'www.greendawn.co.uk' ), true ) ) {
				$path  = $parts['path'] ?? '/';
				$path .= isset( $parts['query'] ) ? '?' . $parts['query'] : '';
				$path .= isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '';
				return home_url( $path );
			}
		}

		return $value;
	}

	/** Registers labelled fields and defaults for the homepage sections. */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		$tabs   = array(
			'hero_image'        => __( 'Hero', 'greendawn-core' ),
			'trust_label'       => __( 'Trust', 'greendawn-core' ),
			'problem_eyebrow'   => __( 'Problem', 'greendawn-core' ),
			'system_eyebrow'    => __( 'System', 'greendawn-core' ),
			'process_title'     => __( 'Process', 'greendawn-core' ),
			'featured_image'    => __( 'Featured project', 'greendawn-core' ),
			'survey_eyebrow'    => __( 'Surveys', 'greendawn-core' ),
			'solutions_eyebrow' => __( 'Services', 'greendawn-core' ),
			'aftercare_eyebrow' => __( 'Aftercare', 'greendawn-core' ),
			'proof_image'       => __( 'Supporting proof', 'greendawn-core' ),
			'insights_eyebrow'  => __( 'Insights', 'greendawn-core' ),
			'closing_eyebrow'   => __( 'Final enquiry', 'greendawn-core' ),
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
