<?php
/**
 * Versioned editorial field contract shared by editing, reading and future imports.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/** Constrained content fields, without layout controls or seeded public copy. */
final class Guide_Fields {

	public const VERSION  = '1';
	public const PREFIXES = array(
		'guide'  => 'greendawn_guide_',
		'hub'    => 'greendawn_guides_hub_',
		'region' => 'greendawn_region_',
	);

	/**
	 * Build an editor field. Descendant keys include their full ancestor path.
	 *
	 * @param string $name Field name.
	 * @param string $label Editor label.
	 * @param string $type SCF type.
	 * @param array  $settings Additional settings.
	 */
	private static function field( string $name, string $label, string $type = 'text', array $settings = array() ): array {
		return array_merge(
			array(
				'name'  => $name,
				'label' => $label,
				'type'  => $type,
			),
			$settings
		);
	}

	/**
	 * Repeated, bounded rows with no arbitrary HTML or component selection.
	 *
	 * @param string $name Field name.
	 * @param string $label Editor label.
	 * @param array  $children Row fields.
	 * @param int    $max Maximum rows.
	 */
	private static function rows( string $name, string $label, array $children, int $max = 100 ): array {
		return self::field(
			$name,
			$label,
			'repeater',
			array(
				'layout'       => 'block',
				'max'          => $max,
				'sub_fields'   => $children,
				'button_label' => __( 'Add row', 'greendawn' ),
			)
		);
	}

	/** Shared image settings use local attachment IDs, never formatted image arrays. */
	private static function image(): array {
		return self::field(
			'image',
			__( 'Image', 'greendawn' ),
			'image',
			array(
				'return_format' => 'id',
				'library'       => 'all',
			)
		);
	}

	/** Shared question/answer records. */
	private static function faqs(): array {
		return self::rows( 'faqs', __( 'Supplementary questions', 'greendawn' ), array( self::field( 'question', __( 'Question', 'greendawn' ) ), self::field( 'answer', __( 'Answer', 'greendawn' ), 'textarea' ) ) );
	}

	/**
	 * Published guide relationship; public readers independently enforce visibility.
	 *
	 * @param string $name Field name.
	 */
	private static function related( string $name ): array {
		return self::field(
			$name,
			__( 'Related guides — drag to reorder', 'greendawn' ),
			'relationship',
			array(
				'post_type'     => array( Guides::TYPE ),
				'post_status'   => array( 'publish' ),
				'return_format' => 'id',
				'max'           => 12,
			)
		);
	}

	/** Closing copy uses the existing enquiry action, not another form configuration. */
	private static function cta(): array {
		return array(
			self::field( 'cta_heading', __( 'Closing enquiry heading', 'greendawn' ) ),
			self::field( 'cta_copy', __( 'Closing enquiry copy', 'greendawn' ), 'textarea' ),
			self::field( 'cta_label', __( 'Closing enquiry button label', 'greendawn' ) ),
		);
	}

	/** The complete, ordered section structure used by SCF and native readers. */
	private static function sections(): array {
		return self::rows(
			'sections',
			__( 'Reading sections', 'greendawn' ),
			array(
				self::field( 'heading', __( 'Section heading', 'greendawn' ) ),
				self::field( 'nav_label', __( 'Short contents label (optional)', 'greendawn' ) ),
				self::field( 'body', __( 'Paragraphs', 'greendawn' ), 'textarea' ),
				self::field(
					'list_style',
					__( 'List style', 'greendawn' ),
					'select',
					array(
						'choices'    => array(
							'plain'     => __( 'Bullets', 'greendawn' ),
							'numbered'  => __( 'Numbered', 'greendawn' ),
							'checklist' => __( 'Checklist', 'greendawn' ),
						),
						'allow_null' => 1,
					)
				),
				self::rows( 'list_items', __( 'List items', 'greendawn' ), array( self::field( 'text', __( 'Item', 'greendawn' ) ) ) ),
				self::field( 'table_caption', __( 'Table caption', 'greendawn' ) ),
				self::rows( 'columns', __( 'Table column headings', 'greendawn' ), array( self::field( 'label', __( 'Column heading', 'greendawn' ) ) ), 4 ),
				self::rows( 'table_rows', __( 'Table rows', 'greendawn' ), array( self::rows( 'cells', __( 'Cells in column order', 'greendawn' ), array( self::field( 'text', __( 'Cell', 'greendawn' ), 'textarea' ) ), 4 ) ) ),
				self::image(),
				self::field( 'caption', __( 'Factual image caption', 'greendawn' ) ),
			),
			60
		);
	}

	/**
	 * Compile keys once; top-level names are prefixed, row names remain SCF-native.
	 *
	 * @param array  $fields Field definitions.
	 * @param string $prefix Top-level prefix or ancestor path.
	 * @param bool   $top Whether fields are top-level.
	 */
	private static function compile( array $fields, string $prefix, bool $top = true ): array {
		foreach ( $fields as &$field ) {
			$path         = $prefix . $field['name'];
			$field['key'] = 'field_' . $path;
			if ( $top ) {
				$field['name'] = $path;
			}
			if ( isset( $field['sub_fields'] ) ) {
				$field['sub_fields'] = self::compile( $field['sub_fields'], $path . '_', false );
			}
		}
		return $fields;
	}

	/**
	 * Canonical field definitions. Publication requirements are enforced separately.
	 * Drafts can retain incomplete work; no fabricated default review date or proof.
	 *
	 * @param string $kind guide, hub or region.
	 */
	public static function fields( string $kind ): array {
		$definitions = array(
			'guide'  => array_merge(
				array(
					self::field( 'intro', __( 'Short introduction', 'greendawn' ), 'textarea' ),
					self::image(),
					self::field(
						'image_position',
						__( 'Image focal point', 'greendawn' ),
						'select',
						array(
							'choices'    => array(
								'centre' => __( 'Centre', 'greendawn' ),
								'upper'  => __( 'Upper', 'greendawn' ),
								'lower'  => __( 'Lower', 'greendawn' ),
								'left'   => __( 'Left', 'greendawn' ),
								'right'  => __( 'Right', 'greendawn' ),
							),
							'allow_null' => 1,
						)
					),
					self::field(
						'primary_topic',
						__( 'Primary topic (choose an assigned topic)', 'greendawn' ),
						'taxonomy',
						array(
							'taxonomy'      => Guides::TAXONOMY,
							'field_type'    => 'select',
							'return_format' => 'id',
							'allow_null'    => 1,
							'add_term'      => 0,
							'save_terms'    => 0,
							'load_terms'    => 0,
						)
					),
					self::field(
						'reviewed_on',
						__( 'Actually reviewed on (optional)', 'greendawn' ),
						'date_picker',
						array(
							'return_format'  => 'Ymd',
							'display_format' => 'j F Y',
							'instructions'   => __( 'Leave empty until a review has actually taken place. Importing does not count as a review.', 'greendawn' ),
						)
					),
					self::field( 'answer', __( 'Direct opening answer', 'greendawn' ), 'textarea' ),
					self::sections(),
					self::faqs(),
					self::rows(
						'sources',
						__( 'Sources', 'greendawn' ),
						array(
							self::field( 'title', __( 'Source title', 'greendawn' ) ),
							self::field( 'publisher', __( 'Publisher', 'greendawn' ) ),
							self::field( 'url', __( 'Source URL', 'greendawn' ), 'url' ),
							self::field(
								'checked_on',
								__( 'Actually checked on (optional)', 'greendawn' ),
								'date_picker',
								array(
									'return_format'  => 'Ymd',
									'display_format' => 'j F Y',
								)
							),
						)
					),
					self::related( 'related' ),
				),
				self::cta()
			),
			'hub'    => array_merge(
				array(
					self::field( 'heading', __( 'Hero heading', 'greendawn' ) ),
					self::field( 'intro', __( 'Hero introduction', 'greendawn' ), 'textarea' ),
					self::image(),
					self::field(
						'topics',
						__( 'Topic display order', 'greendawn' ),
						'taxonomy',
						array(
							'taxonomy'      => Guides::TAXONOMY,
							'field_type'    => 'multi_select',
							'multiple'      => 1,
							'return_format' => 'id',
							'add_term'      => 0,
							'save_terms'    => 0,
							'load_terms'    => 0,
						)
					),
					self::field(
						'featured',
						__( 'Featured guide', 'greendawn' ),
						'post_object',
						array(
							'post_type'     => array( Guides::TYPE ),
							'post_status'   => array( 'publish' ),
							'return_format' => 'id',
							'allow_null'    => 1,
						)
					),
					self::field( 'search_label', __( 'Search label', 'greendawn' ) ),
					self::field( 'empty_heading', __( 'No-results heading', 'greendawn' ) ),
					self::field( 'empty_copy', __( 'No-results copy', 'greendawn' ), 'textarea' ),
					self::field( 'trust_heading', __( 'Trust heading', 'greendawn' ) ),
					self::field( 'trust_copy', __( 'Trust copy', 'greendawn' ), 'textarea' ),
					self::field( 'enquiry_heading', __( 'Supporting enquiry heading', 'greendawn' ) ),
					self::field( 'enquiry_copy', __( 'Supporting enquiry copy', 'greendawn' ), 'textarea' ),
				),
				self::cta()
			),
			'region' => array_merge(
				array(
					self::field( 'name', __( 'Region name', 'greendawn' ) ),
					self::field( 'heading', __( 'Hero heading', 'greendawn' ) ),
					self::field( 'intro', __( 'Hero introduction', 'greendawn' ), 'textarea' ),
					self::image(),
					self::rows(
						'services',
						__( 'Service summaries', 'greendawn' ),
						array(
							self::field( 'title', __( 'Heading', 'greendawn' ) ),
							self::field( 'copy', __( 'Copy', 'greendawn' ), 'textarea' ),
							self::field(
								'icon',
								__( 'Icon', 'greendawn' ),
								'select',
								array(
									'choices'    => array(
										'bolt'     => __( 'Charging', 'greendawn' ),
										'site'     => __( 'Site', 'greendawn' ),
										'fleet'    => __( 'Fleet', 'greendawn' ),
										'property' => __( 'Property', 'greendawn' ),
									),
									'allow_null' => 1,
								)
							),
						)
					),
					self::rows( 'considerations', __( 'Local considerations', 'greendawn' ), array( self::field( 'heading', __( 'Heading', 'greendawn' ) ), self::field( 'copy', __( 'Copy', 'greendawn' ), 'textarea' ) ) ),
					self::rows( 'places', __( 'Places served', 'greendawn' ), array( self::field( 'name', __( 'Place name', 'greendawn' ) ) ) ),
					self::faqs(),
					self::field( 'proof_heading', __( 'Project evidence heading', 'greendawn' ) ),
					self::field( 'proof_copy', __( 'Supported project evidence', 'greendawn' ), 'textarea' ),
					self::field( 'proof_image', __( 'Project image', 'greendawn' ), 'image', array( 'return_format' => 'id' ) ),
					self::field( 'proof_url', __( 'Published project URL', 'greendawn' ), 'url' ),
					self::related( 'guides' ),
				),
				self::cta()
			),
		);
		return isset( self::PREFIXES[ $kind ] ) ? self::compile( $definitions[ $kind ], self::PREFIXES[ $kind ] ) : array();
	}

	/**
	 * Resolve only our registered stable field keys, including nested row fields.
	 *
	 * @param string $key SCF field key.
	 */
	public static function find( string $key ): array {
		static $lookup;
		if ( null === $lookup ) {
			$lookup = array();
			$walk   = static function ( array $fields ) use ( &$walk, &$lookup ): void {
				foreach ( $fields as $field ) {
					$lookup[ $field['key'] ] = $field;
					$walk( $field['sub_fields'] ?? array() );
				}
			};
			foreach ( array_keys( self::PREFIXES ) as $kind ) {
				$walk( self::fields( $kind ) );
			}
		}
		return $lookup[ $key ] ?? array();
	}
}
