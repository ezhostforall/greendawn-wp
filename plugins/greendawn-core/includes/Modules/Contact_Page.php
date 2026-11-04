<?php
/**
 * Structured content for the opt-in Contact template.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Contact fields and scopes the editing interface to that template.
 */
final class Contact_Page implements Module_Interface {

	private const TEMPLATE = 'templates/contact.php';

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
	 * Checks if this page uses the structured Contact editing form.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function uses_fields( \WP_Post $post ): bool {
		return 'page' === $post->post_type
			&& self::TEMPLATE === get_page_template_slug( $post )
			&& function_exists( 'acf_add_local_field_group' );
	}

	/**
	 * Registers the Contact fields with Secure Custom Fields.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_starter_contact',
				'title'    => __( 'Contact details', 'greendawn-core' ),
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
						'key'       => 'field_starter_contact_intro',
						'name'      => 'contact_intro',
						'label'     => __( 'Introduction', 'greendawn-core' ),
						'type'      => 'textarea',
						'maxlength' => 500,
					),
					array(
						'key'   => 'field_starter_contact_phone',
						'name'  => 'contact_phone',
						'label' => __( 'Phone', 'greendawn-core' ),
						'type'  => 'text',
					),
					array(
						'key'   => 'field_starter_contact_email',
						'name'  => 'contact_email',
						'label' => __( 'Public email address', 'greendawn-core' ),
						'type'  => 'email',
					),
					array(
						'key'   => 'field_starter_contact_address',
						'name'  => 'contact_address',
						'label' => __( 'Address', 'greendawn-core' ),
						'type'  => 'textarea',
					),
				),
			)
		);
	}
}
