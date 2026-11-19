<?php
/**
 * Structured landing-page content and the reviewed Workplace campaign upgrade.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/** Stores repeatable SCF content in native metadata, independently of the editor plugin. */
final class Campaign_Content {

	private const VERSION = '1';
	private const MARKER  = '_greendawn_campaign_content_version';

	/** Additional scalar and repeatable campaign field definitions. */
	public static function fields(): array {
		$visibility = array(
			'Show row',
			'select',
			array(
				'choices'       => array(
					'active' => 'While promotion is active',
					'always' => 'Always',
					'closed' => 'After expiry',
				),
				'default_value' => 'active',
			),
		);
		return array(
			'Funding summary'            => array(
				'metrics' => array(
					'Funding figures',
					'repeater',
					array(
						'prefix' => array( 'Qualifier' ),
						'value'  => array( 'Figure' ),
						'copy'   => array( 'Explanation', 'textarea' ),
					),
				),
			),
			'Eligibility'                => array(
				'eligibility'    => array(
					'Eligibility checklist',
					'repeater',
					array(
						'title' => array( 'Heading' ),
						'copy'  => array( 'Explanation', 'textarea' ),
					),
				),
				'criteria_note'  => array( 'Eligibility note', 'textarea' ),
				'guidance_label' => array( 'Official guidance link label' ),
				'panel_kicker'   => array( 'Conversation card kicker' ),
				'panel_cta'      => array( 'Conversation card CTA' ),
				'panel_hint'     => array( 'Conversation card reassurance', 'textarea' ),
				'panel_points'   => array( 'Conversation card supporting points', 'repeater', array( 'title' => array( 'Point' ) ) ),
			),
			'Process'                    => array(
				'process_kicker'       => array( 'Process kicker' ),
				'process_title'        => array( 'Process heading' ),
				'process_intro'        => array( 'Process introduction', 'textarea' ),
				'closed_process_title' => array( 'Process heading after expiry' ),
				'process_steps'        => array(
					'Process steps',
					'repeater',
					array(
						'label'        => array( 'Step label' ),
						'title'        => array( 'Heading' ),
						'copy'         => array( 'Explanation', 'textarea' ),
						'closed_title' => array( 'Heading after expiry' ),
						'closed_copy'  => array( 'Explanation after expiry', 'textarea' ),
					),
				),
				'voucher_note'         => array( 'Voucher timing note', 'textarea' ),
			),
			'Frequently asked questions' => array(
				'faq_kicker'      => array( 'FAQ kicker' ),
				'faq_title'       => array( 'FAQ heading' ),
				'faq_intro'       => array( 'FAQ introduction', 'textarea' ),
				'faqs'            => array(
					'Questions and answers',
					'repeater',
					array(
						'question'   => array( 'Question' ),
						'answer'     => array( 'Answer', 'textarea' ),
						'visibility' => $visibility,
					),
				),
				'installer_url'   => array( 'Installation guidance URL', 'url' ),
				'installer_label' => array( 'Installation guidance link label' ),
			),
			'Closing enquiry'            => array(
				'closing_kicker' => array( 'Closing kicker' ),
				'closing_title'  => array( 'Closing heading' ),
				'closing_copy'   => array( 'Closing copy', 'textarea' ),
			),
			'Sources and expiry'         => array(
				'source_label'  => array( 'Source link label' ),
				'checked_date'  => array( 'Guidance checked', 'date_picker' ),
				'source_note'   => array( 'Source conditions note', 'textarea' ),
				'archive_title' => array( 'Archive heading' ),
				'archive_copy'  => array( 'Archive explanation', 'textarea' ),
			),
		);
	}

	/**
	 * Build a field or its child fields with stable SCF keys.
	 *
	 * @param string $key        Local field name.
	 * @param array  $definition Label, type and optional children/settings.
	 * @param string $parent_key     Parent repeater name.
	 */
	public static function editor_field( string $key, array $definition, string $parent_key = '' ): array {
		$field = array(
			'key'   => 'field_greendawn_campaign_' . ( $parent_key ? $parent_key . '_' : '' ) . $key,
			'name'  => $parent_key ? $key : 'greendawn_campaign_' . $key,
			'label' => $definition[0],
			'type'  => $definition[1] ?? 'text',
		);
		if ( 'repeater' === $field['type'] ) {
			$field['max']          = 100;
			$field['layout']       = 'block';
			$field['button_label'] = 'Add row';
			$field['sub_fields']   = array();
			foreach ( $definition[2] as $child => $settings ) {
				$field['sub_fields'][] = self::editor_field( $child, $settings, $key );
			}
		} elseif ( isset( $definition[2] ) ) {
			$field = array_merge( $field, $definition[2] );
		}
		return $field;
	}

	/**
	 * Read a scalar or native SCF repeater without requiring SCF at runtime.
	 *
	 * @param int    $id         Campaign ID.
	 * @param string $key        Field name.
	 * @param array  $definition Field definition.
	 * @return string|array
	 */
	public static function read( int $id, string $key, array $definition ) {
		$name  = 'greendawn_campaign_' . $key;
		$value = get_post_meta( $id, $name, true );
		if ( 'repeater' !== ( $definition[1] ?? '' ) ) {
			return is_scalar( $value ) ? (string) $value : '';
		}
		$rows = array();
		for ( $index = 0, $count = min( 100, absint( $value ) ); $index < $count; ++$index ) {
			$row = array();
			foreach ( $definition[2] as $child => $settings ) {
				$cell          = get_post_meta( $id, $name . '_' . $index . '_' . $child, true );
				$row[ $child ] = is_scalar( $cell ) ? (string) $cell : '';
			}
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * Seed values in SCF's native metadata layout, including field references.
	 *
	 * @param int   $id     Campaign ID.
	 * @param array $values Scalar fields or repeater rows.
	 */
	public static function write( int $id, array $values ): void {
		foreach ( $values as $key => $value ) {
			$name = 'greendawn_campaign_' . $key;
			update_post_meta( $id, $name, is_array( $value ) ? count( $value ) : wp_slash( $value ) );
			update_post_meta( $id, '_' . $name, 'field_greendawn_campaign_' . $key );
			if ( is_array( $value ) ) {
				foreach ( $value as $index => $row ) {
					foreach ( $row as $child => $cell ) {
						$child_name = $name . '_' . $index . '_' . $child;
						update_post_meta( $id, $child_name, wp_slash( $cell ) );
						update_post_meta( $id, '_' . $child_name, 'field_greendawn_campaign_' . $key . '_' . $child );
					}
				}
			}
		}
	}

	/**
	 * Upgrade only the existing Workplace record, once, preserving deliberate edits.
	 *
	 * @param int $id Campaign ID.
	 */
	public static function upgrade( int $id ): void {
		$post = get_post( $id );
		if ( ! $post || Campaigns::TYPE !== $post->post_type || Campaigns::WORKPLACE_SLUG !== $post->post_name || self::VERSION === get_post_meta( $id, self::MARKER, true ) ) {
			return;
		}
		$original = require dirname( __DIR__ ) . '/campaign-workplace-charging.php';
		$approved = require dirname( __DIR__ ) . '/campaign-workplace-content.php';
		$changes  = array();
		foreach ( $approved as $key => $value ) {
			$name = 'greendawn_campaign_' . $key;
			if ( ! metadata_exists( 'post', $id, $name ) || ( isset( $original[ $key ] ) && get_post_meta( $id, $name, true ) === $original[ $key ] ) ) {
				$changes[ $key ] = $value;
			}
		}
		// Preserve edited legacy answers in the new FAQ list before retiring their old inputs.
		if ( isset( $changes['faqs'] ) ) {
			foreach ( array(
				'faq'     => 'active',
				'voucher' => 'always',
			) as $prefix => $visibility ) {
				$question = (string) get_post_meta( $id, 'greendawn_campaign_' . $prefix . '_question', true );
				$answer   = (string) get_post_meta( $id, 'greendawn_campaign_' . $prefix . '_answer', true );
				if ( $question && $answer && ( $question !== $original[ $prefix . '_question' ] || $answer !== $original[ $prefix . '_answer' ] ) ) {
					if ( 'voucher' === $prefix ) {
						$changes['faqs'][1] = array(
							'question'   => $question,
							'answer'     => $answer,
							'visibility' => $visibility,
						);
					} else {
						$changes['faqs'][] = array(
							'question'   => $question,
							'answer'     => $answer,
							'visibility' => $visibility,
						);
					}
				}
			}
		}
		self::write( $id, $changes );
		update_post_meta( $id, self::MARKER, self::VERSION );
		Campaigns::refresh_public_cache();
	}

	/**
	 * Keep other campaigns' legacy questions editable without seeding Workplace copy.
	 *
	 * @param int $id Campaign ID.
	 */
	public static function upgrade_legacy_faqs( int $id ): void {
		if ( Campaigns::TYPE !== get_post_type( $id ) || metadata_exists( 'post', $id, 'greendawn_campaign_faqs' ) ) {
			return;
		}
		$rows = array();
		foreach ( array(
			'faq'     => 'active',
			'voucher' => 'always',
		) as $prefix => $visibility ) {
			$question = (string) get_post_meta( $id, 'greendawn_campaign_' . $prefix . '_question', true );
			$answer   = (string) get_post_meta( $id, 'greendawn_campaign_' . $prefix . '_answer', true );
			if ( $question && $answer ) {
				$rows[] = array(
					'question'   => $question,
					'answer'     => $answer,
					'visibility' => $visibility,
				);
			}
		}
		if ( $rows ) {
			self::write( $id, array( 'faqs' => $rows ) );
		}
	}

	/** Prepare an existing campaign's legacy FAQ editor before its first SCF save. */
	public static function maybe_upgrade_faqs(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- An authorised edit-screen migration, not a submitted action.
		$id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		if ( $id && current_user_can( 'edit_post', $id ) ) {
			self::upgrade_legacy_faqs( $id );
		}
	}

	/** Apply the one-time content migration only in an authorised administrator session. */
	public static function maybe_upgrade(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$post = get_page_by_path( Campaigns::WORKPLACE_SLUG, OBJECT, array( Campaigns::TYPE ) );
		if ( $post && 'trash' !== $post->post_status && current_user_can( 'edit_post', $post->ID ) ) {
			self::upgrade( $post->ID );
		}
	}
}
