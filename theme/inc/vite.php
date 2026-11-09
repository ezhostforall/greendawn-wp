<?php
/**
 * Vite manifest helpers.
 *
 * The functions here that take a manifest array are pure so they can be unit-tested
 * without WordPress (see tests/php).
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Maps entry names to their source path inside theme/ (keys of the Vite manifest).
 * Keep in sync with build.rollupOptions.input in vite.config.ts.
 *
 * @return array<string,string>
 */
function greendawn_entries(): array {
	return (array) apply_filters(
		'greendawn_entries',
		array(
			'main'                  => 'src/scripts/main.ts',
			'editor'                => 'src/styles/editor.css',
			'react-example'         => 'src/react/index.tsx',
			'enquiry'               => 'src/enquiry/launcher.ts',
			'greendawn-home-motion' => 'src/scripts/home/loader.ts',
			'greendawn-homepage'    => 'src/styles/greendawn-homepage.css',
			'greendawn-pages'       => 'src/scripts/greendawn-pages.ts',
		)
	);
}

/**
 * Resolves an entry from a decoded Vite manifest.
 *
 * @param array<string,mixed> $manifest Decoded manifest.json.
 * @param string              $source   Manifest key, e.g. src/scripts/main.ts.
 * @return array{file:string,css:string[],imports:string[]}|null Null if the entry is absent.
 */
function greendawn_resolve_manifest_entry( array $manifest, string $source ): ?array {
	if ( ! isset( $manifest[ $source ]['file'] ) ) {
		return null;
	}

	$css     = array();
	$imports = array();
	$seen    = array();
	$queue   = array( $source );

	while ( $queue ) {
		$key = array_shift( $queue );
		if ( isset( $seen[ $key ] ) || ! isset( $manifest[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;
		foreach ( (array) ( $manifest[ $key ]['css'] ?? array() ) as $file ) {
			$css[] = $file;
		}
		foreach ( (array) ( $manifest[ $key ]['imports'] ?? array() ) as $import ) {
			if ( isset( $manifest[ $import ]['file'] ) ) {
				$imports[] = $manifest[ $import ]['file'];
			}
			$queue[] = $import;
		}
	}

	// A CSS-only entry (e.g. editor.css) is itself the stylesheet.
	if ( str_ends_with( $manifest[ $source ]['file'], '.css' ) ) {
		$css[] = $manifest[ $source ]['file'];
	}

	return array(
		'file'    => $manifest[ $source ]['file'],
		'css'     => array_values( array_unique( $css ) ),
		'imports' => array_values( array_unique( $imports ) ),
	);
}

/**
 * Loads the production manifest (cached per request).
 *
 * @return array<string,mixed> Empty when the theme has not been built.
 */
function greendawn_manifest(): array {
	static $manifest = null;
	if ( null === $manifest ) {
		$path     = GREENDAWN_DIR . '/dist/manifest.json';
		$decoded  = is_readable( $path ) ? wp_json_file_decode( $path, array( 'associative' => true ) ) : null;
		$manifest = is_array( $decoded ) ? $decoded : array();
	}
	return $manifest;
}

/**
 * Returns the Vite dev server origin when it is running, otherwise null.
 *
 * The dev server writes theme/.vite-hot (see vite.config.ts). It is only honoured on
 * local/development environments so a stray file can never affect production.
 */
function greendawn_dev_server_url(): ?string {
	static $url = false;
	if ( false === $url ) {
		$url  = null;
		$file = GREENDAWN_DIR . '/.vite-hot';
		if ( in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) && is_readable( $file ) ) {
			$candidate = untrailingslashit( trim( (string) file_get_contents( $file ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
			if ( preg_match( '#^https?://#', $candidate ) ) {
				$url = esc_url_raw( $candidate );
			}
		}
	}
	return $url;
}

/**
 * CSS file paths (relative to dist/) of a named entry, for production builds.
 *
 * @param string $name Entry name from greendawn_entries().
 * @return string[]
 */
function greendawn_entry_css_paths( string $name ): array {
	$entries = greendawn_entries();
	if ( ! isset( $entries[ $name ] ) ) {
		return array();
	}
	$resolved = greendawn_resolve_manifest_entry( greendawn_manifest(), $entries[ $name ] );
	return $resolved ? $resolved['css'] : array();
}
