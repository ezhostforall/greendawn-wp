<?php
/**
 * Optional Service content model for client sites.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/**
 * Provides the Service routes and content fields.
 */
final class Services implements Module_Interface {

	/**
	 * Hooks the enabled module into WordPress.
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'use_block_editor_for_post', array( $this, 'use_block_editor' ), 10, 2 );
		add_action( 'add_meta_boxes_page', array( $this, 'hide_overview_editor' ) );
	}

	/**
	 * Uses the field form for overview pages when SCF is active.
	 *
	 * @param bool     $use_editor Existing block editor decision.
	 * @param \WP_Post $post       Current post.
	 */
	public function use_block_editor( bool $use_editor, \WP_Post $post ): bool {
		return $use_editor && ! $this->uses_overview_fields( $post );
	}

	/**
	 * Hides the classic body field on the structured overview edit screen.
	 *
	 * @param \WP_Post $post Current page.
	 */
	public function hide_overview_editor( \WP_Post $post ): void {
		if ( $this->uses_overview_fields( $post ) ) {
			remove_post_type_support( 'page', 'editor' );
		}
	}

	/**
	 * Checks whether the page is edited through the overview fields.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function uses_overview_fields( \WP_Post $post ): bool {
		return 'page' === $post->post_type
			&& 'templates/services.php' === get_page_template_slug( $post )
			&& function_exists( 'acf_add_local_field_group' );
	}

	/**
	 * Registers the Service post type.
	 */
	public function register_post_type(): void {
		register_post_type(
			'service',
			array(
				'labels'       => array(
					'name'          => __( 'Services', 'greendawn-core' ),
					'singular_name' => __( 'Service', 'greendawn-core' ),
					'add_new_item'  => __( 'Add Service', 'greendawn-core' ),
					'edit_item'     => __( 'Edit Service', 'greendawn-core' ),
				),
				'public'       => true,
				'has_archive'  => false,
				'rewrite'      => array(
					'slug'       => 'services',
					'with_front' => false,
				),
				'supports'     => array( 'title', 'excerpt', 'thumbnail', 'revisions' ),
				'menu_icon'    => 'dashicons-portfolio',
				'map_meta_cap' => true,
			)
		);
	}

	/**
	 * Registers the fixed set of fields with Secure Custom Fields.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_starter_service',
				'title'    => __( 'Service details', 'greendawn-core' ),
				'location' => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'service',
						),
					),
				),
				'fields'   => array(
					array(
						'key'          => 'field_starter_service_description',
						'name'         => 'service_description',
						'label'        => __( 'Description', 'greendawn-core' ),
						'type'         => 'wysiwyg',
						'toolbar'      => 'basic',
						'media_upload' => 0,
					),
					array(
						'key'        => 'field_starter_service_benefits',
						'name'       => 'service_benefits',
						'label'      => __( 'Benefits', 'greendawn-core' ),
						'type'       => 'repeater',
						'layout'     => 'row',
						'sub_fields' => array(
							array(
								'key'      => 'field_starter_service_benefit',
								'name'     => 'benefit',
								'label'    => __( 'Benefit', 'greendawn-core' ),
								'type'     => 'text',
								'required' => 1,
							),
						),
					),
					array(
						'key'        => 'field_starter_service_faqs',
						'name'       => 'service_faqs',
						'label'      => __( 'FAQs', 'greendawn-core' ),
						'type'       => 'repeater',
						'layout'     => 'row',
						'sub_fields' => array(
							array(
								'key'      => 'field_starter_service_question',
								'name'     => 'question',
								'label'    => __( 'Question', 'greendawn-core' ),
								'type'     => 'text',
								'required' => 1,
							),
							array(
								'key'      => 'field_starter_service_answer',
								'name'     => 'answer',
								'label'    => __( 'Answer', 'greendawn-core' ),
								'type'     => 'textarea',
								'required' => 1,
							),
						),
					),
					array(
						'key'   => 'field_starter_service_cta_label',
						'name'  => 'service_cta_label',
						'label' => __( 'Call to action label', 'greendawn-core' ),
						'type'  => 'text',
					),
					array(
						'key'   => 'field_starter_service_cta_url',
						'name'  => 'service_cta_url',
						'label' => __( 'Call to action URL', 'greendawn-core' ),
						'type'  => 'url',
					),
				),
			)
		);

		acf_add_local_field_group(
			array(
				'key'      => 'group_starter_services_overview',
				'title'    => __( 'Services overview', 'greendawn-core' ),
				'location' => array(
					array(
						array(
							'param'    => 'page_template',
							'operator' => '==',
							'value'    => 'templates/services.php',
						),
					),
				),
				'fields'   => array(
					array(
						'key'   => 'field_starter_services_intro',
						'name'  => 'services_intro',
						'label' => __( 'Introduction', 'greendawn-core' ),
						'type'  => 'textarea',
					),
				),
			)
		);
	}
}
