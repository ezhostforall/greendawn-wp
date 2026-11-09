<?php
/**
 * Sitewide footer content settings and navigation.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/** Returns content defaults supplied by the site plugin. */
function greendawn_footer_defaults(): array {
	return (array) apply_filters(
		'greendawn_footer_defaults',
		array_fill_keys(
			array( 'summary', 'contact', 'cta_label', 'cta_url', 'company_heading', 'solutions_heading', 'support_heading', 'contact_heading', 'phone', 'email', 'legal_name', 'company_number', 'vat_number' ),
			''
		)
	);
}

/**
 * Reads one native Customiser value, preserving intentionally cleared fields.
 *
 * @param string $name Short setting name.
 */
function greendawn_footer_value( string $name ): string {
	$value = get_theme_mod( 'greendawn_footer_' . $name, greendawn_footer_defaults()[ $name ] ?? '' );
	return is_scalar( $value ) ? trim( (string) $value ) : '';
}

/**
 * Registers the footer content controls without requiring SCF.
 *
 * @param WP_Customize_Manager $customizer Customiser instance.
 */
function greendawn_customize_footer( WP_Customize_Manager $customizer ): void {
	$customizer->add_section(
		'greendawn_footer',
		array(
			'title'       => __( 'Footer', 'greendawn' ),
			'description' => __( 'Edit sitewide footer content here. Assigned Footer menus replace the default links.', 'greendawn' ),
		)
	);

	$fields   = array(
		'summary'           => array( __( 'Footer tagline', 'greendawn' ), 'text', 'sanitize_text_field' ),
		'company_heading'   => array( __( 'Company column heading', 'greendawn' ), 'text', 'sanitize_text_field' ),
		'solutions_heading' => array( __( 'Solutions column heading', 'greendawn' ), 'text', 'sanitize_text_field' ),
		'support_heading'   => array( __( 'Support column heading', 'greendawn' ), 'text', 'sanitize_text_field' ),
		'contact_heading'   => array( __( 'Contact column heading', 'greendawn' ), 'text', 'sanitize_text_field' ),
		'phone'             => array( __( 'Telephone number', 'greendawn' ), 'text', 'sanitize_text_field' ),
		'email'             => array( __( 'Email address', 'greendawn' ), 'email', 'sanitize_email' ),
		'contact'           => array( __( 'Registered address / contact details', 'greendawn' ), 'textarea', 'sanitize_textarea_field' ),
		'cta_label'         => array( __( 'Optional contact call-to-action text', 'greendawn' ), 'text', 'sanitize_text_field' ),
		'cta_url'           => array( __( 'Optional contact call-to-action destination', 'greendawn' ), 'url', 'esc_url_raw' ),
		'legal_name'        => array( __( 'Legal company name', 'greendawn' ), 'text', 'sanitize_text_field' ),
		'company_number'    => array( __( 'Company number', 'greendawn' ), 'text', 'sanitize_text_field' ),
		'vat_number'        => array( __( 'VAT number', 'greendawn' ), 'text', 'sanitize_text_field' ),
	);
	$defaults = greendawn_footer_defaults();
	foreach ( $fields as $key => list( $label, $type, $sanitize ) ) {
		$setting = 'greendawn_footer_' . $key;
		$customizer->add_setting(
			$setting,
			array(
				'default'           => $defaults[ $key ] ?? '',
				'sanitize_callback' => $sanitize,
			)
		);
		$customizer->add_control( $setting, array( 'section' => 'greendawn_footer', 'label' => $label, 'type' => $type ) );
	}

	add_action(
		'customize_controls_enqueue_scripts',
		static function () use ( $defaults ): void {
			$config = wp_json_encode(
				array( 'defaults' => $defaults, 'label' => __( 'Reset footer content', 'greendawn' ) ),
				JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
			);
			wp_add_inline_script(
				'customize-controls',
				'(function (config) {
					wp.customize.control("greendawn_footer_vat_number", function (control) {
						control.deferred.embedded.done(function () {
							const button = document.createElement("button");
							button.type = "button";
							button.className = "button";
							button.textContent = config.label;
							button.addEventListener("click", function () {
								Object.entries(config.defaults).forEach(function (entry) {
									wp.customize("greendawn_footer_" + entry[0], function (setting) { setting.set(entry[1]); });
								});
							});
							control.container[0].append(button);
						});
					});
				})(' . $config . ');'
			);
		}
	);
}
add_action( 'customize_register', 'greendawn_customize_footer' );

/**
 * Keeps imported Greendawn links on the current WordPress environment.
 *
 * @param string $url Saved or default destination.
 */
function greendawn_footer_local_url( string $url ): string {
	$parts = wp_parse_url( $url );
	if ( is_array( $parts ) && in_array( strtolower( $parts['host'] ?? '' ), array( 'greendawn.co.uk', 'www.greendawn.co.uk' ), true ) ) {
		$path  = $parts['path'] ?? '/';
		$path .= isset( $parts['query'] ) ? '?' . $parts['query'] : '';
		$path .= isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '';
		return home_url( $path );
	}
	return $url;
}

add_filter(
	'nav_menu_link_attributes',
	static function ( array $attributes, $item, $args ): array {
		if ( str_starts_with( $args->theme_location ?? '', 'footer' ) && isset( $attributes['href'] ) ) {
			$attributes['href'] = greendawn_footer_local_url( $attributes['href'] );
		}
		return $attributes;
	},
	10,
	3
);

add_filter(
	'nav_menu_item_id',
	static function ( $id, $item, $args ) {
		return str_starts_with( $args->theme_location ?? '', 'footer' ) ? '' : $id;
	},
	10,
	3
);

/**
 * Renders an assigned native menu or the site plugin's default links.
 *
 * @param string $location Menu location.
 * @param string $label Accessible navigation label and optional visible heading.
 * @param bool   $heading Whether to show the column heading.
 */
function greendawn_footer_menu( string $location, string $label, bool $heading = false ): void {
	$arrow = '';
	if ( 'footer_social' === $location ) {
		ob_start();
		get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) );
		$arrow = ob_get_clean();
	}
	$menu = '';
	if ( has_nav_menu( $location ) ) {
		$menu = wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => 'gd-footer__links',
				'menu_id'        => 'greendawn-' . $location,
				'depth'          => 1,
				'fallback_cb'    => false,
				'echo'           => false,
				'link_after'     => $arrow,
			)
		);
	} else {
		$links = (array) apply_filters( 'greendawn_footer_links', array(), $location );
		$items = '';
		foreach ( $links as $link ) {
			$url  = esc_url( greendawn_footer_local_url( (string) ( $link['url'] ?? '' ) ) );
			$text = esc_html( $link['label'] ?? '' );
			if ( '' !== $url && '' !== $text ) {
				$items .= '<li><a href="' . $url . '">' . $text . $arrow . '</a></li>';
			}
		}
		if ( '' !== $items ) {
			$menu = '<ul class="gd-footer__links">' . $items . '</ul>';
		}
	}
	if ( ! $menu ) {
		return;
	}
	$aria_label = '' !== $label ? $label : ( get_registered_nav_menus()[ $location ] ?? __( 'Footer links', 'greendawn' ) );
	?>
	<nav class="<?php echo esc_attr( 'footer-nav' . ( $heading ? ' gd-footer__column' : '' ) ); ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>">
		<?php if ( $heading && '' !== $label ) : ?>
			<span><?php echo esc_html( $label ); ?></span>
		<?php endif; ?>
		<?php echo $menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core menu markup or links escaped above, with a static decorative arrow. ?>
	</nav>
	<?php
}
