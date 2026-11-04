<?php
/**
 * Structured content for the opt-in business homepage template.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the homepage fields and scopes the editing interface to that template.
 */
final class Business_Homepage implements Module_Interface {

	private const TEMPLATE = 'templates/business-homepage.php';

	/**
	 * Hooks the field group and editing controls into WordPress.
	 */
	public function register(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'use_block_editor_for_post', array( $this, 'use_block_editor' ), 10, 2 );
		add_action( 'add_meta_boxes_page', array( $this, 'hide_body_editor' ) );
	}

	/**
	 * Keeps the existing editor decision for other pages and posts.
	 *
	 * @param bool     $use_editor Existing block editor decision.
	 * @param \WP_Post $post Current post.
	 */
	public function use_block_editor( bool $use_editor, \WP_Post $post ): bool {
		return $use_editor && ! $this->uses_fields( $post );
	}

	/**
	 * Hides the classic body editor when the field group is available.
	 *
	 * @param \WP_Post $post Current page.
	 */
	public function hide_body_editor( \WP_Post $post ): void {
		if ( $this->uses_fields( $post ) ) {
			remove_post_type_support( 'page', 'editor' );
		}
	}

	/**
	 * Checks if this page uses the structured homepage editing form.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function uses_fields( \WP_Post $post ): bool {
		return 'page' === $post->post_type
			&& self::TEMPLATE === get_page_template_slug( $post )
			&& function_exists( 'acf_add_local_field_group' );
	}

	/**
	 * Registers the homepage fields with Secure Custom Fields.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_starter_business_homepage',
				'title'    => __( 'Business homepage', 'greendawn-core' ),
				'location' => array(
					array(
						array(
							'param'    => 'page_template',
							'operator' => '==',
							'value'    => self::TEMPLATE,
						),
					),
				),
				'fields'   => array(
					array(
						'key'          => 'field_starter_home_hero_title',
						'name'         => 'home_hero_title',
						'label'        => __( 'Hero headline', 'greendawn-core' ),
						'instructions' => __( 'Leave blank to use the Page title.', 'greendawn-core' ),
						'type'         => 'text',
						'maxlength'    => 120,
					),
					array(
						'key'       => 'field_starter_home_hero_text',
						'name'      => 'home_hero_text',
						'label'     => __( 'Hero text', 'greendawn-core' ),
						'type'      => 'textarea',
						'maxlength' => 300,
					),
					array(
						'key'          => 'field_starter_home_intro',
						'name'         => 'home_intro',
						'label'        => __( 'Business introduction', 'greendawn-core' ),
						'type'         => 'wysiwyg',
						'toolbar'      => 'basic',
						'media_upload' => 0,
					),
					array(
						'key'           => 'field_starter_home_services',
						'name'          => 'home_featured_services',
						'label'         => __( 'Featured services', 'greendawn-core' ),
						'type'          => 'relationship',
						'post_type'     => array( 'service' ),
						'post_status'   => array( 'publish' ),
						'filters'       => array( 'search' ),
						'return_format' => 'id',
					),
					array(
						'key'   => 'field_starter_home_cta_heading',
						'name'  => 'home_cta_heading',
						'label' => __( 'Contact heading', 'greendawn-core' ),
						'type'  => 'text',
					),
					array(
						'key'   => 'field_starter_home_cta_label',
						'name'  => 'home_cta_label',
						'label' => __( 'Contact button label', 'greendawn-core' ),
						'type'  => 'text',
					),
					array(
						'key'   => 'field_starter_home_cta_url',
						'name'  => 'home_cta_url',
						'label' => __( 'Contact button URL', 'greendawn-core' ),
						'type'  => 'url',
					),
				),
			)
		);
	}
}
