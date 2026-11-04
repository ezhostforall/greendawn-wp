<?php
/**
 * Plugin Name: Disposable layout brand scenarios
 * Description: Token-only examples; never installed on the permanent host.
 */

if ( ! defined( 'ABSPATH' ) || 'local' !== wp_get_environment_type() || 'http://localhost:8080' !== home_url() ) {
	return;
}

add_filter(
	'wp_theme_json_data_theme',
	static function ( $data ) {
		// Resolve before the frontend query, or from the disposable editor's post ID.
		$slug = trim( wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
		if ( is_admin() && isset( $_GET['post'] ) ) {
			$slug = get_post_field( 'post_name', absint( $_GET['post'] ) );
		}
		$brands = array(
			'layout-brand-a' => array(
				'accent' => '#175c4a', 'text' => '#142a24', 'background' => '#fffdf8',
				'font' => 'Georgia, serif', 'content' => '38rem', 'wide' => '68rem',
				'space' => '1.25rem', 'large' => '2.5rem', 'headingMin' => '1.75rem', 'headingMax' => '2.75rem',
			),
			'layout-brand-b' => array(
				'accent' => '#5b21b6', 'text' => '#25143b', 'background' => '#fdfbff',
				'font' => 'Arial, sans-serif', 'content' => '46rem', 'wide' => '76rem',
				'space' => '1.5rem', 'large' => '3rem', 'headingMin' => '1.625rem', 'headingMax' => '3rem',
			),
		);
		if ( ! isset( $brands[ $slug ] ) ) {
			return $data;
		}
		$brand = $brands[ $slug ];
		// Supply complete preset collections, preserving every unrelated semantic role.
		$settings = json_decode( file_get_contents( get_template_directory() . '/theme.json' ), true )['settings'];
		$settings['layout'] = array( 'contentSize' => $brand['content'], 'wideSize' => $brand['wide'] );
		foreach ( $settings['color']['palette'] as &$preset ) {
			if ( isset( $brand[ $preset['slug'] ] ) ) {
				$preset['color'] = $brand[ $preset['slug'] ];
			}
		}
		unset( $preset );
		$settings['typography']['fontFamilies'][0]['fontFamily'] = $brand['font'];
		foreach ( $settings['typography']['fontSizes'] as &$preset ) {
			if ( 'heading' === $preset['slug'] ) {
				$preset['fluid'] = array( 'min' => $brand['headingMin'], 'max' => $brand['headingMax'] );
			}
		}
		unset( $preset );
		foreach ( $settings['spacing']['spacingSizes'] as &$preset ) {
			if ( 'm' === $preset['slug'] ) {
				$preset['size'] = $brand['space'];
			} elseif ( 'l' === $preset['slug'] ) {
				$preset['size'] = $brand['large'];
			}
		}
		unset( $preset );
		return $data->update_with( array( 'version' => 3, 'settings' => $settings ) );
	}
);
