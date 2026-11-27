<?php
/**
 * Native SCF storage reader and validated editorial content service.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/** Reads saved content without SCF; contains no rendering or SEO output. */
final class Guide_Content {

	public const AUTHOR       = 'Greendawn Power Limited';
	public const SEARCH_META  = '_greendawn_guide_search_text';
	public const MINUTES_META = '_greendawn_guide_reading_minutes';

	/**
	 * Only Guides and explicitly assigned structured Page templates are managed.
	 *
	 * @param int $id Record ID.
	 */
	public static function kind( int $id ): string {
		if ( Guides::TYPE === get_post_type( $id ) ) {
			return 'guide';
		}
		if ( 'page' !== get_post_type( $id ) ) {
			return '';
		}
		$kind = array_search( get_page_template_slug( $id ), Guides::TEMPLATES, true );
		return false === $kind ? '' : $kind;
	}

	/**
	 * Normalise a field without assuming trusted SCF editor input.
	 *
	 * @param mixed $value Saved or submitted value.
	 * @param array $field Canonical field definition.
	 * @return mixed
	 */
	public static function normalise( $value, array $field ) {
		$type = $field['type'];
		if ( 'repeater' === $type ) {
			$rows = array();
			if ( ! is_array( $value ) ) {
				return $rows;
			}
			unset( $value['acfcloneindex'] );
			foreach ( array_slice( array_values( $value ), 0, $field['max'] ) as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$cells = array();
				foreach ( $field['sub_fields'] as $child ) {
					$cells[ $child['name'] ] = self::normalise( $row[ $child['key'] ] ?? $row[ $child['name'] ] ?? null, $child );
				}
				$rows[] = $cells;
			}
			return $rows;
		}
		if ( 'relationship' === $type || ( 'taxonomy' === $type && ! empty( $field['multiple'] ) ) ) {
			return is_array( $value ) ? array_slice( array_values( array_unique( array_filter( array_map( 'absint', array_filter( $value, 'is_scalar' ) ) ) ) ), 0, $field['max'] ?? 100 ) : array();
		}
		if ( in_array( $type, array( 'image', 'post_object', 'taxonomy' ), true ) ) {
			return is_scalar( $value ) && ctype_digit( (string) $value ) ? absint( $value ) : 0;
		}
		$value = is_scalar( $value ) ? (string) $value : '';
		if ( 'select' === $type ) {
			return isset( $field['choices'][ $value ] ) ? $value : '';
		}
		if ( 'url' === $type ) {
			return esc_url_raw( $value, array( 'http', 'https' ) );
		}
		if ( 'date_picker' === $type ) {
			return self::valid_date( $value ) ? $value : '';
		}
		return 'textarea' === $type ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
	}

	/**
	 * Dates are real past/present YYYYMMDD values; blank never means today.
	 *
	 * @param string $value Date value.
	 */
	public static function valid_date( string $value ): bool {
		return (bool) preg_match( '/^[0-9]{8}$/', $value ) && checkdate( (int) substr( $value, 4, 2 ), (int) substr( $value, 6, 2 ), (int) substr( $value, 0, 4 ) ) && $value <= current_time( 'Ymd' );
	}

	/**
	 * Read SCF's flattened repeater layout recursively, preserving intentional blanks.
	 *
	 * @param int    $id Record ID.
	 * @param array  $field Canonical field definition.
	 * @param string $name Native metadata name.
	 * @return mixed
	 */
	private static function read_field( int $id, array $field, string $name ) {
		$value = get_post_meta( $id, $name, true );
		if ( 'repeater' !== $field['type'] ) {
			return self::normalise( $value, $field );
		}
		$rows  = array();
		$count = is_scalar( $value ) ? min( $field['max'], absint( $value ) ) : 0;
		for ( $index = 0; $index < $count; ++$index ) {
			$row = array();
			foreach ( $field['sub_fields'] as $child ) {
				$row[ $child['name'] ] = self::read_field( $id, $child, $name . '_' . $index . '_' . $child['name'] );
			}
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * Internal raw content reader. Public adapters must use public_data() for Guides.
	 *
	 * @param int $id Record ID.
	 */
	public static function read( int $id ): array {
		$kind = self::kind( $id );
		$data = array();
		foreach ( Guide_Fields::fields( $kind ) as $field ) {
			$data[ substr( $field['name'], strlen( Guide_Fields::PREFIXES[ $kind ] ) ) ] = self::read_field( $id, $field, $field['name'] );
		}
		return $data;
	}

	/**
	 * Resolve public records without leaking private, draft or password-protected copy.
	 *
	 * @param int $id Guide ID.
	 */
	public static function public_data( int $id ): array {
		$post = get_post( $id );
		if ( ! $post || Guides::TYPE !== $post->post_type || 'publish' !== $post->post_status || '' !== $post->post_password ) {
			return array();
		}
		$data            = self::read( $id );
		$data['related'] = array_values(
			array_filter(
				$data['related'],
				static function ( int $related ) use ( $id ): bool {
					$post = get_post( $related );
					return $related !== $id && $post && Guides::TYPE === $post->post_type && 'publish' === $post->post_status && '' === $post->post_password;
				}
			)
		);
		return array_merge(
			$data,
			array(
				'id'              => $id,
				'title'           => get_the_title( $id ),
				'url'             => get_permalink( $id ),
				'author'          => self::AUTHOR,
				'reading_minutes' => self::reading_minutes( $data ),
				'schema_version'  => Guide_Fields::VERSION,
			)
		);
	}

	/**
	 * Human-readable body content only; no IDs, URLs, style choices or private notes.
	 *
	 * @param array $data Normalised short-name fields.
	 */
	public static function reading_text( array $data ): string {
		$text = array( $data['intro'] ?? '', $data['answer'] ?? '' );
		foreach ( $data['sections'] ?? array() as $section ) {
			$text[] = $section['heading'];
			$text[] = $section['body'];
			$text[] = $section['table_caption'];
			$text[] = $section['caption'];
			foreach ( $section['list_items'] as $item ) {
				$text[] = $item['text'];
			}
			foreach ( $section['columns'] as $column ) {
				$text[] = $column['label'];
			}
			foreach ( $section['table_rows'] as $row ) {
				foreach ( $row['cells'] as $cell ) {
					$text[] = $cell['text'];
				}
			}
		}
		foreach ( $data['faqs'] ?? array() as $faq ) {
			$text[] = $faq['question'];
			$text[] = $faq['answer'];
		}
		return trim( implode( "\n", array_filter( $text ) ) );
	}

	/**
	 * Estimate from saved reading copy, never from empty native post_content.
	 *
	 * @param array $data Normalised Guide fields.
	 */
	public static function reading_minutes( array $data ): int {
		$words = preg_split( '/[\s\p{Z}]+/u', self::reading_text( $data ), -1, PREG_SPLIT_NO_EMPTY );
		return $words ? max( 1, (int) ceil( count( $words ) / 200 ) ) : 0;
	}

	/**
	 * Rebuild derived metadata after editor saves, imports, restores or native writes.
	 *
	 * @param int $id Guide ID.
	 */
	public static function rebuild( int $id ): void {
		if ( 'guide' !== self::kind( $id ) ) {
			return;
		}
		$data  = self::read( $id );
		$text  = get_post_field( 'post_title', $id, 'raw' ) . "\n" . self::reading_text( $data );
		$terms = wp_get_object_terms( $id, Guides::TAXONOMY, array( 'fields' => 'names' ) );
		if ( ! is_wp_error( $terms ) ) {
			$text .= "\n" . implode( "\n", $terms );
		}
		update_post_meta( $id, self::SEARCH_META, wp_slash( sanitize_textarea_field( $text ) ) );
		update_post_meta( $id, self::MINUTES_META, self::reading_minutes( $data ) );
		update_post_meta( $id, '_greendawn_guide_schema_version', Guide_Fields::VERSION );
	}

	/**
	 * Validate raw values rather than silently accepting values normalisation discards.
	 *
	 * @param mixed  $value Input value.
	 * @param array  $field Field definition.
	 * @param string $path Human-readable error location.
	 * @return string[]
	 */
	public static function field_errors( $value, array $field, string $path = '' ): array {
		$path   = $path ? $path : $field['label'];
		$errors = array();
		if ( 'repeater' === $field['type'] ) {
			$value = false === $value || null === $value || '' === $value ? array() : $value;
			if ( is_array( $value ) ) {
				unset( $value['acfcloneindex'] );
			}
			if ( ! is_array( $value ) || count( $value ) > $field['max'] ) {
				return array( $path . ': ' . __( 'Use a valid list within the row limit.', 'greendawn' ) );
			}
			foreach ( array_values( $value ) as $index => $row ) {
				if ( ! is_array( $row ) ) {
					$errors[] = $path . ': ' . __( 'Invalid row.', 'greendawn' );
					continue;
				}
				foreach ( $field['sub_fields'] as $child ) {
					$cell   = $row[ $child['key'] ] ?? $row[ $child['name'] ] ?? ( 'repeater' === $child['type'] ? array() : '' );
					$errors = array_merge( $errors, self::field_errors( $cell, $child, $path . ' / ' . ( $index + 1 ) . ' / ' . $child['label'] ) );
				}
			}
			return $errors;
		}
		if ( 'relationship' === $field['type'] || ( 'taxonomy' === $field['type'] && ! empty( $field['multiple'] ) ) ) {
			$value = false === $value || null === $value || '' === $value ? array() : $value;
			if ( ! is_array( $value ) || count( $value ) > ( $field['max'] ?? 100 ) ) {
				return array( $path . ': ' . __( 'Choose a valid list of records.', 'greendawn' ) );
			}
			foreach ( $value as $item ) {
				$single = array_merge(
					$field,
					array(
						'type'     => 'relationship' === $field['type'] ? 'post_object' : 'taxonomy',
						'multiple' => 0,
					)
				);
				$errors = array_merge( $errors, self::field_errors( $item, $single, $path ) );
			}
			return $errors;
		}
		if ( ! is_scalar( $value ) && null !== $value ) {
			return array( $path . ': ' . __( 'Invalid field value.', 'greendawn' ) );
		}
		$value = (string) $value;
		if ( '' === $value || ( '0' === $value && in_array( $field['type'], array( 'image', 'post_object', 'taxonomy' ), true ) ) ) {
			return array();
		}
		$valid = true;
		switch ( $field['type'] ) {
			case 'date_picker':
				$valid = self::valid_date( $value );
				break;
			case 'url':
				$valid = (bool) filter_var( $value, FILTER_VALIDATE_URL ) && in_array( wp_parse_url( $value, PHP_URL_SCHEME ), array( 'http', 'https' ), true );
				break;
			case 'select':
				$valid = isset( $field['choices'][ $value ] );
				break;
			case 'image':
				$valid = ctype_digit( $value ) && wp_attachment_is_image( (int) $value );
				break;
			case 'taxonomy':
				$valid = ctype_digit( $value ) && (bool) term_exists( (int) $value, Guides::TAXONOMY );
				break;
			case 'post_object':
				$valid = ctype_digit( $value ) && (bool) self::public_data( (int) $value );
				break;
		}
		return $valid ? array() : array( $path . ': ' . __( 'Use an available record, permitted choice, HTTP(S) URL or genuine past/present date.', 'greendawn' ) );
	}

	/**
	 * Validate semantic content and publication requirements across the full record.
	 *
	 * @param int   $id Record ID.
	 * @param array $data Short-name field values (merged with saved values by caller).
	 * @param bool  $publish Require complete public content.
	 * @param array $topics Assigned/pending topic IDs.
	 * @return string[]
	 */
	public static function errors( int $id, array $data, bool $publish = false, array $topics = array() ): array {
		$kind   = self::kind( $id );
		$errors = array();
		if ( ! $kind ) {
			return array( __( 'This record does not use a Guides content model.', 'greendawn' ) );
		}
		foreach ( Guide_Fields::fields( $kind ) as $field ) {
			$key = substr( $field['name'], strlen( Guide_Fields::PREFIXES[ $kind ] ) );
			if ( array_key_exists( $key, $data ) ) {
				$errors       = array_merge( $errors, self::field_errors( $data[ $key ], $field ) );
				$data[ $key ] = self::normalise( $data[ $key ], $field );
			}
		}
		if ( $errors ) {
			return $errors;
		}
		if ( 'guide' === $kind ) {
			foreach ( $data['sections'] ?? array() as $section ) {
				if ( ! is_array( $section ) ) {
					continue;
				}
				if ( ! empty( $section['columns'] ) || ! empty( $section['table_rows'] ) ) {
					$columns = count( $section['columns'] ?? array() );
					if ( ! trim( $section['table_caption'] ?? '' ) || ! $columns || empty( $section['table_rows'] ) || in_array( '', array_column( $section['columns'] ?? array(), 'label' ), true ) ) {
						$errors[] = __( 'Tables need a caption, column headings and rows.', 'greendawn' );
					}
					foreach ( $section['table_rows'] ?? array() as $row ) {
						if ( ! is_array( $row ) || count( $row['cells'] ?? array() ) !== $columns ) {
							$errors[] = __( 'Each table row must match the number of columns.', 'greendawn' );
						}
					}
				}
			}
		}
		if ( ! $publish ) {
			return $errors;
		}
		$required = array(
			'guide'  => array( 'intro', 'answer', 'sections', 'primary_topic' ),
			'hub'    => array( 'heading', 'intro', 'search_label', 'empty_heading', 'empty_copy' ),
			'region' => array( 'name', 'heading', 'intro', 'considerations' ),
		);
		foreach ( $required[ $kind ] as $key ) {
			if ( empty( $data[ $key ] ) || ( is_string( $data[ $key ] ) && '' === trim( $data[ $key ] ) ) ) {
				$errors[] = sprintf( /* translators: %s: required field name. */ __( 'Complete %s before publishing.', 'greendawn' ), $key );
			}
		}
		if ( 'guide' === $kind ) {
			if ( ! $topics || ! in_array( (int) ( $data['primary_topic'] ?? 0 ), array_map( 'intval', $topics ), true ) ) {
				$errors[] = __( 'Assign a topic and choose an assigned primary topic before publishing.', 'greendawn' );
			}
			foreach ( $data['sections'] ?? array() as $section ) {
				if ( ! is_array( $section ) || empty( $section['heading'] ) || ( empty( $section['body'] ) && empty( $section['list_items'] ) && empty( $section['table_rows'] ) && empty( $section['image'] ) ) ) {
					$errors[] = __( 'Each reading section needs a heading and useful content.', 'greendawn' );
				}
			}
		}
		foreach ( array(
			'faqs'           => array( 'question', 'answer' ),
			'sources'        => array( 'title', 'url' ),
			'considerations' => array( 'heading', 'copy' ),
			'services'       => array( 'title', 'copy' ),
		) as $list => $keys ) {
			foreach ( $data[ $list ] ?? array() as $row ) {
				foreach ( $keys as $key ) {
					if ( empty( $row[ $key ] ) ) {
						$errors[] = __( 'Complete optional content rows or remove them before publishing.', 'greendawn' );
					}
				}
			}
		}
		if ( 'region' === $kind && ! empty( $data['proof_copy'] ) && empty( $data['proof_url'] ) ) {
			$errors[] = __( 'Project evidence needs a link to its published source.', 'greendawn' );
		}
		return array_values( array_unique( $errors ) );
	}

	/**
	 * Authorised API for future import/editor tools; errors never partially save fields.
	 *
	 * @param int   $id Record ID.
	 * @param array $values Short-name fields to replace; omitted fields are preserved.
	 * @return true|\WP_Error
	 */
	public static function write( int $id, array $values ) {
		$kind = self::kind( $id );
		if ( ! $kind || ! current_user_can( 'edit_post', $id ) || ! function_exists( 'update_field' ) ) {
			return new \WP_Error( 'greendawn_guide_write_denied', __( 'Editing requires permission and Secure Custom Fields.', 'greendawn' ) );
		}
		$fields = array();
		foreach ( Guide_Fields::fields( $kind ) as $field ) {
			$fields[ substr( $field['name'], strlen( Guide_Fields::PREFIXES[ $kind ] ) ) ] = $field;
		}
		if ( array_diff_key( $values, $fields ) ) {
			return new \WP_Error( 'greendawn_guide_unknown_fields', __( 'The payload contains unrecognised fields.', 'greendawn' ) );
		}
		$data   = array_replace( self::read( $id ), $values );
		$topics = wp_get_object_terms( $id, Guides::TAXONOMY, array( 'fields' => 'ids' ) );
		$errors = self::errors( $id, $data, in_array( get_post_status( $id ), array( 'publish', 'future', 'private' ), true ), is_wp_error( $topics ) ? array() : $topics );
		if ( $errors ) {
			return new \WP_Error( 'greendawn_guide_invalid_content', implode( ' ', $errors ) );
		}
		foreach ( $values as $key => $value ) {
			update_field( $fields[ $key ]['key'], wp_slash( self::normalise( $value, $fields[ $key ] ) ), $id );
		}
		Guides::commit( $id );
		return true;
	}
}
