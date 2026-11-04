<?php
/**
 * Site footer.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_footer_summary   = trim( (string) get_theme_mod( 'greendawn_footer_summary', '' ) );
$greendawn_footer_contact   = trim( (string) get_theme_mod( 'greendawn_footer_contact', '' ) );
$greendawn_footer_cta_label = trim( (string) get_theme_mod( 'greendawn_footer_cta_label', '' ) );
$greendawn_footer_cta_url   = sanitize_url( (string) get_theme_mod( 'greendawn_footer_cta_url', '' ) );
$greendawn_footer_logo      = get_theme_mod( 'custom_logo' );

/**
 * Supplies optional client-owned disclosure copy with basic inline markup.
 *
 * @param string $disclosure Disclosure HTML.
 */
$greendawn_footer_disclosure = trim( (string) apply_filters( 'greendawn_footer_disclosure', '' ) );
?>
	</main>

	<footer class="site-footer">
		<div class="site-footer__inner">
			<div class="site-footer__columns">
				<div class="site-footer__identity">
					<a class="site-footer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php if ( $greendawn_footer_logo ) : ?>
							<?php echo wp_get_attachment_image( (int) $greendawn_footer_logo, 'medium', false, array( 'alt' => '' ) ); ?>
						<?php endif; ?>
						<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
					</a>
					<?php if ( '' !== $greendawn_footer_summary ) : ?>
						<p class="site-footer__copy"><?php echo esc_html( $greendawn_footer_summary ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $greendawn_footer_cta_label && '' !== $greendawn_footer_cta_url ) : ?>
						<a class="site-footer__cta" href="<?php echo esc_url( $greendawn_footer_cta_url ); ?>"><?php echo esc_html( $greendawn_footer_cta_label ); ?></a>
					<?php endif; ?>
				</div>
				<?php
				greendawn_footer_menu( 'footer', __( 'Footer navigation', 'greendawn' ) );
				greendawn_footer_menu( 'footer_secondary', __( 'More footer links', 'greendawn' ) );
				?>
				<?php if ( '' !== $greendawn_footer_contact || has_nav_menu( 'footer_social' ) ) : ?>
					<div class="site-footer__contact">
						<?php if ( '' !== $greendawn_footer_contact ) : ?>
							<p class="site-footer__copy"><?php echo esc_html( $greendawn_footer_contact ); ?></p>
						<?php endif; ?>
						<?php greendawn_footer_menu( 'footer_social', __( 'Social links', 'greendawn' ) ); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( '' !== $greendawn_footer_disclosure ) : ?>
				<div class="site-footer__disclosure"><?php echo wp_kses_post( $greendawn_footer_disclosure ); ?></div>
			<?php endif; ?>
			<div class="site-footer__meta">
				<p>
					<?php
					/* translators: 1: year, 2: site name. */
					printf( esc_html__( '&copy; %1$s %2$s', 'greendawn' ), esc_html( gmdate( 'Y' ) ), esc_html( get_bloginfo( 'name' ) ) );
					?>
				</p>
				<?php greendawn_footer_menu( 'footer_legal', __( 'Legal links', 'greendawn' ) ); ?>
			</div>
		</div>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
