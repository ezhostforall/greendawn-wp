<?php
/**
 * Carries consented advertising identifiers through configured native sales forms.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/** Extends sales form transport and internal notifications in memory. */
final class Advertising_Attribution implements Module_Interface {

	private const IDENTIFIERS = array(
		'gclid',
		'gbraid',
		'wbraid',
		'gad_source',
		'gad_campaignid',
		'gclsrc',
		'dclid',
		'msclkid',
		'fbclid',
		'ttclid',
		'li_fat_id',
		'twclid',
		'sccid',
		'epik',
		'rdt_cid',
	);

	private const CAMPAIGNS = array( 'ad_utm_source', 'ad_utm_medium', 'ad_utm_campaign', 'ad_utm_term', 'ad_utm_content' );

	private const SALES_TEMPLATES = array(
		'templates/greendawn-about.php'                  => 'greendawn_about_form',
		'templates/greendawn-workplace.php'              => 'greendawn_workplace_form',
		'templates/greendawn-enquire.php'                => 'greendawn_enquire_form',
		'templates/greendawn-business-installations.php' => 'greendawn_business_form',
	);

	private const SUPPORT_TEMPLATES = array(
		'templates/greendawn-aftercare.php'     => 'greendawn_aftercare_form',
		'templates/greendawn-customer-care.php' => 'greendawn_customer_care_form',
	);

	/** Register the native CF7 extension points. */
	public function register(): void {
		add_filter( 'wpcf7_contact_form_properties', array( $this, 'form_properties' ), 20, 2 );
		add_filter( 'wpcf7_posted_data', array( $this, 'posted_data' ), 20 );
		add_filter( 'wpcf7_mail_components', array( $this, 'mail_components' ), 20, 3 );
	}

	/**
	 * Resolve eligibility from WordPress assignments. Support takes precedence if an ID is shared.
	 *
	 * @param int $id Native form ID.
	 */
	private function is_sales_form( int $id ): bool {
		$sales = in_array( $id, array( absint( get_option( 'greendawn_core_callback_form', 0 ) ), absint( get_theme_mod( 'greendawn_contact_form', 0 ) ) ), true );
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'has_password'   => false,
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'orderby'        => 'none',
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Cached lookup limited to the sales and support templates below.
					array(
						'key'     => '_wp_page_template',
						'value'   => array_keys( self::SALES_TEMPLATES + self::SUPPORT_TEMPLATES ),
						'compare' => 'IN',
					),
				),
			)
		);
		update_meta_cache( 'post', $pages );
		foreach ( $pages as $page ) {
			$template = get_page_template_slug( $page );
			$key      = self::SALES_TEMPLATES[ $template ] ?? self::SUPPORT_TEMPLATES[ $template ] ?? '';
			if ( '' === $key || absint( get_post_meta( $page, $key, true ) ) !== $id ) {
				continue;
			}
			if ( isset( self::SUPPORT_TEMPLATES[ $template ] ) ) {
				return false;
			}
			$sales = true;
		}
		return $sales;
	}

	/**
	 * Add hidden transport fields without modifying the saved form or mail properties.
	 *
	 * @param array              $properties CF7 form properties.
	 * @param \WPCF7_ContactForm $form CF7 form.
	 */
	public function form_properties( array $properties, \WPCF7_ContactForm $form ): array {
		if ( ! isset( $properties['form'] ) || is_admin() || ! $form->id() || ! $this->is_sales_form( $form->id() ) ) {
			return $properties;
		}
		foreach ( array_merge( self::IDENTIFIERS, self::CAMPAIGNS ) as $name ) {
			if ( ! preg_match( '/\[[a-z0-9_*]+\s+' . preg_quote( $name, '/' ) . '(?:\s|\])/', $properties['form'] ) ) {
				$properties['form'] .= "\n[hidden " . $name . ']';
			}
		}
		return $properties;
	}

	/**
	 * Discard invalid values without preventing delivery of the enquiry.
	 *
	 * @param array $data Native CF7 submission fields.
	 */
	public function posted_data( array $data ): array {
		if ( ! array_intersect_key( $data, array_fill_keys( array_merge( self::IDENTIFIERS, self::CAMPAIGNS ), true ) ) ) {
			return $data;
		}
		$form = wpcf7_get_current_contact_form();
		if ( ! $form || ! $this->is_sales_form( $form->id() ) ) {
			return $data;
		}

		// The same Complianz preference used by the browser, read again on the submission request.
		$allowed = isset( $_COOKIE['cmplz_marketing'] ) && 'allow' === $_COOKIE['cmplz_marketing']; // phpcs:ignore WordPress.Security -- Strict comparison to Complianz's allow token. The cookie does not authorise an operation.
		$support = $form->id() === absint( get_option( 'greendawn_core_callback_form', 0 ) ) && in_array( 'aftercare', (array) ( $data['intent'] ?? array() ), true );
		foreach ( array_merge( self::IDENTIFIERS, self::CAMPAIGNS ) as $name ) {
			$value = $data[ $name ] ?? '';
			if ( ! $allowed || $support || ! is_string( $value ) ) {
				$data[ $name ] = '';
			} elseif ( in_array( $name, self::IDENTIFIERS, true ) ) {
				$data[ $name ] = preg_match( '#^[A-Za-z0-9._~+/=-]{1,1024}$#D', $value ) ? $value : '';
			} else {
				$data[ $name ] = preg_match( '/^[^\p{Cc}]{1,120}$/uD', $value ) ? sanitize_text_field( $value ) : '';
			}
		}
		foreach ( self::IDENTIFIERS as $name ) {
			if ( '' !== $data[ $name ] ) {
				return $data;
			}
		}
		return array_replace( $data, array_fill_keys( self::CAMPAIGNS, '' ) );
	}

	/**
	 * Append non-empty advertising details to the primary internal notification only.
	 *
	 * @param array                   $components Rendered notification.
	 * @param \WPCF7_ContactForm|null $form Form owning the notification.
	 * @param \WPCF7_Mail             $mail Notification template.
	 */
	public function mail_components( array $components, ?\WPCF7_ContactForm $form, \WPCF7_Mail $mail ): array {
		$submission = \WPCF7_Submission::get_instance();
		if ( 'mail' !== $mail->name() || ! $submission || ! $form || ! $this->is_sales_form( $form->id() ) ) {
			return $components;
		}
		$template = $form->prop( 'mail' );
		$data     = $submission->get_posted_data();
		$lines    = array();
		foreach ( array_merge( self::IDENTIFIERS, self::CAMPAIGNS ) as $name ) {
			$value = $data[ $name ] ?? '';
			if ( is_string( $value ) && '' !== $value && ! str_contains( (string) ( $template['body'] ?? '' ), '[' . $name . ']' ) ) {
				$lines[] = $name . ': ' . $value;
			}
		}
		if ( ! $lines ) {
			return $components;
		}
		$heading = __( 'Advertising attribution', 'greendawn-core' );
		if ( empty( $template['use_html'] ) ) {
			$components['body'] .= "\n\n" . $heading . "\n" . implode( "\n", $lines );
			return $components;
		}
		$block = '<p><strong>' . esc_html( $heading ) . '</strong><br>' . implode( '<br>', array_map( 'esc_html', $lines ) ) . '</p>';
		if ( false !== stripos( $components['body'], '</body>' ) ) {
			$components['body'] = str_ireplace( '</body>', $block . '</body>', $components['body'] );
		} else {
			$components['body'] .= $block;
		}
		return $components;
	}
}
