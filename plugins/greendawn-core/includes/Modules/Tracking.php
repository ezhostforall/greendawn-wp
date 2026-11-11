<?php
/**
 * Explicit environment configuration for the first-party tracking bridge.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/** Exposes a single container configuration for the matching frontend host. */
final class Tracking {
	/** Registers the frontend configuration output. */
	public function register(): void {
		add_action( 'wp_head', array( $this, 'render_config' ), 1 );
	}

	/** Prints inert settings. The browser bridge loads GTM after an optional grant. */
	public function render_config(): void {
		$environment = wp_get_environment_type();
		$config      = defined( 'GREENDAWN_CORE_TRACKING' ) ? GREENDAWN_CORE_TRACKING : array();
		if ( ! is_array( $config ) || ! in_array( $environment, array( 'production', 'staging' ), true ) ) {
			return;
		}

		$site       = $config[ $environment ] ?? array();
		$production = $config['production'] ?? array();
		$host       = is_array( $site ) ? ( $site['host'] ?? '' ) : '';
		$container  = is_array( $site ) ? ( $site['container'] ?? '' ) : '';
		if ( ! is_string( $host ) || '' === $host || wp_parse_url( home_url(), PHP_URL_HOST ) !== $host
			|| ! is_string( $container ) || ! preg_match( '/^GTM-[A-Z0-9]+$/D', $container )
			|| ( 'staging' === $environment && ( ! is_array( $production ) || empty( $production['host'] ) || empty( $production['container'] )
				|| $host === $production['host'] || $container === $production['container'] ) ) ) {
			return;
		}

		printf(
			'<meta name="greendawn-tracking" content="%1$s" data-host="%2$s" data-production="%3$s">' . "\n",
			esc_attr( $container ),
			esc_attr( $host ),
			'production' === $environment ? '1' : '0'
		);
	}
}
