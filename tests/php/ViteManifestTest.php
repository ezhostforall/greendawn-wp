<?php
use PHPUnit\Framework\TestCase;

final class ViteManifestTest extends TestCase {

	private function manifest(): array {
		return array(
			'src/scripts/main.ts' => array(
				'file'    => 'assets/main-abc.js',
				'imports' => array( '_shared.js' ),
				'css'     => array( 'assets/main-abc.css' ),
			),
			'_shared.js'          => array(
				'file' => 'assets/shared-def.js',
				'css'  => array( 'assets/shared-def.css' ),
			),
			'src/styles/editor.css' => array( 'file' => 'assets/editor-ghi.css' ),
		);
	}

	public function test_resolves_file_css_and_static_imports(): void {
		$resolved = greendawn_resolve_manifest_entry( $this->manifest(), 'src/scripts/main.ts' );

		$this->assertSame( 'assets/main-abc.js', $resolved['file'] );
		$this->assertSame( array( 'assets/main-abc.css', 'assets/shared-def.css' ), $resolved['css'] );
		$this->assertSame( array( 'assets/shared-def.js' ), $resolved['imports'] );
	}

	public function test_css_only_entry_is_its_own_stylesheet(): void {
		$resolved = greendawn_resolve_manifest_entry( $this->manifest(), 'src/styles/editor.css' );

		$this->assertSame( array( 'assets/editor-ghi.css' ), $resolved['css'] );
	}

	public function test_missing_entry_returns_null(): void {
		$this->assertNull( greendawn_resolve_manifest_entry( $this->manifest(), 'src/missing.ts' ) );
		$this->assertNull( greendawn_resolve_manifest_entry( array(), 'src/scripts/main.ts' ) );
	}

	public function test_circular_imports_terminate(): void {
		$manifest = array(
			'a' => array( 'file' => 'a.js', 'imports' => array( 'b' ) ),
			'b' => array( 'file' => 'b.js', 'imports' => array( 'a' ) ),
		);

		$resolved = greendawn_resolve_manifest_entry( $manifest, 'a' );

		$this->assertSame( array( 'b.js', 'a.js' ), $resolved['imports'] );
	}
}
