<?php
/**
 * Import-compatible EV charger content and specification fields.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/**
 * Owns charger registration without depending on the theme or legacy snippets.
 */
final class EV_Chargers implements Module_Interface {

	private const REWRITE_VERSION = '1';

	/**
	 * Registers the site-specific content model on every Core installation.
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_content' ) );
		add_action( 'init', array( $this, 'refresh_rewrites' ), 20 );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	/**
	 * Retains the source storage keys and /evcharger/<slug>/ detail URLs.
	 */
	public function register_content(): void {
		register_post_type(
			'evcharger',
			array(
				'labels'              => array(
					'name'          => __( 'EV Chargers', 'greendawn' ),
					'singular_name' => __( 'EV Charger', 'greendawn' ),
					'add_new_item'  => __( 'Add EV Charger', 'greendawn' ),
					'edit_item'     => __( 'Edit EV Charger', 'greendawn' ),
				),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_rest'        => false,
				'has_archive'         => false,
				'rewrite'             => array(
					'slug'       => 'evcharger',
					'with_front' => false,
					'feeds'      => false,
				),
				'supports'            => array( 'title', 'thumbnail', 'revisions' ),
				'taxonomies'          => array( 'brands', 'power_outputs' ),
				'capability_type'     => 'page',
				'map_meta_cap'        => true,
				'can_export'          => true,
				'delete_with_user'    => false,
				'menu_icon'           => 'dashicons-car',
				'exclude_from_search' => false,
			)
		);

		foreach ( array( 'brands', 'power_outputs' ) as $taxonomy ) {
			register_taxonomy(
				$taxonomy,
				array( 'evcharger' ),
				array(
					'labels'             => array(
						'name'          => 'brands' === $taxonomy ? __( 'Brands', 'greendawn' ) : __( 'Power Outputs', 'greendawn' ),
						'singular_name' => 'brands' === $taxonomy ? __( 'Brand', 'greendawn' ) : __( 'Power Output', 'greendawn' ),
					),
					'hierarchical'       => 'brands' === $taxonomy,
					'public'             => false,
					'publicly_queryable' => false,
					'show_ui'            => true,
					'show_admin_column'  => true,
					'show_in_rest'       => false,
					'rewrite'            => false,
					'query_var'          => false,
					'capabilities'       => array(
						'manage_terms' => 'edit_pages',
						'edit_terms'   => 'edit_pages',
						'delete_terms' => 'edit_pages',
						'assign_terms' => 'edit_pages',
					),
				)
			);
		}
	}

	/**
	 * Refreshes rules once after installation or update, after all init registrations.
	 */
	public function refresh_rewrites(): void {
		if ( self::REWRITE_VERSION !== get_option( 'greendawn_core_charger_rewrite_version' ) ) {
			flush_rewrite_rules( false );
			update_option( 'greendawn_core_charger_rewrite_version', self::REWRITE_VERSION, false );
		}
	}

	/**
	 * Loads the original field schema unchanged; imported values are never seeded.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		$groups = json_decode( file_get_contents( GREENDAWN_CORE_DIR . 'fields/ev-charger-specification.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reads a bundled local schema, never a remote URL.
		foreach ( $groups as $group ) {
			acf_add_local_field_group( $group );
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_greendawn_charger_content',
				'title'    => __( 'Charger content', 'greendawn' ),
				'location' => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'evcharger',
						),
					),
				),
				'fields'   => array(
					array(
						'key'          => 'field_greendawn_charger_description',
						'name'         => 'greendawn_charger_description',
						'label'        => __( 'Description', 'greendawn' ),
						'instructions' => __( 'Add the approved plain-text description. Imported legacy content is preserved separately until it has been reviewed and mapped.', 'greendawn' ),
						'type'         => 'textarea',
						'new_lines'    => 'wpautop',
					),
				),
			)
		);
	}
}
