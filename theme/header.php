<?php
/**
 * Site header.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

$wp_starter_cta_label = trim( (string) get_theme_mod( 'wp_starter_header_cta_label', '' ) );
$wp_starter_cta_url   = esc_url( (string) get_theme_mod( 'wp_starter_header_cta_url', '' ) );
/* translators: %s: parent menu link text. */
$wp_starter_submenu_label = __( 'Toggle submenu for %s', 'wp-starter' );
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
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'wp-starter' ); ?></a>

	<header class="site-header<?php echo get_theme_mod( 'wp_starter_header_sticky', false ) ? ' site-header--sticky' : ''; ?>">
		<?php
		if ( has_nav_menu( 'utility' ) ) {
			wp_nav_menu(
				array(
					'theme_location'       => 'utility',
					'container'            => 'nav',
					'container_class'      => 'utility-nav',
					'container_aria_label' => __( 'Utility', 'wp-starter' ),
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
					<summary class="nav-toggle button" data-nav-toggle data-submenu-label="<?php echo esc_attr( $wp_starter_submenu_label ); ?>" aria-controls="primary-navigation">
						<?php esc_html_e( 'Menu', 'wp-starter' ); ?>
					</summary>
				</details>
				<?php
				wp_nav_menu(
					array(
						'theme_location'       => 'primary',
						'container'            => 'nav',
						'container_id'         => 'primary-navigation',
						'container_class'      => 'primary-nav',
						'container_aria_label' => __( 'Primary', 'wp-starter' ),
						'fallback_cb'          => false,
					)
				);
				?>
			<?php endif; ?>
			<?php if ( '' !== $wp_starter_cta_label && '' !== $wp_starter_cta_url && '#' !== $wp_starter_cta_url ) : ?>
				<a class="header-cta button" href="<?php echo esc_url( $wp_starter_cta_url ); ?>"><?php echo esc_html( $wp_starter_cta_label ); ?></a>
			<?php endif; ?>
		</div>
	</header>

	<main id="content" class="site-main">
