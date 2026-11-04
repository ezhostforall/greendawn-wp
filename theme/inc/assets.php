<?php
/**
 * Asset enqueueing.
 *
 * `main` is loaded on every page. Everything else is opt-in per page by calling
 * wp_starter_enqueue_entry() (e.g. from a template or template part), or by returning
 * extra entry names from the `wp_starter_page_entries` filter.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues a Vite entry by name, from the dev server or the production manifest.
 *
 * @param string $name Entry name from wp_starter_entries().
 */
function wp_starter_enqueue_entry( string $name ): void {
	$entries = wp_starter_entries();
	if ( ! isset( $entries[ $name ] ) ) {
		return;
	}
	$handle = 'wp-starter-' . $name;
	$dev    = wp_starter_dev_server_url();

	if ( $dev ) {
		static $client_loaded = false;
		if ( ! $client_loaded ) {
			$client_loaded = true;
			wp_enqueue_script( 'wp-starter-vite-client', $dev . '/@vite/client', array(), null, array( 'in_footer' => true ) );
			wp_script_add_data( 'wp-starter-vite-client', 'wp_starter_module', true );
		}
		if ( 'react-example' === $name ) {
			add_action( 'wp_footer', 'wp_starter_print_react_refresh_preamble', 5 );
		}
		wp_enqueue_script( $handle, $dev . '/' . $entries[ $name ], array(), null, array( 'in_footer' => true ) );
		wp_script_add_data( $handle, 'wp_starter_module', true );
		return;
	}

	$resolved = wp_starter_resolve_manifest_entry( wp_starter_manifest(), $entries[ $name ] );
	if ( ! $resolved ) {
		return; // Not built yet.
	}

	$base = get_template_directory_uri() . '/dist/';
	foreach ( $resolved['css'] as $index => $css ) {
		wp_enqueue_style( $handle . '-' . $index, $base . $css, array(), null );
	}
	if ( ! str_ends_with( $resolved['file'], '.css' ) ) {
		wp_enqueue_script( $handle, $base . $resolved['file'], array(), null, array( 'in_footer' => true ) );
		wp_script_add_data( $handle, 'wp_starter_module', true );
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
function wp_starter_enqueue_assets(): void {
	wp_starter_enqueue_entry( 'main' );

	foreach ( (array) apply_filters( 'wp_starter_page_entries', array() ) as $name ) {
		wp_starter_enqueue_entry( (string) $name );
	}
}
add_action( 'wp_enqueue_scripts', 'wp_starter_enqueue_assets' );

/**
 * Marks Vite scripts as ES modules.
 *
 * @param string $tag    Script tag.
 * @param string $handle Script handle.
 * @return string
 */
function wp_starter_module_script_tag( string $tag, string $handle ): string {
	if ( wp_scripts()->get_data( $handle, 'wp_starter_module' ) ) {
		return preg_replace( '/<script /', '<script type="module" ', $tag, 1 ) ?? $tag;
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'wp_starter_module_script_tag', 10, 2 );

/**
 * React Fast Refresh preamble, required by @vitejs/plugin-react in dev mode only.
 */
function wp_starter_print_react_refresh_preamble(): void {
	$dev = wp_starter_dev_server_url();
	if ( ! $dev ) {
		return;
	}
	printf(
		'<script type="module">import RefreshRuntime from %s;RefreshRuntime.injectIntoGlobalHook(window);window.$RefreshReg$=()=>{};window.$RefreshSig$=()=>(type)=>type;window.__vite_plugin_react_preamble_installed__=true;</script>' . "\n", // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Value is JSON-encoded.
		wp_json_encode( $dev . '/@react-refresh' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}
