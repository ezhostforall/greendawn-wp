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
		add_filter( 'acf/load_value/key=field_greendawn_charger_description', array( $this, 'description' ), 10, 2 );
		add_filter( 'greendawn_charger_data', array( $this, 'view_data' ), 10, 2 );
		add_filter( 'greendawn_selected_charger', array( $this, 'selected_charger' ) );
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
	 * Resolves an available public product without exposing drafts/private/passworded records.
	 *
	 * @param mixed $id Candidate product ID.
	 * @return array
	 */
	public static function quote_context( $id ): array {
		if ( ! is_scalar( $id ) || ! preg_match( '/^[1-9][0-9]*$/', (string) $id ) ) {
			return array();
		}
		$post = get_post( (int) $id );
		if ( ! $post || 'evcharger' !== $post->post_type || 'publish' !== $post->post_status || '' !== $post->post_password ) {
			return array();
		}
		return array(
			'id'    => $post->ID,
			'title' => html_entity_decode( wp_strip_all_tags( $post->post_title ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'url'   => get_permalink( $post ),
		);
	}

	/**
	 * Supplies selected product context on its detail page or the ordinary Enquire fallback.
	 *
	 * @param array $fallback Existing context.
	 * @return array
	 */
	public function selected_charger( array $fallback ): array {
		if ( is_singular( 'evcharger' ) ) {
			return self::quote_context( get_queried_object_id() );
		}
		if ( is_page( 'enquire' ) && isset( $_GET['charger'] ) && is_string( $_GET['charger'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only public product selection, not a mutation.
			$slug = sanitize_title( wp_unslash( $_GET['charger'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only public product selection.
			$post = get_page_by_path( $slug, OBJECT, 'evcharger' );
			return $post ? self::quote_context( $post->ID ) : array();
		}
		return $fallback;
	}

	/**
	 * Unset descriptions can use native imported copy; builders/shortcodes never execute.
	 *
	 * @param mixed $value Saved SCF value.
	 * @param mixed $post_id Record identifier.
	 * @return string
	 */
	public function description( $value, $post_id ): string {
		$id = absint( $post_id );
		if ( metadata_exists( 'post', $id, 'greendawn_charger_description' ) ) {
			return is_string( $value ) ? $value : '';
		}
		$body = (string) get_post_field( 'post_content', $id, 'raw' );
		if ( preg_match( '/\[\/?[a-zA-Z_][a-zA-Z0-9_-]*(?:\s|\])/', $body ) ) {
			return ''; // Legacy builder/shortcode content needs explicit editorial mapping.
		}
		$body = preg_replace( '~</(?:p|div|li|h[1-6])\s*>|<br\s*/?>~i', "\n\n", $body );
		return trim( html_entity_decode( wp_strip_all_tags( $body ), ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ) );
	}

	/**
	 * Fixed specification labels read raw metadata, never SCF's display defaults.
	 *
	 * @param array $fallback Existing data.
	 * @param int   $id Product record.
	 * @return array
	 */
	public function view_data( array $fallback, int $id ): array {
		$specs = array(
			'brand'                    => __( 'Brand', 'greendawn' ),
			'charging_power'           => __( 'Charging power', 'greendawn' ),
			'mains_supply'             => __( 'Mains supply', 'greendawn' ),
			'supply_voltage'           => __( 'Supply voltage', 'greendawn' ),
			'dimensions'               => __( 'Dimensions (H × W × D)', 'greendawn' ),
			'weight'                   => __( 'Weight', 'greendawn' ),
			'mounting_options'         => __( 'Mounting options', 'greendawn' ),
			'max_current_amps'         => __( 'Maximum current', 'greendawn' ),
			'no_of_output_connections' => __( 'Number of outputs', 'greendawn' ),
			'output_connector_types'   => __( 'Output connector types', 'greendawn' ),
			'connectivity'             => __( 'Connectivity', 'greendawn' ),
			'price'                    => __( 'Price', 'greendawn' ),
		);
		$data  = array(
			'description'    => $this->description( get_post_meta( $id, 'greendawn_charger_description', true ), $id ),
			'specifications' => array(),
			'brand'          => '',
			'power'          => '',
		);
		foreach ( $specs as $name => $label ) {
			$value = get_post_meta( $id, $name, true );
			if ( 'connectivity' === $name && is_array( $value ) ) {
				$value = implode( ', ', array_filter( array_map( static fn( $item ) => is_string( $item ) ? trim( $item ) : '', $value ), static fn( $item ) => '' !== $item ) );
			}
			if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
				continue;
			}
			$value = trim( (string) $value );
			if ( '' === $value ) {
				continue;
			}
			if ( 'weight' === $name && is_numeric( $value ) ) {
				$value .= ' kg';
			}
			if ( 'dimensions' === $name && preg_match( '/^[0-9.\s×xX]+$/u', $value ) ) {
				$value .= ' mm';
			}
			$data['specifications'][] = array(
				'name'  => $name,
				'label' => $label,
				'value' => $value,
			);
			if ( in_array( $name, array( 'brand', 'charging_power' ), true ) ) {
				$data[ 'brand' === $name ? 'brand' : 'power' ] = $value;
			}
		}
		return $data;
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
						'instructions' => __( 'Edit the approved plain-text description. Unset fields use native imported text; saved blanks stay blank. Builder/shortcode content must be mapped manually and is never executed. The original imported body remains stored.', 'greendawn' ),
						'type'         => 'textarea',
						'new_lines'    => 'wpautop',
					),
				),
			)
		);
	}
}
