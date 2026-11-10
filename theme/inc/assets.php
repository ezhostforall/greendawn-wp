<?php
/**
 * Asset enqueueing.
 *
 * `main` is loaded on every page. Everything else is opt-in per page by calling
 * greendawn_enqueue_entry() (e.g. from a template or template part), or by returning
 * extra entry names from the `greendawn_page_entries` filter.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues a Vite entry by name, from the dev server or the production manifest.
 *
 * @param string $name Entry name from greendawn_entries().
 */
function greendawn_enqueue_entry( string $name ): void {
	$entries = greendawn_entries();
	if ( ! isset( $entries[ $name ] ) ) {
		return;
	}
	$handle = 'greendawn-' . $name;
	$dev    = greendawn_dev_server_url();

	if ( $dev ) {
		if ( str_ends_with( $entries[ $name ], '.css' ) ) {
			wp_enqueue_style( $handle, $dev . '/' . $entries[ $name ], array(), null );
			return;
		}
		static $client_loaded = false;
		if ( ! $client_loaded ) {
			$client_loaded = true;
			wp_enqueue_script( 'greendawn-vite-client', $dev . '/@vite/client', array(), null, array( 'in_footer' => true ) );
			wp_script_add_data( 'greendawn-vite-client', 'greendawn_module', true );
		}
		if ( in_array( $name, array( 'react-example', 'enquiry' ), true ) ) {
			add_action( 'wp_footer', 'greendawn_print_react_refresh_preamble', 5 );
		}
		wp_enqueue_script( $handle, $dev . '/' . $entries[ $name ], array(), null, array( 'in_footer' => true ) );
		wp_script_add_data( $handle, 'greendawn_module', true );
		return;
	}

	$resolved = greendawn_resolve_manifest_entry( greendawn_manifest(), $entries[ $name ] );
	if ( ! $resolved ) {
		return; // Not built yet.
	}

	$base = get_template_directory_uri() . '/dist/';
	foreach ( $resolved['css'] as $index => $css ) {
		wp_enqueue_style( $handle . '-' . $index, $base . $css, array(), null );
	}
	if ( ! str_ends_with( $resolved['file'], '.css' ) ) {
		wp_enqueue_script( $handle, $base . $resolved['file'], array(), null, array( 'in_footer' => true ) );
		wp_script_add_data( $handle, 'greendawn_module', true );
		foreach ( $resolved['imports'] as $import ) {
			add_action(
				'wp_head',
				static function () use ( $base, $import ): void {
					printf( '<link rel="modulepreload" href="%s">' . "\n", esc_url( $base . $import ) );
				}
			);
		}
	}
}

/**
 * Enqueues theme assets for the front end.
 */
function greendawn_enqueue_assets(): void {
	greendawn_enqueue_entry( 'main' );
	if ( is_page_template( 'templates/greendawn-homepage.php' ) ) {
		greendawn_enqueue_entry( 'greendawn-homepage' );
		greendawn_enqueue_entry( 'greendawn-home-motion' );
	}

	if ( is_page_template( array( 'templates/greendawn-about.php', 'templates/greendawn-workplace.php', 'templates/greendawn-fleet.php' ) ) || is_singular( 'evcharger' ) ) {
		greendawn_enqueue_entry( 'greendawn-pages' );
	}

	foreach ( (array) apply_filters( 'greendawn_page_entries', array() ) as $name ) {
		greendawn_enqueue_entry( (string) $name );
	}
}
add_action( 'wp_enqueue_scripts', 'greendawn_enqueue_assets' );

/**
 * Marks Vite scripts as ES modules.
 *
 * @param string $tag    Script tag.
 * @param string $handle Script handle.
 * @return string
 */
function greendawn_module_script_tag( string $tag, string $handle ): string {
	if ( wp_scripts()->get_data( $handle, 'greendawn_module' ) ) {
		return preg_replace( '/<script /', '<script type="module" ', $tag, 1 ) ?? $tag;
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'greendawn_module_script_tag', 10, 2 );

/**
 * React Fast Refresh preamble, required by @vitejs/plugin-react in dev mode only.
 */
function greendawn_print_react_refresh_preamble(): void {
	$dev = greendawn_dev_server_url();
	if ( ! $dev ) {
		return;
	}
	printf(
		'<script type="module">import RefreshRuntime from %s;RefreshRuntime.injectIntoGlobalHook(window);window.$RefreshReg$=()=>{};window.$RefreshSig$=()=>(type)=>type;window.__vite_plugin_react_preamble_installed__=true;</script>' . "\n", // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Value is JSON-encoded.
		wp_json_encode( $dev . '/@react-refresh' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}
