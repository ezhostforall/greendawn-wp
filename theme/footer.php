<?php
/**
 * Sitewide Greendawn footer.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_footer_phone     = greendawn_footer_value( 'phone' );
$greendawn_footer_tel       = greendawn_phone_uri( $greendawn_footer_phone );
$greendawn_footer_email     = sanitize_email( greendawn_footer_value( 'email' ) );
$greendawn_footer_logo      = absint( get_theme_mod( 'custom_logo' ) );
$greendawn_footer_image     = $greendawn_footer_logo ? wp_get_attachment_image(
	$greendawn_footer_logo,
	'medium',
	false,
	array( 'alt' => '', 'loading' => 'lazy', 'fetchpriority' => 'low' )
) : '';
$greendawn_footer_address   = greendawn_footer_value( 'contact' );
$greendawn_footer_cta_label = greendawn_footer_value( 'cta_label' );
$greendawn_footer_cta_url   = esc_url( greendawn_footer_local_url( greendawn_footer_value( 'cta_url' ) ) );
$greendawn_footer_has_cta   = '' !== $greendawn_footer_cta_label && '' !== $greendawn_footer_cta_url && '#' !== $greendawn_footer_cta_url;
$greendawn_footer_legal     = greendawn_footer_value( 'legal_name' );
$greendawn_footer_company   = greendawn_footer_value( 'company_number' );
$greendawn_footer_vat       = greendawn_footer_value( 'vat_number' );

/**
 * Supplies optional approved disclosures with basic inline markup.
 *
 * @param string $disclosure Disclosure HTML.
 */
$greendawn_footer_disclosure = trim( (string) apply_filters( 'greendawn_footer_disclosure', '' ) );
?>
	</main>

	<footer class="site-footer gd-footer">
		<div class="gd-footer__shell">
			<div class="gd-footer__lead">
				<a class="gd-footer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php /* translators: %s: site name. */ echo esc_attr( sprintf( __( '%s homepage', 'greendawn' ), get_bloginfo( 'name' ) ) ); ?>">
					<?php if ( '' !== $greendawn_footer_image ) : ?>
						<?php echo $greendawn_footer_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generates escaped attachment markup. ?>
					<?php else : ?>
						<img src="<?php echo esc_url( get_theme_file_uri( '/assets/home/greendawn-wordmark-480.png' ) ); ?>" width="480" height="132" loading="lazy" fetchpriority="low" alt="">
					<?php endif; ?>
				</a>
				<?php if ( '' !== greendawn_footer_value( 'summary' ) ) : ?>
					<p><?php echo esc_html( greendawn_footer_value( 'summary' ) ); ?></p>
				<?php endif; ?>
			</div>
			<div class="gd-footer__grid">
				<?php
				greendawn_footer_menu( 'footer', greendawn_footer_value( 'company_heading' ), true );
				greendawn_footer_menu( 'footer_secondary', greendawn_footer_value( 'solutions_heading' ), true );
				greendawn_footer_menu( 'footer_support', greendawn_footer_value( 'support_heading' ), true );
				?>
				<?php if ( '' !== $greendawn_footer_tel || '' !== $greendawn_footer_email || '' !== $greendawn_footer_address || $greendawn_footer_has_cta ) : ?>
					<div class="gd-footer__contact">
						<?php if ( '' !== greendawn_footer_value( 'contact_heading' ) ) : ?>
							<span><?php echo esc_html( greendawn_footer_value( 'contact_heading' ) ); ?></span>
						<?php endif; ?>
						<?php if ( '' !== $greendawn_footer_tel ) : ?>
							<a class="gd-footer__phone" href="<?php echo esc_url( $greendawn_footer_tel ); ?>"><?php echo esc_html( $greendawn_footer_phone ); ?></a>
						<?php endif; ?>
						<?php if ( '' !== $greendawn_footer_email ) : ?>
							<a href="<?php echo esc_url( 'mailto:' . $greendawn_footer_email ); ?>"><?php echo esc_html( $greendawn_footer_email ); ?></a>
						<?php endif; ?>
						<?php if ( '' !== $greendawn_footer_address ) : ?>
							<p><?php echo esc_html( $greendawn_footer_address ); ?></p>
						<?php endif; ?>
						<?php if ( $greendawn_footer_has_cta ) : ?>
							<a class="gd-footer__cta" href="<?php echo esc_url( $greendawn_footer_cta_url ); ?>"><?php echo esc_html( $greendawn_footer_cta_label ); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( '' !== $greendawn_footer_disclosure ) : ?>
				<div class="gd-footer__disclosure"><?php echo wp_kses_post( $greendawn_footer_disclosure ); ?></div>
			<?php endif; ?>
			<div class="gd-footer__base">
				<span>
					&copy; <?php echo esc_html( '' !== $greendawn_footer_legal ? $greendawn_footer_legal : get_bloginfo( 'name' ) ); ?>
					<?php if ( '' !== $greendawn_footer_company ) : ?>
						&middot; <?php /* translators: %s: company registration number. */ echo esc_html( sprintf( __( 'Company %s', 'greendawn' ), $greendawn_footer_company ) ); ?>
					<?php endif; ?>
					<?php if ( '' !== $greendawn_footer_vat ) : ?>
						&middot; <?php /* translators: %s: VAT registration number. */ echo esc_html( sprintf( __( 'VAT %s', 'greendawn' ), $greendawn_footer_vat ) ); ?>
					<?php endif; ?>
				</span>
				<?php greendawn_footer_menu( 'footer_legal', __( 'Legal links', 'greendawn' ) ); ?>
				<div class="gd-footer__social"><?php greendawn_footer_menu( 'footer_social', __( 'Social links', 'greendawn' ) ); ?></div>
			</div>
		</div>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
