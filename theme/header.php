<?php
/**
 * Site header.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_cta_label = trim( (string) get_theme_mod( 'greendawn_header_cta_label', '' ) );
$greendawn_cta_url   = esc_url( (string) get_theme_mod( 'greendawn_header_cta_url', '' ) );
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
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="site" id="page">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'greendawn' ); ?></a>

	<header class="site-header<?php echo get_theme_mod( 'greendawn_header_sticky', false ) ? ' site-header--sticky' : ''; ?>">
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
		<div class="site-header__inner">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<details class="nav-disclosure">
					<summary class="nav-toggle button" data-nav-toggle data-submenu-label="<?php echo esc_attr( $greendawn_submenu_label ); ?>" aria-controls="primary-navigation">
						<?php esc_html_e( 'Menu', 'greendawn' ); ?>
					</summary>
				</details>
				<?php
				wp_nav_menu(
					array(
						'theme_location'       => 'primary',
						'container'            => 'nav',
						'container_id'         => 'primary-navigation',
						'container_class'      => 'primary-nav',
						'container_aria_label' => __( 'Primary', 'greendawn' ),
						'fallback_cb'          => false,
					)
				);
				?>
			<?php endif; ?>
			<?php if ( '' !== $greendawn_cta_label && '' !== $greendawn_cta_url && '#' !== $greendawn_cta_url ) : ?>
				<a class="header-cta button" href="<?php echo esc_url( $greendawn_cta_url ); ?>"><?php echo esc_html( $greendawn_cta_label ); ?></a>
			<?php endif; ?>
		</div>
	</header>

	<main id="content" class="site-main">
