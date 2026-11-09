<?php
/**
 * Theme setup: supports, menus, image sizes.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers theme features.
 */
function greendawn_setup(): void {
	load_theme_textdomain( 'greendawn', GREENDAWN_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support(
		'custom-logo',
		array(
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	register_nav_menus(
		array(
			'primary'          => __( 'Primary menu', 'greendawn' ),
			'utility'          => __( 'Utility menu', 'greendawn' ),
			'footer'           => __( 'Footer company menu', 'greendawn' ),
			'footer_secondary' => __( 'Footer solutions menu', 'greendawn' ),
			'footer_support'   => __( 'Footer support menu', 'greendawn' ),
			'footer_social'    => __( 'Footer social links', 'greendawn' ),
			'footer_legal'     => __( 'Footer legal links', 'greendawn' ),
		)
	);

	add_image_size( 'greendawn-card', 640, 360, true );

	// Editor styles are compiled by Vite; skip silently until `pnpm build` has run.
	$editor_css = greendawn_entry_css_paths( 'editor' );
	if ( $editor_css ) {
		add_editor_style( 'dist/' . $editor_css[0] );
	}
}
add_action( 'after_setup_theme', 'greendawn_setup' );

/**
 * Sets the default content width for embeds.
 */
function greendawn_content_width(): void {
	$GLOBALS['content_width'] = 800;
}
add_action( 'after_setup_theme', 'greendawn_content_width', 0 );
