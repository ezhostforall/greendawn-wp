<?php
/**
 * Gallery project stories and ordered attachment selections.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/** Owns the Gallery content fields and their initial values. */
final class Greendawn_Gallery implements Module_Interface {

	private const TEMPLATE = 'templates/greendawn-gallery.php';
	private const PREFIX   = 'greendawn_gallery_';

	/** Registers Gallery editing and rendering hooks. */
	public function register(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'greendawn_gallery_field_value', array( $this, 'field_value' ), 10, 3 );
		add_filter( 'use_block_editor_for_post', array( $this, 'use_block_editor' ), 10, 2 );
		add_action( 'add_meta_boxes_page', array( $this, 'hide_body_editor' ) );
	}

	/**
	 * Keeps the Gallery page in the structured editing interface.
	 *
	 * @param bool     $enabled Existing editor decision.
	 * @param \WP_Post $post Page being edited.
	 */
	public function use_block_editor( bool $enabled, \WP_Post $post ): bool {
		return $enabled && self::TEMPLATE !== get_page_template_slug( $post );
	}

	/**
	 * Keeps the imported body in storage while removing its editing box.
	 *
	 * @param \WP_Post $post Page being edited.
	 */
	public function hide_body_editor( \WP_Post $post ): void {
		if ( self::TEMPLATE === get_page_template_slug( $post ) ) {
			remove_post_type_support( 'page', 'editor' );
		}
	}

	/** Defines the editable copy and attachment fields. */
	public function fields(): array {
		$fields = array(
			'heading'       => array(
				'label'         => __( 'Hero heading', 'greendawn' ),
				'type'          => 'text',
				'default_value' => 'Our',
				'required'      => 1,
			),
			'accent'        => array(
				'label'         => __( 'Hero accent', 'greendawn' ),
				'type'          => 'text',
				'default_value' => 'Projects',
			),
			'intro'         => array(
				'label'         => __( 'Hero introduction', 'greendawn' ),
				'type'          => 'textarea',
				'default_value' => 'Delivered solutions, built around real sites, constraints, and requirements.',
			),
			'cta_heading'   => array(
				'label'         => __( 'Enquiry heading', 'greendawn' ),
				'type'          => 'text',
				'default_value' => 'Not Sure Where to Start?',
			),
			'cta_copy'      => array(
				'label'         => __( 'Enquiry copy', 'greendawn' ),
				'type'          => 'textarea',
				'default_value' => "Every site is different. Power availability, parking layout, and how your team will use the chargers all play a part.\nWe’ll help you work out what makes sense before anything is installed.",
			),
			'cta_label'     => array(
				'label'         => __( 'Enquiry button label', 'greendawn' ),
				'type'          => 'text',
				'default_value' => 'Start Your Journey',
			),
			'photo_heading' => array(
				'label'         => __( 'Photo gallery heading', 'greendawn' ),
				'type'          => 'text',
				'default_value' => 'Our Gallery',
			),
			'photos'        => array(
				'label'         => __( 'Gallery photographs', 'greendawn' ),
				'type'          => 'gallery',
				'return_format' => 'id',
				'preview_size'  => 'medium',
				'instructions'  => __( 'Select and order the photos. Edit alt text and captions in the Media Library.', 'greendawn' ),
			),
			'stories'       => array(
				'label'        => __( 'Project stories', 'greendawn' ),
				'type'         => 'repeater',
				'layout'       => 'block',
				'collapsed'    => 'field_greendawn_gallery_story_title',
				'max'          => 100,
				'button_label' => __( 'Add project story', 'greendawn' ),
				'sub_fields'   => $this->story_fields(),
			),
		);
		foreach ( $fields as $name => &$field ) {
			$field['name'] = self::PREFIX . $name;
			$field['key']  = 'field_' . $field['name'];
		}
		return $fields;
	}

	/** Defines the fields for one project story. */
	private function story_fields(): array {
		$fields = array(
			'title'  => array(
				'label'    => __( 'Project name', 'greendawn' ),
				'type'     => 'text',
				'required' => 1,
			),
			'facts'  => array(
				'label' => __( 'Project facts, one per line', 'greendawn' ),
				'type'  => 'textarea',
				'rows'  => 3,
			),
			'body'   => array(
				'label'        => __( 'Project story', 'greendawn' ),
				'type'         => 'wysiwyg',
				'tabs'         => 'visual',
				'toolbar'      => 'basic',
				'media_upload' => 0,
			),
			'status' => array(
				'label' => __( 'Optional status text', 'greendawn' ),
				'type'  => 'text',
			),
			'images' => array(
				'label'         => __( 'Project photographs', 'greendawn' ),
				'type'          => 'gallery',
				'return_format' => 'id',
				'preview_size'  => 'medium',
				'instructions'  => __( 'The first photo leads the story. Drag photos to change their order.', 'greendawn' ),
			),
		);
		foreach ( $fields as $name => &$field ) {
			$field['name'] = $name;
			$field['key']  = 'field_' . self::PREFIX . 'story_' . $name;
		}
		return array_values( $fields );
	}

	/** Resolves the approved upload paths in one attachment query per request. */
	private function defaults(): array {
		static $defaults;
		if ( isset( $defaults ) ) {
			return $defaults;
		}
		$defaults = require GREENDAWN_CORE_DIR . 'includes/gallery-defaults.php';
		$paths    = $defaults['photos'];
		foreach ( $defaults['stories'] as $story ) {
			$paths = array_merge( $paths, $story['images'] );
		}
		$paths  = array_unique( $paths );
		$scaled = static fn( string $path ): string => (string) preg_replace( '/(\.[^.]+)$/', '-scaled$1', $path );
		$files  = array();
		$images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- One lookup for all approved local uploads.
				'meta_query'     => array(
					array(
						'key'     => '_wp_attached_file',
						'value'   => array_values( array_unique( array_merge( $paths, array_map( $scaled, $paths ) ) ) ),
						'compare' => 'IN',
					),
				),
			)
		);
		foreach ( $images as $image ) {
			$file = get_post_meta( $image->ID, '_wp_attached_file', true );
			if ( is_string( $file ) && ! isset( $files[ $file ] ) ) {
				$files[ $file ] = $image->ID;
			}
		}
		$resolve            = static fn( array $group ): array => array_values(
			array_filter( array_map( static fn( string $path ): int => $files[ $path ] ?? $files[ $scaled( $path ) ] ?? 0, $group ) )
		);
		$defaults['photos'] = $resolve( $defaults['photos'] );
		foreach ( $defaults['stories'] as &$story ) {
			$story['images'] = $resolve( $story['images'] );
		}
		return $defaults;
	}

	/**
	 * Provides initial field values in the editor without writing metadata.
	 *
	 * @param mixed $value Stored value.
	 * @param mixed $post_id SCF record identifier.
	 * @param array $field Field definition.
	 * @return mixed
	 */
	public function load_default( $value, $post_id, array $field ) {
		if ( metadata_exists( 'post', absint( $post_id ), $field['name'] ) ) {
			return $value;
		}
		if ( 'gallery' === $field['type'] ) {
			return $this->defaults()['photos'];
		}
		if ( 'repeater' === $field['type'] ) {
			return array_map(
				static function ( array $row ) use ( $field ): array {
					$values = array();
					foreach ( $field['sub_fields'] as $sub ) {
						$values[ $sub['key'] ] = $row[ $sub['name'] ];
					}
					return $values;
				},
				$this->defaults()['stories']
			);
		}
		return $field['default_value'] ?? '';
	}

	/**
	 * Resolves page data, including native repeater rows when SCF is inactive.
	 *
	 * @param mixed  $fallback Existing value.
	 * @param string $name Short field name.
	 * @param int    $post_id Page record.
	 * @return mixed
	 */
	public function field_value( $fallback, string $name, int $post_id ) {
		$field = $this->fields()[ $name ] ?? null;
		if ( ! $field ) {
			return $fallback;
		}
		$value = get_post_meta( $post_id, $field['name'], true );
		if ( 'stories' !== $name ) {
			return $this->load_default( $value, $post_id, $field );
		}
		if ( ! metadata_exists( 'post', $post_id, $field['name'] ) ) {
			return $this->defaults()['stories'];
		}

		$rows  = array();
		$count = is_numeric( $value ) ? min( 100, (int) $value ) : 0;
		for ( $i = 0; $i < $count; ++$i ) {
			$row = array();
			foreach ( $field['sub_fields'] as $sub ) {
				$key                 = $field['name'] . '_' . $i . '_' . $sub['name'];
				$row[ $sub['name'] ] = get_post_meta( $post_id, $key, true );
			}
			$rows[] = $row;
		}
		return $rows;
	}

	/** Registers the fields on pages using the Gallery template. */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		$schema = $this->fields();
		$tabs   = array(
			'hero'     => array( __( 'Hero', 'greendawn' ), array( 'heading', 'accent', 'intro' ) ),
			'projects' => array( __( 'Projects', 'greendawn' ), array( 'stories' ) ),
			'enquire'  => array( __( 'Enquiry', 'greendawn' ), array( 'cta_heading', 'cta_copy', 'cta_label' ) ),
			'photos'   => array( __( 'Photo gallery', 'greendawn' ), array( 'photo_heading', 'photos' ) ),
		);
		$fields = array();
		foreach ( $tabs as $name => $tab ) {
			$fields[] = array(
				'key'   => 'field_' . self::PREFIX . 'tab_' . $name,
				'label' => $tab[0],
				'type'  => 'tab',
			);
			foreach ( $tab[1] as $key ) {
				$field    = $schema[ $key ];
				$fields[] = $field;
				add_filter( 'acf/load_value/key=' . $field['key'], array( $this, 'load_default' ), 10, 3 );
			}
		}
		acf_add_local_field_group(
			array(
				'key'      => 'group_greendawn_gallery',
				'title'    => __( 'Greendawn Gallery', 'greendawn' ),
				'location' => array(
					array(
						array(
							'param'    => 'page_template',
							'operator' => '==',
							'value'    => self::TEMPLATE,
						),
					),
				),
				'fields'   => $fields,
			)
		);
	}
}
