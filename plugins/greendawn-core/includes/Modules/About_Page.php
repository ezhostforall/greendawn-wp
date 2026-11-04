<?php
/**
 * Structured content for the opt-in About template.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the About fields and scopes the editing interface to that template.
 */
final class About_Page implements Module_Interface {

	private const TEMPLATE = 'templates/about.php';

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
	 * Checks if this page uses the structured About editing form.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function uses_fields( \WP_Post $post ): bool {
		return 'page' === $post->post_type
			&& self::TEMPLATE === get_page_template_slug( $post )
			&& function_exists( 'acf_add_local_field_group' );
	}

	/**
	 * Registers the About fields with Secure Custom Fields.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_starter_about',
				'title'    => __( 'About page', 'greendawn-core' ),
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
						'key'       => 'field_starter_about_intro',
						'name'      => 'about_intro',
						'label'     => __( 'Introduction', 'greendawn-core' ),
						'type'      => 'textarea',
						'maxlength' => 500,
					),
					array(
						'key'   => 'field_starter_about_story_heading',
						'name'  => 'about_story_heading',
						'label' => __( 'Story heading', 'greendawn-core' ),
						'type'  => 'text',
					),
					array(
						'key'          => 'field_starter_about_story',
						'name'         => 'about_story',
						'label'        => __( 'Story', 'greendawn-core' ),
						'type'         => 'wysiwyg',
						'toolbar'      => 'basic',
						'media_upload' => 0,
					),
					array(
						'key'           => 'field_starter_about_story_image',
						'name'          => 'about_story_image',
						'label'         => __( 'Story image', 'greendawn-core' ),
						'type'          => 'image',
						'return_format' => 'id',
					),
					array(
						'key'   => 'field_starter_about_cta_label',
						'name'  => 'about_cta_label',
						'label' => __( 'Contact button label', 'greendawn-core' ),
						'type'  => 'text',
					),
					array(
						'key'   => 'field_starter_about_cta_url',
						'name'  => 'about_cta_url',
						'label' => __( 'Contact button URL', 'greendawn-core' ),
						'type'  => 'url',
					),
				),
			)
		);
	}
}
