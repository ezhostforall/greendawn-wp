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
if ( $greendawn_branded_home && '' === $greendawn_cta_label ) {
	$greendawn_cta_label = __( 'Discuss your site', 'greendawn' );
}
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

	<header class="site-header<?php echo get_theme_mod( 'greendawn_header_sticky', false ) ? ' site-header--sticky' : ''; ?>">
		<?php if ( $greendawn_branded_home ) : ?>
			<div class="gd-utility">
				<p><?php esc_html_e( 'Commercial EV infrastructure across England, Scotland & Wales', 'greendawn' ); ?></p>
		<?php endif; ?>
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
		<?php if ( $greendawn_branded_home ) : ?>
			</div>
		<?php endif; ?>
		<div class="site-header__inner">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php elseif ( $greendawn_branded_home ) : ?>
				<a class="custom-logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<img class="custom-logo" src="<?php echo esc_url( get_theme_file_uri( '/assets/home/greendawn-wordmark-480.png' ) ); ?>" width="480" height="132" fetchpriority="auto" alt="<?php esc_attr_e( 'Greendawn', 'greendawn' ); ?>">
				</a>
			<?php else : ?>
				<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<details class="nav-disclosure">
					<summary class="nav-toggle button" data-nav-toggle data-nav-wide="<?php echo esc_attr( $greendawn_branded_home ? 'true' : 'false' ); ?>" data-submenu-label="<?php echo esc_attr( $greendawn_submenu_label ); ?>" aria-controls="primary-navigation">
						<?php if ( $greendawn_branded_home ) : ?>
							<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'greendawn' ); ?></span>
						<?php else : ?>
							<?php esc_html_e( 'Menu', 'greendawn' ); ?>
						<?php endif; ?>
					</summary>
				</details>
				<?php if ( $greendawn_branded_home ) : ?>
					<nav id="primary-navigation" class="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'greendawn' ); ?>">
				<?php endif; ?>
				<?php
				wp_nav_menu(
					array(
						'theme_location'       => 'primary',
						'container'            => $greendawn_branded_home ? false : 'nav',
						'container_id'         => 'primary-navigation',
						'container_class'      => 'primary-nav',
						'container_aria_label' => __( 'Primary', 'greendawn' ),
						'fallback_cb'          => false,
					)
				);
				?>
				<?php if ( $greendawn_branded_home ) : ?>
					<?php if ( '' !== $greendawn_cta_url && '#' !== $greendawn_cta_url ) : ?>
						<a class="gd-home-button gd-mobile-enquiry" href="<?php echo esc_url( $greendawn_cta_url ); ?>"><?php echo esc_html( $greendawn_cta_label ); ?></a>
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
			<?php endif; ?>
			<?php if ( '' !== $greendawn_cta_label && '' !== $greendawn_cta_url && '#' !== $greendawn_cta_url ) : ?>
				<a class="header-cta button" href="<?php echo esc_url( $greendawn_cta_url ); ?>"><?php echo esc_html( $greendawn_cta_label ); ?>
					<?php if ( $greendawn_branded_home ) : ?>
						<?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?>
					<?php endif; ?>
				</a>
			<?php endif; ?>
		</div>
	</header>

	<main id="content" class="site-main">
