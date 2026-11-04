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

	/** Hooks the homepage editor. */
	public function register(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
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

	/** Registers labelled fields for the first homepage sections. */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		$hero = array(
			'eyebrow'                => array( __( 'Eyebrow', 'greendawn-core' ), 60 ),
			'headline'               => array( __( 'Headline first line', 'greendawn-core' ), 28 ),
			'headline_accent'        => array( __( 'Headline accent line', 'greendawn-core' ), 16 ),
			'lead'                   => array( __( 'Introduction', 'greendawn-core' ), 280, 'textarea' ),
			'projects_label'         => array( __( 'Projects link label', 'greendawn-core' ), 45 ),
			'projects_url'           => array( __( 'Projects destination', 'greendawn-core' ), 0, 'url' ),
			'assurance_requirements' => array( __( 'Site requirements assurance', 'greendawn-core' ), 70 ),
			'assurance_equipment'    => array( __( 'Equipment assurance', 'greendawn-core' ), 70 ),
			'assurance_support'      => array( __( 'Support assurance', 'greendawn-core' ), 70 ),
		);

		$fields = array();
		foreach ( $hero as $name => $settings ) {
			$fields[] = array(
				'key'       => 'field_greendawn_home_' . $name,
				'name'      => 'greendawn_home_' . $name,
				'label'     => $settings[0],
				'type'      => $settings[2] ?? 'text',
				'maxlength' => $settings[1],
			);
		}
		$fields[] = array(
			'key'           => 'field_greendawn_home_hero_image',
			'name'          => 'greendawn_home_hero_image',
			'label'         => __( 'Hero photograph', 'greendawn-core' ),
			'instructions'  => __( 'Leave empty to use the supplied engineers photograph. Add alt text to any replacement in the Media Library.', 'greendawn-core' ),
			'type'          => 'image',
			'return_format' => 'id',
		);
		$fields[] = array(
			'key'       => 'field_greendawn_home_trust_intro',
			'name'      => 'greendawn_home_trust_intro',
			'label'     => __( 'Commercial delivery summary', 'greendawn-core' ),
			'type'      => 'textarea',
			'maxlength' => 160,
		);

		acf_add_local_field_group(
			array(
				'key'         => 'group_greendawn_homepage',
				'title'       => __( 'Greendawn homepage', 'greendawn-core' ),
				'description' => __( 'Blank copy fields use the supplied Greendawn wording. Set the enquiry destination under Appearance → Customise → Header.', 'greendawn-core' ),
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
