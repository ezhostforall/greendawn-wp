<?php
/**
 * Commercial callback form configuration and Contact Form 7 validation.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps delivery and form policy outside the theme.
 */
final class Lead_Capture implements Module_Interface {

	/** Registers the optional integration. */
	public function register(): void {
		add_action( 'admin_init', array( $this, 'settings' ) );
		add_action( 'admin_post_greendawn_core_create_callback', array( $this, 'create_form' ) );
		add_filter( 'greendawn_lead_config', array( $this, 'configuration' ) );
		add_filter( 'wpcf7_contact_form_properties', array( $this, 'attribution_properties' ), 10, 2 );
		add_filter( 'wpcf7_validate', array( $this, 'validate' ), 20, 2 );
		add_filter( 'wpcf7_spam', array( $this, 'spam' ), 20, 2 );
	}

	/** Registers the administrator opt-in. */
	public function settings(): void {
		register_setting(
			'greendawn_core_callback_settings',
			'greendawn_core_callback_enabled',
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'wp_validate_boolean',
			)
		);
	}

	/**
	 * Pinned Astro choice values and labels, shared with server validation.
	 *
	 * @return array<string,array<string,string>>
	 */
	public static function choices(): array {
		return array(
			'intent'             => array(
				'ev-charging'   => 'EV charging',
				'power'         => 'Power / grid capacity',
				'solar-battery' => 'Solar & battery',
				'aftercare'     => 'Existing chargers / aftercare',
				'unsure'        => 'Not sure yet',
			),
			'projectStage'       => array(
				'exploring' => 'Just exploring',
				'planning'  => 'Planning a project',
				'quote'     => 'Need a quote',
				'existing'  => 'Already have infrastructure',
				'problem'   => 'Something isn’t working',
			),
			'callbackPreference' => array(
				'asap'      => 'As soon as possible',
				'today'     => 'Today',
				'tomorrow'  => 'Tomorrow',
				'scheduled' => 'Suggest a time',
			),
			'siteType'           => array(
				'workplace'          => 'Workplace',
				'fleet-depot'        => 'Fleet / depot',
				'public-destination' => 'Public / destination',
				'motor-dealership'   => 'Motor dealership',
				'other'              => 'Other',
				'unsure'             => 'Not sure',
			),
			'aftercareIssue'     => array(
				'offline'          => 'Chargers offline',
				'slow-charging'    => 'Charging too slowly',
				'payment-software' => 'Payment / software issue',
				'maintenance'      => 'Maintenance',
				'other'            => 'Something else',
				'unsure'           => 'Not sure',
			),
			'powerReason'        => array(
				'capacity'     => 'Not enough capacity',
				'grid-upgrade' => 'Grid upgrade',
				'expansion'    => 'Future expansion',
				'ev-demand'    => 'EV charging demand',
				'other'        => 'Something else',
				'unsure'       => 'Not sure',
			),
		);
	}

	/**
	 * Limits apply before the native CF7 mail renderer, including direct HTTP posts.
	 *
	 * @return array<string,int>
	 */
	public static function limits(): array {
		return array(
			'firstName'             => 80,
			'company'               => 120,
			'phone'                 => 40,
			'email'                 => 160,
			'location'              => 120,
			'preferredCallbackTime' => 30,
			'notes'                 => 1500,
			'pagePath'              => 240,
			'entryPoint'            => 30,
			'source'                => 30,
			'utmSource'             => 120,
			'utmMedium'             => 120,
			'utmCampaign'           => 120,
			'utmTerm'               => 120,
			'utmContent'            => 120,
			'page-url'              => 2048,
			'first_landing'         => 2048,
			'first_referrer'        => 2048,
			'website'               => 200,
		);
	}

	/**
	 * Attribution fields extend existing managed forms without rebuilding saved mail settings.
	 *
	 * @return string[]
	 */
	public static function attribution_fields(): array {
		return array( 'page-url', 'first_landing', 'first_referrer', 'utmSource', 'utmMedium', 'utmCampaign', 'utmTerm', 'utmContent' );
	}

	/**
	 * Extends only the configured callback form in memory; saved properties stay intact.
	 *
	 * @param array              $properties Existing CF7 properties.
	 * @param \WPCF7_ContactForm $form Form being loaded.
	 * @return array
	 */
	public function attribution_properties( array $properties, \WPCF7_ContactForm $form ): array {
		$id = absint( get_option( 'greendawn_core_callback_form', 0 ) );
		if ( ! $id || $form->id() !== $id ) {
			return $properties;
		}
		$markup = (string) ( $properties['form'] ?? '' );
		$extra  = array();
		$mail   = $properties['mail'] ?? array();
		foreach ( self::attribution_fields() as $name ) {
			if ( ! preg_match( '/\[[a-z0-9_*]+\s+' . preg_quote( $name, '/' ) . '(?:\s|\])/', $markup ) ) {
				$markup .= "\n[hidden " . $name . ']';
			}
			if ( ! str_contains( (string) ( $mail['body'] ?? '' ), '[' . $name . ']' ) ) {
				$extra[] = $name . ': [' . $name . ']';
			}
		}
		$properties['form'] = $markup;
		if ( $extra ) {
			$mail['body']       = ( $mail['body'] ?? '' ) . "\n\nVisit attribution\n" . implode( "\n", $extra );
			$properties['mail'] = $mail;
		}
		return $properties;
	}

	/**
	 * Supplies the managed form only after an administrator enables it.
	 *
	 * @param array $config Existing configuration.
	 * @return array
	 */
	public function configuration( array $config ): array {
		$id = absint( get_option( 'greendawn_core_callback_form', 0 ) );
		if ( ! get_option( 'greendawn_core_callback_enabled', false ) || ! function_exists( 'wpcf7_contact_form' ) || ! wpcf7_contact_form( $id ) ) {
			return $config;
		}
		return array(
			'formId'  => $id,
			'choices' => self::choices(),
		);
	}

	/**
	 * Builds a separate CF7 form, without changing existing contact forms.
	 *
	 * @return int New or existing managed form ID.
	 */
	public static function provision(): int {
		$id = absint( get_option( 'greendawn_core_callback_form', 0 ) );
		if ( ! class_exists( '\WPCF7_ContactForm' ) || ( $id && wpcf7_contact_form( $id ) ) ) {
			return $id;
		}
		$form = \WPCF7_ContactForm::get_template();
		$form->set_title( __( 'Greendawn callback requests', 'greendawn-core' ) );
		$markup = array();
		foreach ( self::limits() as $name => $limit ) {
			$type = 'notes' === $name ? 'textarea' : ( 'email' === $name ? 'email*' : ( 'phone' === $name ? 'tel*' : 'text' ) );
			if ( in_array( $name, array( 'firstName', 'company' ), true ) ) {
				$type .= '*';
			}
			$markup[] = in_array( $name, self::attribution_fields(), true )
				? '[hidden ' . $name . ']'
				: sprintf( '<label>%1$s [%2$s %1$s maxlength:%3$d]</label>', $name, $type, $limit );
		}
		foreach ( self::choices() as $name => $choices ) {
			$type     = 'callbackPreference' === $name ? 'select*' : 'select';
			$markup[] = sprintf( '<label>%1$s [%2$s %1$s include_blank "%3$s"]</label>', $name, $type, implode( '" "', array_keys( $choices ) ) );
		}
		$markup[]                   = '[submit "Request callback"]';
		$mail                       = $form->prop( 'mail' );
		$mail['recipient']          = get_option( 'admin_email' );
		$mail['subject']            = '[Greendawn] Commercial callback request';
		$mail['additional_headers'] = 'Reply-To: [email]';
		$mail['body']               = "Commercial callback request\n\n";
		foreach ( array_merge( array_keys( self::choices() ), array_keys( self::limits() ) ) as $name ) {
			if ( 'website' !== $name ) {
				$mail['body'] .= $name . ': [' . $name . "]\n";
			}
		}
		$mail['body'] .= "\nReceived: [_date] [_time]\nSuggested callback times are preferences, not confirmed appointments.";
		$form->set_properties(
			array(
				'form' => implode( "\n", $markup ),
				'mail' => $mail,
			)
		);
		$id = $form->save();
		// A newly created/replacement form needs a fresh recipient review.
		update_option( 'greendawn_core_callback_enabled', false );
		update_option( 'greendawn_core_callback_form', $id, false );
		return (int) $id;
	}

	/** Handles the nonce-protected administrator setup button. */
	public function create_form(): void {
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'wpcf7_edit_contact_forms' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- CF7 maps its own form-edit capability.
			wp_die( esc_html__( 'You cannot configure callback forms.', 'greendawn-core' ) );
		}
		check_admin_referer( 'greendawn_core_create_callback' );
		self::provision();
		wp_safe_redirect( admin_url( 'options-general.php?page=greendawn-core' ) );
		exit;
	}

	/**
	 * Checks the selected managed form rather than trusting a client-side marker.
	 */
	private function is_managed(): bool {
		$form = function_exists( 'wpcf7_get_current_contact_form' ) ? wpcf7_get_current_contact_form() : null;
		return $form && $form->id() === absint( get_option( 'greendawn_core_callback_form', 0 ) );
	}

	/**
	 * Additional server-side validation for the qualified callback contract.
	 *
	 * @param \WPCF7_Validation $result CF7 validation result.
	 * @param array             $tags Form tags.
	 * @return \WPCF7_Validation
	 */
	public function validate( $result, array $tags ) {
		if ( ! $this->is_managed() ) {
			return $result;
		}
		$submission            = \WPCF7_Submission::get_instance();
		$data                  = $submission ? $submission->get_posted_data() : array();
		$choices               = self::choices();
		$choices['source']     = array_fill_keys( array( 'homepage', 'website' ), '' );
		$choices['entryPoint'] = array_fill_keys( array( 'floating-launcher', 'header', 'hero', 'services', 'pricing', 'final-cta', 'footer-cta' ), '' );
		$limits                = self::limits();
		foreach ( $tags as $tag ) {
			$name  = $tag->name;
			$value = $data[ $name ] ?? '';
			// CF7 represents empty optional selects as [], answered selects as [value].
			if ( isset( $choices[ $name ] ) && is_array( $value ) && count( $value ) <= 1 ) {
				$value = $value ? reset( $value ) : '';
			}
			if ( ! is_scalar( $value ) || ( isset( $limits[ $name ] ) && strlen( (string) $value ) > $limits[ $name ] * 4 ) || ( isset( $choices[ $name ] ) && '' !== $value && ! isset( $choices[ $name ][ $value ] ) ) ) {
				$result->invalidate( $tag, __( 'Please check this value.', 'greendawn-core' ) );
				continue;
			}
			$value = trim( (string) $value );
			if ( in_array( $name, array( 'firstName', 'company', 'phone', 'email', 'callbackPreference' ), true ) && '' === $value ) {
				$result->invalidate( $tag, __( 'Please complete this field.', 'greendawn-core' ) );
			}
			if ( 'phone' === $name && ( ! preg_match( '/^[+\d][\d\s().\/-]*(?:\s?(?:x|ext\.?)\s?\d{1,6})?$/i', $value ) || strlen( preg_replace( '/\D/', '', $value ) ) < 7 || strlen( preg_replace( '/\D/', '', $value ) ) > 18 ) ) {
				$result->invalidate( $tag, __( 'Enter a telephone number we can use for the callback.', 'greendawn-core' ) );
			}
			if ( 'preferredCallbackTime' === $name && '' !== $value && ! preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value ) ) {
				$result->invalidate( $tag, __( 'Please check the suggested date and time.', 'greendawn-core' ) );
			}
		}
		return $result;
	}

	/**
	 * Adds a honeypot and modest hashed-IP throttle to native CF7 spam checks.
	 * No form values or raw IP addresses are stored by this module.
	 *
	 * @param bool              $spam Existing spam verdict.
	 * @param \WPCF7_Submission $submission Current submission.
	 * @return bool
	 */
	public function spam( bool $spam, $submission ): bool {
		if ( ! $this->is_managed() || $spam ) {
			return $spam;
		}
		if ( ! get_option( 'greendawn_core_callback_enabled', false ) || $submission->get_posted_data( 'website' ) ) {
			return true;
		}
		$ip    = (string) $submission->get_meta( 'remote_ip' );
		$key   = 'greendawn_callback_' . hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) );
		$count = (int) get_transient( $key );
		if ( $count >= 5 ) {
			return true;
		}
		set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );
		return false;
	}
}
