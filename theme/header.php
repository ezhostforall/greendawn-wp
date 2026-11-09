<?php
/**
 * Site header.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_branded_home = is_page_template( 'templates/greendawn-homepage.php' );
$greendawn_cta_label    = trim( (string) get_theme_mod( 'greendawn_header_cta_label', '' ) );
$greendawn_cta_url      = esc_url( (string) get_theme_mod( 'greendawn_header_cta_url', '' ) );
if ( '' === $greendawn_cta_label ) {
	$greendawn_cta_label = __( 'Discuss your site', 'greendawn' );
}
$greendawn_has_cta = '' !== $greendawn_cta_url && '#' !== $greendawn_cta_url;
/* translators: %s: parent menu link text. */
$greendawn_submenu_label = __( 'Toggle submenu for %s', 'greendawn' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( $greendawn_branded_home ? 'greendawn-home' : '' ); ?>>
<?php wp_body_open(); ?>
<div class="site" id="page">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'greendawn' ); ?></a>

	<div class="gd-utility-bar">
		<div class="gd-utility">
			<p><?php echo esc_html( get_theme_mod( 'greendawn_header_utility_text', greendawn_utility_text_default() ) ); ?></p>
			<?php
			if ( has_nav_menu( 'utility' ) ) {
				wp_nav_menu(
					array(
						'theme_location'       => 'utility',
						'container'            => 'nav',
						'container_class'      => 'utility-nav',
						'container_aria_label' => __( 'Utility', 'greendawn' ),
						'depth'                => 1,
						'fallback_cb'          => false,
					)
				);
			}
			?>
		</div>
	</div>

	<div class="gd-header-slot">
		<header class="site-header<?php echo get_theme_mod( 'greendawn_header_sticky', false ) ? ' site-header--sticky' : ''; ?>">
			<div class="site-header__inner">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a class="custom-logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<img class="custom-logo" src="<?php echo esc_url( get_theme_file_uri( '/assets/home/greendawn-wordmark-480.png' ) ); ?>" width="480" height="132" fetchpriority="auto" alt="<?php esc_attr_e( 'Greendawn', 'greendawn' ); ?>">
					</a>
				<?php endif; ?>

				<?php if ( has_nav_menu( 'primary' ) ) : ?>
					<details class="nav-disclosure">
						<summary class="nav-toggle button" data-nav-toggle data-submenu-label="<?php echo esc_attr( $greendawn_submenu_label ); ?>" aria-controls="primary-navigation">
							<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'greendawn' ); ?></span>
						</summary>
					</details>
					<nav id="primary-navigation" class="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'greendawn' ); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'primary',
								'container'      => false,
								'fallback_cb'    => false,
							)
						);
						?>
						<?php if ( $greendawn_has_cta ) : ?>
							<a class="gd-home-button gd-mobile-enquiry" data-lead-capture-open data-lead-entry-point="header" href="<?php echo esc_url( $greendawn_cta_url ); ?>"><?php echo esc_html( $greendawn_cta_label ); ?></a>
						<?php endif; ?>
						<?php
						if ( has_nav_menu( 'utility' ) ) {
							$greendawn_hide_menu_ids = static function (): string {
								return '';
							};
							add_filter( 'nav_menu_item_id', $greendawn_hide_menu_ids );
							wp_nav_menu(
								array(
									'theme_location' => 'utility',
									'container'      => false,
									'menu_id'        => 'greendawn-mobile-utility',
									'menu_class'     => 'gd-mobile-utility',
									'depth'          => 1,
									'fallback_cb'    => false,
								)
							);
							remove_filter( 'nav_menu_item_id', $greendawn_hide_menu_ids );
						}
						?>
					</nav>
				<?php endif; ?>
				<?php if ( $greendawn_has_cta ) : ?>
					<a class="header-cta button" data-lead-capture-open data-lead-entry-point="header" href="<?php echo esc_url( $greendawn_cta_url ); ?>">
						<?php echo esc_html( $greendawn_cta_label ); ?>
						<?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?>
					</a>
				<?php endif; ?>
			</div>
		</header>
	</div>

	<main id="content" class="site-main">
