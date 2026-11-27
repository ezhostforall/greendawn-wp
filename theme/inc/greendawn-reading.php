<?php
/**
 * Presentation adapters for shared reading components. Core owns saved content.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Scalar component arguments must never become array-to-string notices.
 *
 * @param mixed $value Candidate copy.
 */
function greendawn_component_text( $value ): string {
	return is_scalar( $value ) ? trim( (string) $value ) : '';
}

/**
 * Resolve the public Core adapter without requiring Core or SCF to be installed.
 *
 * @param int $id Guide ID.
 */
function greendawn_guide_view( int $id ): array {
	return class_exists( '\Greendawn_Core\Modules\Guide_Content' ) ? \Greendawn_Core\Modules\Guide_Content::public_data( $id ) : array();
}

/**
 * One card record for published Posts or Guides; selected order belongs to callers.
 *
 * @param WP_Post $post Candidate article.
 */
function greendawn_article_card_data( WP_Post $post ): array {
	if ( 'publish' !== $post->post_status || '' !== $post->post_password ) {
		return array();
	}
	if ( 'gdguide' === $post->post_type ) {
		$data = greendawn_guide_view( $post->ID );
		if ( ! $data ) {
			return array();
		}
		$topic = get_term( $data['primary_topic'], 'gdguide_topic' );
		return array(
			'post_id'         => $post->ID,
			'title'           => $data['title'],
			'url'             => $data['url'],
			'image_id'        => $data['image'],
			'image_position'  => $data['image_position'],
			'summary'         => $data['intro'],
			'topic'           => $topic instanceof WP_Term ? $topic->name : '',
			'reading_minutes' => $data['reading_minutes'],
			'variant'         => 'guide',
		);
	}
	return 'post' === $post->post_type ? array(
		'post_id'    => $post->ID,
		'title'      => get_the_title( $post ),
		'url'        => get_permalink( $post ),
		'image_id'   => get_post_thumbnail_id( $post ),
		'show_label' => false,
	) : array();
}

/**
 * Crop positions are controlled class variants, never arbitrary editor CSS.
 *
 * @param mixed $position Saved focal point.
 */
function greendawn_image_position_class( $position ): string {
	return in_array( $position, array( 'centre', 'upper', 'lower', 'left', 'right' ), true ) ? ' gd-image-position--' . $position : '';
}

/**
 * Fixed decorative paths shared by icons and checklists; unknown names are omitted.
 *
 * @param mixed $name Approved icon name.
 */
function greendawn_component_icon( $name ): string {
	$paths = array(
		'bolt'     => '<path d="m13 2-9 12h7l-1 8 10-13h-8z"/>',
		'site'     => '<path d="M3 21V8l9-5 9 5v13M3 12h18M8 21v-5h8v5M7 8h1m4 0h1m4 0h1"/>',
		'fleet'    => '<path d="M3 17V6h12v11H3Zm12-7h4l3 4v3h-7M3 13h12"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
		'property' => '<path d="M4 22V3h12v19M16 10h5v12M8 7h4M8 11h4M8 15h4M9 22v-3h3v3"/>',
		'pin'      => '<path d="M19 10c0 5-7 12-7 12S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/>',
		'check'    => '<path d="m5 12 4 4L20 5"/>',
	);
	return is_string( $name ) && isset( $paths[ $name ] ) ? '<svg class="gd-component-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>' : '';
}

/**
 * Optional enquiry action uses the existing drawer launcher and a real fallback URL.
 * Intent is a validated markup contract; PR4 will connect intent preselection.
 *
 * @param array  $args Component/CTA arguments.
 * @param string $default_point Existing drawer entry-point fallback.
 */
function greendawn_component_cta( array $args, string $default_point = 'final-cta' ): array {
	$action = 'enquiry' === ( $args['action'] ?? '' );
	$url    = greendawn_component_text( $args['url'] ?? '' );
	if ( $action ) {
		$url = esc_url_raw( $url, array( 'http', 'https' ) );
	}
	if ( $action && ( '' === $url || '#' === $url ) ) {
		$url = esc_url_raw( greendawn_component_text( get_theme_mod( 'greendawn_header_cta_url', '' ) ), array( 'http', 'https' ) );
		$url = '' !== $url && '#' !== $url ? $url : home_url( '/contact/' );
	}
	$point  = $args['entry_point'] ?? $default_point;
	$point  = in_array( $point, array( 'hero', 'services', 'pricing', 'final-cta', 'footer-cta', 'header' ), true ) ? $point : $default_point;
	$intent = $args['intent'] ?? '';
	$intent = in_array( $intent, array( 'ev-charging', 'power', 'solar-battery', 'aftercare', 'unsure' ), true ) ? $intent : '';
	return array(
		'url'        => $url,
		'label'      => greendawn_component_text( $args['label'] ?? '' ),
		'attributes' => $action ? ' data-lead-capture-open data-lead-entry-point="' . esc_attr( $point ) . '"' . ( $intent ? ' data-lead-intent="' . esc_attr( $intent ) . '"' : '' ) : '',
	);
}

/**
 * Format only genuine valid past/present dates. No implicit date is fabricated.
 *
 * @param mixed $value YYYYMMDD date.
 */
function greendawn_editorial_date( $value ): array {
	$value = greendawn_component_text( $value );
	if ( ! preg_match( '/^[0-9]{8}$/', $value ) || ! checkdate( (int) substr( $value, 4, 2 ), (int) substr( $value, 6, 2 ), (int) substr( $value, 0, 4 ) ) || $value > current_time( 'Ymd' ) ) {
		return array();
	}
	$date = DateTimeImmutable::createFromFormat( '!Ymd', $value, wp_timezone() );
	return $date ? array(
		'iso'   => $date->format( 'Y-m-d' ),
		'label' => wp_date( get_option( 'date_format' ), $date->getTimestamp(), wp_timezone() ),
	) : array();
}

/**
 * Prepare unique, deterministic anchors and constrained presentation rows once.
 * Pass the same result to reading-contents and reading-sections.
 *
 * @param array  $rows Saved sections or previously prepared rows.
 * @param string $prefix Instance prefix for multiple reading compositions on one page.
 */
function greendawn_reading_sections( array $rows, string $prefix = 'guide' ): array {
	$sections = array();
	$used     = array();
	$prefix   = sanitize_html_class( $prefix, 'guide' );
	foreach ( array_slice( $rows, 0, 60 ) as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$heading = greendawn_component_text( $row['heading'] ?? '' );
		if ( '' === $heading ) {
			continue;
		}
		$slug = sanitize_html_class( sanitize_title( $heading ), 'section' );
		$base = sanitize_html_class( greendawn_component_text( $row['id'] ?? '' ), $prefix . '-' . $slug );
		$id   = $base;
		for ( $suffix = 2; isset( $used[ $id ] ) || isset( $used[ $id . '-title' ] ) || isset( $used[ $id . '-table-caption' ] ); ++$suffix ) {
			$id = $base . '-' . $suffix;
		}
		$used[ $id ]                    = true;
		$used[ $id . '-title' ]         = true;
		$used[ $id . '-table-caption' ] = true;
		$section                        = array(
			'id'            => $id,
			'heading'       => $heading,
			'nav_label'     => greendawn_component_text( $row['nav_label'] ?? '' ),
			'body'          => greendawn_component_text( $row['body'] ?? '' ),
			'list_style'    => in_array( $row['list_style'] ?? '', array( 'plain', 'numbered', 'checklist' ), true ) ? $row['list_style'] : 'plain',
			'list_items'    => array(),
			'columns'       => array(),
			'table_rows'    => array(),
			'table_caption' => greendawn_component_text( $row['table_caption'] ?? '' ),
			'image'         => is_scalar( $row['image'] ?? null ) ? absint( $row['image'] ) : 0,
			'caption'       => greendawn_component_text( $row['caption'] ?? '' ),
		);
		foreach ( is_array( $row['list_items'] ?? null ) ? array_slice( $row['list_items'], 0, 100 ) : array() as $item ) {
			$text = greendawn_component_text( is_array( $item ) ? $item['text'] ?? '' : $item );
			if ( '' !== $text ) {
				$section['list_items'][] = array( 'text' => $text );
			}
		}
		$columns = is_array( $row['columns'] ?? null ) ? $row['columns'] : array();
		$valid   = count( $columns ) > 0 && count( $columns ) <= 4 && '' !== $section['table_caption'];
		foreach ( $columns as $column ) {
			$label                = greendawn_component_text( is_array( $column ) ? $column['label'] ?? '' : '' );
			$valid                = $valid && '' !== $label;
			$section['columns'][] = array( 'label' => $label );
		}
		foreach ( is_array( $row['table_rows'] ?? null ) ? array_slice( $row['table_rows'], 0, 100 ) : array() as $table_row ) {
			$cells                   = is_array( $table_row ) && is_array( $table_row['cells'] ?? null ) ? $table_row['cells'] : array();
			$valid                   = $valid && count( $cells ) === count( $columns );
			$section['table_rows'][] = array( 'cells' => array_map( static fn( $cell ): array => array( 'text' => greendawn_component_text( is_array( $cell ) ? $cell['text'] ?? '' : '' ) ), $cells ) );
		}
		if ( ! $valid || ! $section['table_rows'] ) {
			$section['columns']    = array();
			$section['table_rows'] = array();
		}
		$sections[] = $section;
	}
	return $sections;
}
