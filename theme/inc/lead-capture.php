<?php
/**
 * Optional React enquiry presentation; Contact Form 7 owns submission.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gets the Core-owned managed callback configuration.
 *
 * @return array
 */
function greendawn_lead_config(): array {
	$config = apply_filters( 'greendawn_lead_config', array() );
	return is_array( $config ) && ! empty( $config['formId'] ) && function_exists( 'wpcf7_contact_form' ) ? $config : array();
}

/** Loads the small launcher; React is downloaded only when it is opened. */
function greendawn_lead_assets(): void {
	if ( ! greendawn_lead_config() ) {
		return;
	}
	greendawn_enqueue_entry( 'enquiry' );
	// Honour native CF7 integrations even on sites which disable global CF7 assets.
	if ( function_exists( 'wpcf7_enqueue_scripts' ) ) {
		wpcf7_enqueue_scripts();
	}
}
add_action( 'wp_enqueue_scripts', 'greendawn_lead_assets', 20 );

/** Renders the native transport and optional enhancement outside page content. */
function greendawn_lead_render(): void {
	$config = greendawn_lead_config();
	if ( ! $config ) {
		return;
	}
	$privacy_url           = get_privacy_policy_url();
	$config['privacyUrl']  = '' !== $privacy_url ? $privacy_url : home_url( '/privacy-policy/' );
	$config['phone']       = greendawn_footer_value( 'phone' );
	$config['phoneUrl']    = greendawn_phone_uri( $config['phone'] );
	$config['source']      = is_page_template( 'templates/greendawn-homepage.php' ) ? 'homepage' : 'website';
	$fallback_url          = esc_url_raw( (string) get_theme_mod( 'greendawn_header_cta_url', '' ) );
	$config['fallbackUrl'] = '' !== $fallback_url && '#' !== $fallback_url ? $fallback_url : home_url( '/contact/' );
	?>
	<aside class="lead-capture" data-lead-capture data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>" aria-label="<?php esc_attr_e( 'Request a callback', 'greendawn' ); ?>">
		<button class="lead-capture__launcher" type="button" data-lead-capture-open data-lead-entry-point="floating-launcher" aria-label="<?php esc_attr_e( 'Request a callback', 'greendawn' ); ?>" aria-haspopup="dialog" hidden>
			<span class="lead-capture__launcher-label"><?php esc_html_e( 'Request a callback', 'greendawn' ); ?></span>
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 5h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-8l-5 3v-3H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" /><path d="M8 10h8M8 14h5" /></svg>
		</button>
		<div data-lead-react></div>
		<div data-lead-transport hidden aria-hidden="true">
			<?php echo do_shortcode( '[contact-form-7 id="' . absint( $config['formId'] ) . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CF7 generates its escaped native form and integration fields. ?>
		</div>
	</aside>
	<?php
}
add_action( 'wp_footer', 'greendawn_lead_render', 5 );
