<?php
/**
 * Client-owned footer content and internal navigation defaults.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/** Supplies the content shared by every footer. */
final class Greendawn_Footer implements Module_Interface {

	/** Registers defaults without requiring SCF. */
	public function register(): void {
		add_filter( 'greendawn_footer_defaults', array( self::class, 'defaults' ) );
		add_filter( 'greendawn_footer_links', array( $this, 'links' ), 10, 2 );
	}

	/**
	 * Supplies copy from the pinned Astro footer and site configuration.
	 *
	 * @param array $defaults Existing defaults.
	 */
	public static function defaults( array $defaults = array() ): array {
		return array_merge(
			$defaults,
			array(
				'summary'           => __( 'EV charging. Handled.', 'greendawn-core' ),
				'company_heading'   => __( 'Company', 'greendawn-core' ),
				'solutions_heading' => __( 'Our Solutions', 'greendawn-core' ),
				'support_heading'   => __( 'Support', 'greendawn-core' ),
				'contact_heading'   => __( 'Contact', 'greendawn-core' ),
				'phone'             => '0345 512 0044',
				'email'             => 'connect@greendawn.co.uk',
				'contact'           => "Office 2.026, Innovation Centre\n7 University Avenue, Keele\nNewcastle-under-Lyme, England, ST5 5NU",
				'legal_name'        => __( 'Greendawn Power Limited', 'greendawn-core' ),
				'company_number'    => '12758202',
				'vat_number'        => 'GB359248367',
			)
		);
	}

	/**
	 * Supplies internal links until a WordPress menu is assigned.
	 *
	 * @param array  $links    Existing fallback links.
	 * @param string $location WordPress menu location.
	 */
	public function links( array $links, string $location ): array {
		$menus = array(
			'footer'           => array(
				array(
					'label' => __( 'About Us', 'greendawn-core' ),
					'url'   => home_url( '/about-us/' ),
				),
				array(
					'label' => __( 'Our Projects', 'greendawn-core' ),
					'url'   => home_url( '/gallery/' ),
				),
			),
			'footer_secondary' => array(
				array(
					'label' => __( 'Workplace Charging', 'greendawn-core' ),
					'url'   => home_url( '/workplace-charging/' ),
				),
				array(
					'label' => __( 'Fleet Charging', 'greendawn-core' ),
					'url'   => home_url( '/fleet-charging-solutions/' ),
				),
				array(
					'label' => __( 'Public Charging', 'greendawn-core' ),
					'url'   => home_url( '/public-charging/' ),
				),
			),
			'footer_support'   => array(
				array(
					'label' => __( 'Aftercare', 'greendawn-core' ),
					'url'   => home_url( '/aftercare/' ),
				),
				array(
					'label' => __( 'Customer support', 'greendawn-core' ),
					'url'   => home_url( '/customer-care/' ),
				),
				array(
					'label' => __( 'Insights', 'greendawn-core' ),
					'url'   => home_url( '/blog/' ),
				),
			),
			'footer_legal'     => array(
				array(
					'label' => __( 'Privacy', 'greendawn-core' ),
					'url'   => home_url( '/privacy-policy/' ),
				),
				array(
					'label' => __( 'Terms', 'greendawn-core' ),
					'url'   => home_url( '/terms-and-conditions/' ),
				),
				array(
					'label' => __( 'Modern Slavery', 'greendawn-core' ),
					'url'   => home_url( '/modern-slavery-statements/' ),
				),
			),
			'footer_social'    => array(
				array(
					'label' => __( 'LinkedIn', 'greendawn-core' ),
					'url'   => 'https://www.linkedin.com/company/greendawnpower/',
				),
			),
		);

		$enquiry = esc_url_raw( (string) get_theme_mod( 'greendawn_header_cta_url', '' ) );
		if ( '' !== $enquiry && '#' !== $enquiry ) {
			$menus['footer_support'][] = array(
				'label' => __( 'Discuss your site', 'greendawn-core' ),
				'url'   => $enquiry,
			);
		}

		return $menus[ $location ] ?? $links;
	}
}
