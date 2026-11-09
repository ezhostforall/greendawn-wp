<?php
/**
 * Structured team records for fixed page templates.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps team content editable without creating accounts or public profile routes.
 */
final class Team_Members implements Module_Interface {

	/**
	 * Registers native records even when the field editor is unavailable.
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	/**
	 * Uses native Page capabilities so administrators and editors can maintain records.
	 */
	public function register_post_type(): void {
		register_post_type(
			'team_member',
			array(
				'labels'              => array(
					'name'          => __( 'Team Members', 'greendawn' ),
					'singular_name' => __( 'Team Member', 'greendawn' ),
					'add_new_item'  => __( 'Add Team Member', 'greendawn' ),
					'edit_item'     => __( 'Edit Team Member', 'greendawn' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title', 'thumbnail', 'page-attributes', 'revisions' ),
				'capability_type'     => 'page',
				'map_meta_cap'        => true,
				'can_export'          => true,
				'delete_with_user'    => false,
				'menu_icon'           => 'dashicons-groups',
			)
		);
	}

	/**
	 * Provides content fields; layout and page inclusion belong to fixed templates.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_greendawn_team_member',
				'title'    => __( 'Team member details', 'greendawn' ),
				'location' => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'team_member',
						),
					),
				),
				'fields'   => array(
					array(
						'key'   => 'field_greendawn_team_role',
						'name'  => 'greendawn_team_role',
						'label' => __( 'Role / organisation', 'greendawn' ),
						'type'  => 'text',
					),
					array(
						'key'       => 'field_greendawn_team_biography',
						'name'      => 'greendawn_team_biography',
						'label'     => __( 'Biography', 'greendawn' ),
						'type'      => 'textarea',
						'new_lines' => 'wpautop',
					),
					array(
						'key'   => 'field_greendawn_team_linkedin',
						'name'  => 'greendawn_team_linkedin',
						'label' => __( 'LinkedIn profile URL', 'greendawn' ),
						'type'  => 'url',
					),
					array(
						'key'   => 'field_greendawn_team_connections',
						'name'  => 'greendawn_team_connections',
						'label' => __( 'Connection-count label (optional)', 'greendawn' ),
						'type'  => 'text',
					),
				),
			)
		);
	}
}
