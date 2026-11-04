<?php
/**
 * GitHub release updater. No credentials or source information are stored in WordPress.
 *
 * @package EZHost_Updater
 */

namespace EZHost_Updater;

defined( 'ABSPATH' ) || exit;

/**
 * UpdateURI provider for agency-managed components.
 */
final class Updater {
	const HOST         = 'updates.ezhostforall.invalid';
	const PACKAGE_BASE = 'https://updates.ezhostforall.invalid/package/';
	const MAX_PACKAGE  = 104857600;
	const MAX_METADATA = 524288;

	/**
	 * Register only native update hooks. No background jobs or dashboard UI.
	 */
	public static function register(): void {
		add_filter( 'update_plugins_' . self::HOST, array( __CLASS__, 'plugin_update' ), 10, 4 );
		add_filter( 'update_themes_' . self::HOST, array( __CLASS__, 'theme_update' ), 10, 4 );
		add_filter( 'upgrader_pre_download', array( __CLASS__, 'pre_download' ), 10, 4 );
		add_filter( 'auto_update_plugin', array( __CLASS__, 'prevent_plugin_auto_update' ), 10, 2 );
		add_filter( 'auto_update_theme', array( __CLASS__, 'prevent_theme_auto_update' ), 10, 2 );
	}

	/**
	 * Sources are set in wp-config.php; the keys are the installed component IDs.
	 *
	 * @return array<string,mixed>
	 */
	public static function sources(): array {
		return defined( 'EZHOST_UPDATE_SOURCES' ) && is_array( EZHOST_UPDATE_SOURCES ) ? EZHOST_UPDATE_SOURCES : array();
	}

	/**
	 * Return a validated mapping. Unconfigured targets are never intercepted.
	 *
	 * @param string $type theme or plugin.
	 * @param string $key  Theme stylesheet or plugin file.
	 * @return array<string,string>|null
	 */
	public static function source( string $type, string $key ): ?array {
		$sources = self::sources();
		$group   = 'theme' === $type ? 'themes' : 'plugins';
		$source  = $sources[ $group ][ $key ] ?? null;
		if ( ! is_array( $source ) || ! isset( $source['repo'] ) ) {
			return null;
		}
		if ( ! preg_match( '#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#D', (string) $source['repo'] ) ) {
			return null;
		}
		if ( 'theme' === $type && ! preg_match( '/^[a-z0-9-]+$/D', $key ) ) {
			return null;
		}
		if ( 'plugin' === $type && ! preg_match( '#^([a-z0-9-]+)/\1\.php$#D', $key ) ) {
			return null;
		}
		$token_env = isset( $source['token_env'] ) ? (string) $source['token_env'] : '';
		if ( '' !== $token_env && ! preg_match( '/^EZHOST_[A-Z0-9_]+$/D', $token_env ) ) {
			return null;
		}
		// Candidate tags are deliberately unavailable on production sites.
		$candidate_tag = isset( $source['candidate_tag'] ) ? (string) $source['candidate_tag'] : '';
		if ( '' !== $candidate_tag && ( ! preg_match( '/^v[0-9]+\.[0-9]+\.[0-9]+$/D', $candidate_tag ) || 'staging' !== wp_get_environment_type() ) ) {
			return null;
		}
		return array(
			'repo'          => (string) $source['repo'],
			'token_env'     => $token_env,
			'candidate_tag' => $candidate_tag,
		);
	}

	/**
	 * Read a token from wp-config constants or the server environment (never the DB).
	 *
	 * @param array<string,string> $source Valid source.
	 */
	private static function token( array $source ): string {
		$name = $source['token_env'];
		if ( ! $name ) {
			return '';
		}
		$token = defined( $name ) ? constant( $name ) : getenv( $name );
		return is_string( $token ) ? $token : '';
	}

	/**
	 * Get immutable release metadata and the component manifest. Cache successful reads.
	 *
	 * @param array<string,string> $source Source configuration.
	 * @return array<string,mixed>|null
	 */
	private static function release( array $source ): ?array {
		$key    = 'ezhost_updater_' . md5( $source['repo'] . ':' . $source['token_env'] . ':' . $source['candidate_tag'] );
		$cached = get_site_transient( $key );
		if ( is_array( $cached ) ) {
			return $cached;
		}
		$url  = 'https://api.github.com/repos/' . $source['repo'] . '/releases/';
		$url .= '' !== $source['candidate_tag'] ? 'tags/' . rawurlencode( $source['candidate_tag'] ) : 'latest';
		$res  = wp_remote_get(
			$url,
			array(
				'timeout'     => 15,
				'redirection' => 0,
				'headers'     => self::headers( $source, false ),
			)
		);
		if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
			return null;
		}
		$data = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! is_array( $data ) || ! empty( $data['draft'] ) ) {
			return null;
		}
		$tag = (string) ( $data['tag_name'] ?? '' );
		if ( ( '' === $source['candidate_tag'] && ! empty( $data['prerelease'] ) )
			|| ( '' !== $source['candidate_tag'] && $tag !== $source['candidate_tag'] ) ) {
			return null;
		}
		if ( ! preg_match( '/^v?(\d+\.\d+\.\d+)$/D', $tag, $match ) ) {
			return null;
		}
		$assets = array();
		foreach ( (array) ( $data['assets'] ?? array() ) as $asset ) {
			if ( is_array( $asset ) && isset( $asset['name'], $asset['id'] ) && is_numeric( $asset['id'] ) ) {
				$assets[ (string) $asset['name'] ] = (int) $asset['id'];
			}
		}
		if ( ! isset( $assets['updates.json'], $assets['checksums.sha256'] ) ) {
			return null;
		}
		$raw = self::download_asset( $source, $assets['updates.json'], self::MAX_METADATA );
		if ( is_wp_error( $raw ) ) {
			return null;
		}
		$manifest = json_decode( (string) file_get_contents( $raw ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Temporary local file.
		wp_delete_file( $raw );
		if ( ! is_array( $manifest ) || 1 !== ( $manifest['schema'] ?? null ) || ( $manifest['version'] ?? null ) !== $match[1] || ! isset( $manifest['components'] ) || ! is_array( $manifest['components'] ) ) {
			return null;
		}
		$release = array(
			'version'    => $match[1],
			'assets'     => $assets,
			'components' => $manifest['components'],
			'url'        => 'https://github.com/' . $source['repo'] . '/releases/tag/' . rawurlencode( $tag ),
		);
		set_site_transient( $key, $release, 15 * MINUTE_IN_SECONDS );
		return $release;
	}

	/**
	 * HTTP headers for GitHub API requests.
	 *
	 * @param array<string,string> $source Source.
	 * @param bool                 $binary Request binary asset.
	 * @return array<string,string>
	 */
	private static function headers( array $source, bool $binary ): array {
		$headers = array(
			'Accept'               => $binary ? 'application/octet-stream' : 'application/vnd.github+json',
			'X-GitHub-Api-Version' => '2022-11-28',
			'User-Agent'           => 'EZHost-WordPress-Updater',
		);
		$token   = self::token( $source );
		if ( '' !== $token ) {
			$headers['Authorization'] = 'Bearer ' . $token;
		}
		return $headers;
	}

	/**
	 * Stream a release asset to a temporary file. Never forward credentials to redirects.
	 *
	 * @param array<string,string> $source Configured source.
	 * @param int                  $id GitHub asset identifier.
	 * @param int                  $limit Maximum bytes.
	 * @return string|\WP_Error Path or error.
	 */
	private static function download_asset( array $source, int $id, int $limit ) {
		if ( $id <= 0 ) {
			return new \WP_Error( 'ezhost_bad_asset', 'Invalid release asset identifier.' );
		}
		$url = 'https://api.github.com/repos/' . $source['repo'] . '/releases/assets/' . $id;
		for ( $attempt = 0; $attempt < 3; ++$attempt ) {
			$file = wp_tempnam( 'ezhost-release-' . $id );
			if ( ! $file ) {
				return new \WP_Error( 'ezhost_temp', 'Cannot create temporary release download.' );
			}
			$args = array(
				'timeout'            => 90,
				'redirection'        => 0,
				'reject_unsafe_urls' => true,
				'stream'             => true,
				'filename'           => $file,
				'headers'            => 0 === $attempt ? self::headers( $source, true ) : array( 'User-Agent' => 'EZHost-WordPress-Updater' ),
			);
			$res  = wp_remote_get( $url, $args );
			if ( is_wp_error( $res ) ) {
				wp_delete_file( $file );
				return $res;
			}
			$code = wp_remote_retrieve_response_code( $res );
			if ( 200 === $code ) {
				if ( ! is_file( $file ) || 0 === filesize( $file ) || filesize( $file ) > $limit ) {
					wp_delete_file( $file );
					return new \WP_Error( 'ezhost_asset_size', 'Release asset is empty or exceeds its permitted size.' );
				}
				$content_type = strtolower( (string) wp_remote_retrieve_header( $res, 'content-type' ) );
				if ( self::MAX_PACKAGE === $limit && str_contains( $content_type, 'application/json' ) ) {
					wp_delete_file( $file );
					return new \WP_Error( 'ezhost_asset_type', 'GitHub returned metadata instead of a release asset.' );
				}
				return $file;
			}
			wp_delete_file( $file );
			if ( ! in_array( $code, array( 301, 302, 303, 307, 308 ), true ) ) {
				return new \WP_Error( 'ezhost_asset_http', 'GitHub release download failed (HTTP ' . (int) $code . ').' );
			}
			$location = wp_remote_retrieve_header( $res, 'location' );
			if ( ! self::trusted_redirect( (string) $location ) ) {
				return new \WP_Error( 'ezhost_asset_redirect', 'GitHub release download redirected to an untrusted destination.' );
			}
			// A redirect is a time-limited signed URL. Use NO authentication on it.
			$url = (string) $location;
		}
		return new \WP_Error( 'ezhost_asset_redirects', 'Too many release download redirects.' );
	}

	/**
	 * Only HTTPS and exact trusted GitHub-hosted asset domains are allowed.
	 *
	 * @param string $url URL to validate.
	 */
	public static function trusted_redirect( string $url ): bool {
		$parts = wp_parse_url( $url );
		return is_array( $parts )
			&& 'https' === ( $parts['scheme'] ?? '' )
			&& in_array( strtolower( (string) ( $parts['host'] ?? '' ) ), array( 'release-assets.githubusercontent.com', 'objects.githubusercontent.com' ), true )
			&& ! isset( $parts['user'] )
			&& ! isset( $parts['pass'] );
	}

	/**
	 * Native plugin update response.
	 *
	 * @param array|false         $update Previous response.
	 * @param array<string,mixed> $data Installed plugin headers.
	 * @param string              $file Installed plugin file.
	 * @param string[]            $locales Installed locales.
	 * @return array|false
	 */
	public static function plugin_update( $update, array $data, string $file, array $locales ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Required WordPress hook signature.
		return self::component_update( 'plugin', $file, (string) ( $data['Version'] ?? '' ), (string) ( $data['UpdateURI'] ?? '' ) );
	}

	/**
	 * Native theme update response.
	 *
	 * @param array|false         $update Previous response.
	 * @param array<string,mixed> $data Installed theme headers.
	 * @param string              $stylesheet Installed stylesheet directory.
	 * @param string[]            $locales Installed locales.
	 * @return array|false
	 */
	public static function theme_update( $update, array $data, string $stylesheet, array $locales ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Required WordPress hook signature.
		return self::component_update( 'theme', $stylesheet, (string) ( $data['Version'] ?? '' ), (string) ( $data['UpdateURI'] ?? '' ) );
	}

	/**
	 * Build a native Update URI response from a known component.
	 *
	 * @param string $type Component type.
	 * @param string $key Installed component ID.
	 * @param string $installed Installed version.
	 * @param string $uri Update URI.
	 * @return array<string,mixed>|false
	 */
	public static function component_update( string $type, string $key, string $installed, string $uri ) {
		$source = self::source( $type, $key );
		if ( ! $source || ! str_starts_with( $uri, 'https://' . self::HOST . '/' ) ) {
			return false;
		}
		$release = self::release( $source );
		if ( ! $release || ! version_compare( $release['version'], $installed, '>' ) ) {
			return false;
		}
		$id   = $type . '/' . $key;
		$meta = $release['components'][ $id ] ?? null;
		if ( ! is_array( $meta ) || empty( $meta['asset'] ) || ! isset( $release['assets'][ $meta['asset'] ] ) ) {
			return false;
		}
		if ( ! self::compatible( $meta ) ) {
			return false;
		}
		$update = array(
			'version'      => $release['version'],
			'url'          => $release['url'],
			'package'      => self::package_url( $type, $key, $release['version'] ),
			'autoupdate'   => false,
			'requires_php' => (string) ( $meta['requires_php'] ?? '' ),
			'requires'     => (string) ( $meta['requires_wp'] ?? '' ),
		);
		if ( 'plugin' === $type ) {
			$update['slug'] = dirname( $key );
		} else {
			$update['theme'] = $key;
		}
		return $update;
	}

	/**
	 * Compare release requirements before advertising an update.
	 *
	 * @param array<string,mixed> $meta Manifest component.
	 */
	public static function compatible( array $meta ): bool {
		$php = (string) ( $meta['requires_php'] ?? '' );
		$wp  = (string) ( $meta['requires_wp'] ?? '' );
		return '' !== $php && '' !== $wp && version_compare( PHP_VERSION, $php, '>=' ) && version_compare( get_bloginfo( 'version' ), $wp, '>=' );
	}

	/**
	 * Virtual package URL is only a handle for upgrader_pre_download; never fetched.
	 *
	 * @param string $type Component type.
	 * @param string $key Installed component ID.
	 * @param string $version Released version.
	 */
	public static function package_url( string $type, string $key, string $version ): string {
		return self::PACKAGE_BASE . rawurlencode( $type ) . '/' . rawurlencode( $key ) . '/' . rawurlencode( $version );
	}

	/**
	 * Hook before WordPress downloads a package. Restrict by exact package, type, key and release.
	 *
	 * @param false|string|\WP_Error $reply Existing response.
	 * @param string                 $package Package URL.
	 * @param \WP_Upgrader           $upgrader Upgrader instance.
	 * @param array<string,mixed>    $extra Installed component identity.
	 * @return false|string|\WP_Error
	 */
	public static function pre_download( $reply, string $package, $upgrader, array $extra = array() ) {
		if ( false !== $reply || ! str_starts_with( $package, self::PACKAGE_BASE ) ) {
			return $reply;
		}
		$type   = isset( $extra['plugin'] ) ? 'plugin' : ( isset( $extra['theme'] ) ? 'theme' : '' );
		$key    = 'plugin' === $type ? (string) $extra['plugin'] : ( 'theme' === $type ? (string) $extra['theme'] : '' );
		$source = self::source( $type, $key );
		if ( ! $source ) {
			return new \WP_Error( 'ezhost_unconfigured', 'Update source is not configured for the requested component.' );
		}
		$release = self::release( $source );
		if ( ! $release || self::package_url( $type, $key, $release['version'] ) !== $package ) {
			return new \WP_Error( 'ezhost_stale', 'Release metadata is missing or the requested update is no longer approved.' );
		}
		$id   = $type . '/' . $key;
		$meta = $release['components'][ $id ] ?? null;
		if ( ! is_array( $meta ) || ! self::compatible( $meta ) ) {
			return new \WP_Error( 'ezhost_incompatible', 'Release does not meet the installed WordPress/PHP requirements.' );
		}
		$expected_asset = ( 'theme' === $type ? $key : dirname( $key ) ) . '-' . $release['version'] . '.zip';
		if ( ( $meta['asset'] ?? null ) !== $expected_asset || empty( $release['assets'][ $expected_asset ] ) ) {
			return new \WP_Error( 'ezhost_identity', 'Release asset does not match the installed theme or plugin.' );
		}

		$checksums = self::download_asset( $source, $release['assets']['checksums.sha256'], self::MAX_METADATA );
		if ( is_wp_error( $checksums ) ) {
			return $checksums;
		}
		$manifest = (string) file_get_contents( $checksums ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Temporary file.
		wp_delete_file( $checksums );
		$hash = self::checksum_for( $manifest, $expected_asset );
		if ( ! $hash ) {
			return new \WP_Error( 'ezhost_missing_checksum', 'Approved package checksum missing from release.' );
		}
		$file = self::download_asset( $source, $release['assets'][ $expected_asset ], self::MAX_PACKAGE );
		if ( is_wp_error( $file ) ) {
			return $file;
		}
		if ( ! hash_equals( $hash, hash_file( 'sha256', $file ) ) || ! self::archive_valid( $file, $type, $key, $release['version'] ) ) {
			wp_delete_file( $file );
			return new \WP_Error( 'ezhost_verification', 'Release package failed checksum or component identity verification.' );
		}
		return $file;
	}

	/**
	 * Find exactly one SHA256 line for a package; reject duplicate entries.
	 *
	 * @param string $manifest SHA-256 manifest contents.
	 * @param string $asset Expected ZIP filename.
	 */
	public static function checksum_for( string $manifest, string $asset ): ?string {
		$hashes = array();
		foreach ( preg_split( '/\r?\n/', $manifest ) as $line ) {
			if ( preg_match( '/^([a-f0-9]{64})  ([a-z0-9.-]+\.zip)$/D', trim( $line ), $matches ) && $matches[2] === $asset ) {
				$hashes[] = $matches[1];
			}
		}
		return 1 === count( $hashes ) ? $hashes[0] : null;
	}

	/**
	 * Check archive members, zip-slip paths, top-level directory and main header version.
	 *
	 * @param string $file ZIP file path.
	 * @param string $type Component type.
	 * @param string $key Installed component ID.
	 * @param string $version Expected version.
	 */
	public static function archive_valid( string $file, string $type, string $key, string $version ): bool {
		if ( ! class_exists( 'PclZip' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
		}
		$zip  = new \PclZip( $file );
		$list = $zip->listContent();
		if ( ! is_array( $list ) || ! $list ) {
			return false;
		}
		$slug   = 'theme' === $type ? $key : dirname( $key );
		$header = $slug . '/' . ( 'theme' === $type ? 'style.css' : basename( $key ) );
		$found  = false;
		foreach ( $list as $entry ) {
			$name = (string) ( $entry['filename'] ?? '' );
			if ( str_contains( $name, '\\' ) || str_starts_with( $name, '/' ) || str_contains( '/' . $name, '/../' ) || ! str_starts_with( $name, $slug . '/' ) ) {
				return false;
			}
			if ( $header === $name ) {
				$found = true;
			}
		}
		if ( ! $found ) {
			return false;
		}
		$content = $zip->extract( PCLZIP_OPT_BY_NAME, $header, PCLZIP_OPT_EXTRACT_AS_STRING );
		if ( ! is_array( $content ) || ! isset( $content[0]['content'] ) ) {
			return false;
		}
		$body = (string) $content[0]['content'];
		return (bool) preg_match( '/^\s*(?:\*\s*)?Version:\s*' . preg_quote( $version, '/' ) . '\s*$/mi', $body )
			&& (bool) preg_match( '/^\s*(?:\*\s*)?' . ( 'theme' === $type ? 'Theme Name' : 'Plugin Name' ) . ':\s*\S+/mi', $body );
	}

	/**
	 * Automatic updates are deliberately disabled for managed components.
	 *
	 * @param bool   $update Previous preference.
	 * @param object $item Plugin update metadata.
	 */
	public static function prevent_plugin_auto_update( $update, $item ) {
		$file = (string) ( $item->plugin ?? '' );
		return self::source( 'plugin', $file ) ? false : $update;
	}

	/**
	 * Automatic theme updates are deliberately disabled for managed components.
	 *
	 * @param bool   $update Previous preference.
	 * @param object $item Theme update metadata.
	 */
	public static function prevent_theme_auto_update( $update, $item ) {
		$key = (string) ( $item->theme ?? '' );
		return self::source( 'theme', $key ) ? false : $update;
	}
}
