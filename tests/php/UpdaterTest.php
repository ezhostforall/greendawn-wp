<?php
/**
 * Test pure update metadata/validation logic without a WordPress installation.
 *
 * @package WP_Starter
 */

use EZHost_Updater\Updater;
use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/plugins/ezhost-updater/includes/Updater.php';

if ( ! function_exists( 'wp_parse_url' ) ) {
	function wp_parse_url( $url ) {
		return parse_url( $url ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Pure PHP tests.
	}
}

if ( ! function_exists( 'get_bloginfo' ) ) {
	function get_bloginfo( $field ) {
		return 'version' === $field ? '6.9.0' : '';
	}
}

// Candidate release tests emulate the WordPress staging environment.
if ( ! function_exists( 'wp_get_environment_type' ) ) {
	function wp_get_environment_type() {
		return $GLOBALS['wp_starter_test_env'] ?? 'production';
	}
}

/**
 * Unit tests for the isolated updater rules.
 */
final class UpdaterTest extends TestCase {

	public function test_approved_redirects_require_https_and_known_hosts(): void {
		self::assertTrue( Updater::trusted_redirect( 'https://release-assets.githubusercontent.com/asset?sig=x' ) );
		self::assertTrue( Updater::trusted_redirect( 'https://objects.githubusercontent.com/object' ) );
		self::assertFalse( Updater::trusted_redirect( 'http://release-assets.githubusercontent.com/asset' ) );
		self::assertFalse( Updater::trusted_redirect( 'https://release-assets.githubusercontent.com.evil.test/asset' ) );
		self::assertFalse( Updater::trusted_redirect( 'https://user:pass@objects.githubusercontent.com/file' ) );
		self::assertFalse( Updater::trusted_redirect( 'https://api.github.com/other' ) );
	}

	public function test_checksummed_asset_must_have_unique_valid_sha256(): void {
		$first = str_repeat( 'a', 64 );
		$other = str_repeat( 'b', 64 );
		$body  = $first . "  example-client-1.0.0.zip\n" . $other . "  other-1.0.0.zip\n";
		self::assertSame( $first, Updater::checksum_for( $body, 'example-client-1.0.0.zip' ) );
		self::assertNull( Updater::checksum_for( $body, 'missing.zip' ) );
		self::assertNull( Updater::checksum_for( $body . $first . "  example-client-1.0.0.zip\n", 'example-client-1.0.0.zip' ) );
		self::assertNull( Updater::checksum_for( "not-a-sha  example-client-1.0.0.zip", 'example-client-1.0.0.zip' ) );
	}

	public function test_virtual_download_urls_are_exact_handles(): void {
		self::assertSame(
			'https://updates.ezhostforall.invalid/package/plugin/example-client-core%2Fexample-client-core.php/1.2.3',
			Updater::package_url( 'plugin', 'example-client-core/example-client-core.php', '1.2.3' )
		);
	}

	public function test_release_compatibility_requires_declared_minimums(): void {
		self::assertTrue( Updater::compatible( array( 'requires_php' => '8.1', 'requires_wp' => '6.6' ) ) );
		self::assertFalse( Updater::compatible( array( 'requires_php' => '99.0', 'requires_wp' => '6.6' ) ) );
		self::assertFalse( Updater::compatible( array( 'requires_php' => '8.1', 'requires_wp' => '99.0' ) ) );
		self::assertFalse( Updater::compatible( array() ) );
	}

	/**
	 * Candidate releases must be pinned and only available to staging websites.
	 */
	#[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
	public function test_candidate_source_is_staging_only_and_requires_valid_tag(): void {
		define(
			'EZHOST_UPDATE_SOURCES',
			array(
				'themes' => array(
					'example-client' => array(
						'repo'          => 'ezhostforall/example-client',
						'candidate_tag' => 'v0.3.0',
					),
					'bad-tag' => array(
						'repo'          => 'ezhostforall/example-client',
						'candidate_tag' => 'main',
					),
				),
			)
		);
		$GLOBALS['wp_starter_test_env'] = 'staging';
		self::assertSame(
			'v0.3.0',
			Updater::source( 'theme', 'example-client' )['candidate_tag']
		);
		self::assertNull( Updater::source( 'theme', 'bad-tag' ) );

		$GLOBALS['wp_starter_test_env'] = 'production';
		self::assertNull( Updater::source( 'theme', 'example-client' ) );
	}

	public function test_no_source_configuration_prevents_unapproved_update(): void {
		self::assertNull( Updater::source( 'plugin', 'example-client-core/example-client-core.php' ) );
		self::assertNull( Updater::source( 'theme', 'example-client' ) );
		self::assertFalse( Updater::component_update( 'theme', 'example-client', '1.0.0', 'https://updates.ezhostforall.invalid/example-client' ) );
	}

	public function test_unconfigured_download_is_not_intercepted(): void {
		$target = Updater::package_url( 'plugin', 'example-client-core/example-client-core.php', '1.0.0' );
		$reply  = Updater::pre_download( false, $target, null, array( 'plugin' => 'example-client-core/example-client-core.php' ) );
		self::assertInstanceOf( \WP_Error::class, $reply );
		self::assertSame( 'ezhost_unconfigured', $reply->get_error_code() );
		self::assertFalse( Updater::pre_download( false, 'https://example.org/package.zip', null, array() ) );
	}
}
