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
			'eyebrow'                => array( __( 'Eyebrow', 'greendawn-core' ), __( 'Commercial EV charging infrastructure', 'greendawn-core' ), 60 ),
			'coverage'               => array( __( 'Coverage', 'greendawn-core' ), __( 'England · Scotland · Wales', 'greendawn-core' ), 60 ),
			'headline'               => array( __( 'Headline first line', 'greendawn-core' ), __( 'EV charging.', 'greendawn-core' ), 28 ),
			'headline_accent'        => array( __( 'Headline accent line', 'greendawn-core' ), __( 'Handled.', 'greendawn-core' ), 16 ),
			'lead'                   => array( __( 'Introduction', 'greendawn-core' ), __( 'Greendawn plans, installs and supports commercial EV charging around your vehicles, users and available power—from a single site to a coordinated estate programme.', 'greendawn-core' ), 280, 'textarea' ),
			'projects_label'         => array( __( 'Projects link label', 'greendawn-core' ), __( 'Explore our projects', 'greendawn-core' ), 45 ),
			'projects_url'           => array( __( 'Projects destination', 'greendawn-core' ), 'https://greendawn.co.uk/gallery/', 0, 'url' ),
			'assurance_requirements' => array( __( 'Site requirements assurance', 'greendawn-core' ), __( 'Site requirements first', 'greendawn-core' ), 70 ),
			'assurance_equipment'    => array( __( 'Equipment assurance', 'greendawn-core' ), __( 'Equipment chosen for the operation', 'greendawn-core' ), 70 ),
			'assurance_support'      => array( __( 'Support assurance', 'greendawn-core' ), __( 'Agreed support after installation', 'greendawn-core' ), 70 ),
			'consult_label'          => array( __( 'Consult stage label', 'greendawn-core' ), __( 'Consult', 'greendawn-core' ), 30 ),
			'consult_caption'        => array( __( 'Consult stage caption', 'greendawn-core' ), __( 'Understand your requirement', 'greendawn-core' ), 75 ),
			'design_label'           => array( __( 'Design stage label', 'greendawn-core' ), __( 'Design', 'greendawn-core' ), 30 ),
			'design_caption'         => array( __( 'Design stage caption', 'greendawn-core' ), __( 'Resolve power, layout and scope', 'greendawn-core' ), 75 ),
			'install_label'          => array( __( 'Install stage label', 'greendawn-core' ), __( 'Install', 'greendawn-core' ), 30 ),
			'install_caption'        => array( __( 'Install stage caption', 'greendawn-core' ), __( 'Coordinate works and commissioning', 'greendawn-core' ), 75 ),
			'manage_label'           => array( __( 'Manage stage label', 'greendawn-core' ), __( 'Manage', 'greendawn-core' ), 30 ),
			'manage_caption'         => array( __( 'Manage stage caption', 'greendawn-core' ), __( 'Support the charging operation', 'greendawn-core' ), 75 ),
			'trust_label'            => array( __( 'Commercial delivery label', 'greendawn-core' ), __( 'Commercial delivery', 'greendawn-core' ), 45 ),
			'trust_intro'            => array( __( 'Commercial delivery summary', 'greendawn-core' ), __( 'Experience across dealership, fleet, workplace and destination sites.', 'greendawn-core' ), 160, 'textarea' ),
			'footprint_label'        => array( __( 'Footprint label', 'greendawn-core' ), __( 'GB', 'greendawn-core' ), 20 ),
			'footprint_caption'      => array( __( 'Footprint caption', 'greendawn-core' ), __( 'England, Scotland and Wales', 'greendawn-core' ), 75 ),
			'equipment_label'        => array( __( 'Equipment label', 'greendawn-core' ), __( 'AC–DC', 'greendawn-core' ), 20 ),
			'equipment_caption'      => array( __( 'Equipment caption', 'greendawn-core' ), __( 'Equipment selected around the requirement', 'greendawn-core' ), 75 ),
		);

		$fields = array();
		foreach ( $copy as $name => $settings ) {
			$fields[ $name ] = array(
				'key'           => 'field_greendawn_home_' . $name,
				'name'          => 'greendawn_home_' . $name,
				'label'         => $settings[0],
				'default_value' => $settings[1],
				'maxlength'     => $settings[2],
				'type'          => $settings[3] ?? 'text',
			);
		}
		$fields['hero_image'] = array(
			'key'           => 'field_greendawn_home_hero_image',
			'name'          => 'greendawn_home_hero_image',
			'label'         => __( 'Hero photograph', 'greendawn-core' ),
			'instructions'  => __( 'Defaults to the uploaded engineers photograph if present. Set alt text for the chosen photograph in the Media Library.', 'greendawn-core' ),
			'type'          => 'image',
			'return_format' => 'id',
		);

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

			$uploads = wp_get_upload_dir();
			return attachment_url_to_postid( trailingslashit( $uploads['baseurl'] ) . '2026/11/greendawn-engineers-electrical-distribution-1600.webp' );
		}

		if ( null === $value || false === $value || '' === $value || ( 'url' === $field['type'] && '#' === $value ) ) {
			return $field['default_value'];
		}

		return $value;
	}

	/** Registers labelled fields and defaults for the first homepage sections. */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		$fields = $this->fields();
		foreach ( $fields as $field ) {
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
				'fields'      => array_values( $fields ),
			)
		);
	}
}
