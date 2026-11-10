<?php
/**
 * Content-only fields and defaults for the fixed Greendawn Public Charging template.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps page content in Core and page composition in the theme.
 */
final class Greendawn_Public_Charging implements Module_Interface {

	private const TEMPLATE = 'templates/greendawn-public-charging.php';

	/** Registers the structured editing interface and server-side data adapter. */
	public function register(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'greendawn_public_field_value', array( $this, 'field_value' ), 10, 3 );
		add_filter( 'use_block_editor_for_post', array( $this, 'use_block_editor' ), 10, 2 );
		add_action( 'add_meta_boxes_page', array( $this, 'hide_body_editor' ) );
	}

	/**
	 * Disables Gutenberg only on this fixed template.
	 *
	 * @param bool     $use_editor Existing decision.
	 * @param \WP_Post $post Current record.
	 */
	public function use_block_editor( bool $use_editor, \WP_Post $post ): bool {
		return $use_editor && self::TEMPLATE !== get_page_template_slug( $post );
	}

	/**
	 * Preserves the imported body in storage without exposing layout editing.
	 *
	 * @param \WP_Post $post Current page.
	 */
	public function hide_body_editor( \WP_Post $post ): void {
		if ( self::TEMPLATE === get_page_template_slug( $post ) ) {
			remove_post_type_support( 'page', 'editor' );
		}
	}

	/**
	 * Defines copy defaults from the approved source inventory, not in the template.
	 *
	 * @return array<string,array>
	 */
	public function fields(): array {
		$copy     = array(
			'heading'           => array( __( 'Hero heading', 'greendawn' ), 'Public EV' ),
			'accent'            => array( __( 'Hero accent', 'greendawn' ), 'Charging Solutions' ),
			'intro'             => array( __( 'Hero introduction', 'greendawn' ), 'Optimise Public Charging: Reliable, Revenue-Ready, and Easy to Manage', 'textarea' ),
			'benefits_heading'  => array( __( 'Benefits heading', 'greendawn' ), 'Why Install Public EV Charging?' ),
			'chargers_heading'  => array( __( 'Chargers heading', 'greendawn' ), 'The Right Chargers for Public Sites' ),
			'chargers_intro'    => array( __( 'Chargers introduction', 'greendawn' ), 'For public use, we recommend a combination of 22 kW fast AC chargers and rapid DC chargers:', 'textarea' ),
			'ac_title'          => array( __( 'AC guidance label', 'greendawn' ), '22 kW AC Chargers:' ),
			'ac_copy'           => array( __( 'AC guidance', 'greendawn' ), 'Perfect for destinations like retail parks, gyms, or hotels where dwell time is longer. Affordable, reliable, and ideal for consistent revenue from longer stays.', 'textarea' ),
			'dc_title'          => array( __( 'DC guidance label', 'greendawn' ), 'Rapid DC Chargers:' ),
			'dc_copy'           => array( __( 'DC guidance', 'greendawn' ), 'Essential for roadside, service stations, and high-traffic sites where drivers expect a fast turnaround. Higher initial investment, but greater earning potential from quick throughput.', 'textarea' ),
			'chargers_outro'    => array( __( 'Chargers closing paragraph', 'greendawn' ), 'This mixed approach ensures you can serve both long-stay visitors and quick-stop drivers, maximising usage and ROI.', 'textarea' ),
			'cta_heading'       => array( __( 'CTA heading', 'greendawn' ), 'Not sure what the right public charging setup looks like?' ),
			'cta_copy'          => array( __( 'CTA introduction', 'greendawn' ), "Every site is different. Power capacity, user demand, and revenue potential all play a part.\nWe’ll help you understand what works before anything is installed.", 'textarea' ),
			'cta_label'         => array( __( 'CTA button label', 'greendawn' ), 'Start Your Journey' ),
			'cta_url'           => array( __( 'CTA destination', 'greendawn' ), home_url( '/enquire/' ), 'url' ),
			'final_cta_heading' => array( __( 'Final CTA heading', 'greendawn' ), 'Ready to Power Public EV Charging?' ),
			'final_cta_copy'    => array( __( 'Final CTA introduction', 'greendawn' ), 'Turn EV charging into a new revenue stream while growing your customer base and futureproofing your business.', 'textarea' ),
			'final_cta_label'   => array( __( 'Final CTA button label', 'greendawn' ), 'Book a Free Consultation' ),
			'final_cta_url'     => array( __( 'Final CTA destination', 'greendawn' ), home_url( '/enquire/' ), 'url' ),
			'posts_heading'     => array( __( 'Articles heading', 'greendawn' ), 'Our Content' ),
		);
		$benefits = array(
			'revenue'        => array( 'New Revenue Streams', 'Earn from every charge. Public charging stations provide ongoing income while offsetting installation costs.' ),
			'loyalty'        => array( 'Customer Attraction & Loyalty', 'Drivers are more likely to stop, spend, and return when you offer convenient EV charging at your site.' ),
			'futureproof'    => array( 'Futureproof Your Business', 'With EV adoption accelerating, public charging positions your business as forward-thinking and ready to meet customer demand.' ),
			'sustainability' => array( 'Sustainability & Brand Value', 'Demonstrate commitment to green transport, enhancing your reputation and compliance with environmental targets.' ),
		);
		foreach ( $benefits as $key => $benefit ) {
			$copy[ $key . '_title' ] = array( $benefit[0] . ' — ' . __( 'title', 'greendawn' ), $benefit[0] );
			$copy[ $key . '_copy' ]  = array( $benefit[0] . ' — ' . __( 'description', 'greendawn' ), $benefit[1], 'textarea' );
		}
		foreach ( Greendawn_Process::defaults() as $name => $default ) {
			$copy[ $name ] = array( ucwords( str_replace( '_', ' ', $name ) ), $default, str_ends_with( $name, '_copy' ) || 'process_intro' === $name ? 'textarea' : 'text' );
		}
		$fields = array();
		foreach ( $copy as $name => $settings ) {
			$fields[ $name ] = array(
				'key'           => 'field_greendawn_public_' . $name,
				'name'          => 'greendawn_public_' . $name,
				'label'         => $settings[0],
				'type'          => $settings[2] ?? 'text',
				'default_value' => $settings[1],
			);
		}
		foreach ( array(
			'hero_image' => '2026/11/commercial-ev-destination-hospitality-1200.webp',
		) as $name => $upload ) {
			$fields[ $name ] = array(
				'key'            => 'field_greendawn_public_' . $name,
				'name'           => 'greendawn_public_' . $name,
				'label'          => __( 'Hero photograph', 'greendawn' ),
				'type'           => 'image',
				'return_format'  => 'id',
				'default_upload' => $upload,
				'instructions'   => __( 'Select an image from this site’s Media Library. Unset fields use the matching uploaded reference image when available. Clearing a saved selection removes the image.', 'greendawn' ),
			);
		}
		foreach ( array(
			'chargers' => array( __( 'Recommended chargers — drag to reorder', 'greendawn' ), 'evcharger' ),
			'posts'    => array( __( 'Related articles — drag to reorder', 'greendawn' ), 'post' ),
		) as $name => $settings ) {
			$fields[ $name ] = array(
				'key'           => 'field_greendawn_public_' . $name,
				'name'          => 'greendawn_public_' . $name,
				'label'         => $settings[0],
				'type'          => 'relationship',
				'post_type'     => array( $settings[1] ),
				'post_status'   => array( 'publish' ),
				'filters'       => array( 'search' ),
				'elements'      => array( 'featured_image' ),
				'return_format' => 'id',
				'instructions'  => __( 'Only published records appear publicly. Clearing a saved selection removes the section’s records.', 'greendawn' ),
			);
		}
		foreach ( $fields as $name => &$field ) {
			if ( 'heading' === $name || str_ends_with( $name, '_heading' ) || str_ends_with( $name, '_title' ) ) {
				$field['required'] = 1;
			}
		}
		unset( $field );
		foreach ( array_keys( Greendawn_Process::defaults() ) as $name ) {
			$fields[ $name ]['instructions'] = __( 'Approved shared wording is the initial default. Saved edits belong to this Page and do not change other pages. Clearing a saved value leaves it empty; paste the approved wording to restore it.', 'greendawn' );
		}
		return $fields;
	}

	/**
	 * Provides raw data with the same defaults whether SCF is active or not.
	 *
	 * @param mixed  $fallback Value if Core does not know this field.
	 * @param string $name Short field name.
	 * @param int    $post_id Page being rendered.
	 * @return mixed
	 */
	public function field_value( $fallback, string $name, int $post_id ) {
		$field = $this->fields()[ $name ] ?? null;
		if ( ! $field ) {
			return $fallback;
		}
		return $this->load_default( get_post_meta( $post_id, $field['name'], true ), $post_id, $field );
	}

	/**
	 * Defaults are applied only to unset metadata; saved blank values stay blank.
	 *
	 * @param mixed $value Existing value.
	 * @param mixed $post_id SCF object identifier.
	 * @param array $field Field definition.
	 * @return mixed
	 */
	public function load_default( $value, $post_id, array $field ) {
		$post_id = absint( $post_id );
		if ( ! metadata_exists( 'post', $post_id, $field['name'] ) ) {
			if ( 'relationship' === $field['type'] ) {
				$query = array(
					'post_type'      => $field['post_type'][0],
					'post_status'    => 'publish',
					'has_password'   => false,
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'orderby'        => array(
						'menu_order' => 'ASC',
						'title'      => 'ASC',
					),
				);
				if ( 'post' === $field['post_type'][0] ) {
					$query['post_name__in'] = array( 'ozev-ev-charger-grants-from-april-2026', 'ev-charging-for-car-dealership', 'ev-charger-earthing', 'greendawn-ev-charging-survey-pre-installation-guide', 'transitioning-to-electric-fleets', 'ev-charging-cable-theft', 'zaptec-status-codes', 'project-ev-fault-codes', 'is-ozev-funding-enough', 'ev-installation-day-and-beyond' );
					$query['orderby']       = 'post_name__in';
				}
				if ( 'evcharger' === $field['post_type'][0] ) {
					$query['post_name__in'] = array( 'hellonext-h1-22', 'project-ev-ac-pro-earth-dual-7-22kw', 'simpson-partners-7-22kw', 'sync-ev-22kw', 'sync-ev-22kw-2', 'easee-charger-22kw' );
					$query['orderby']       = 'post_name__in';
				}
				return get_posts( $query );
			}
			if ( 'image' === $field['type'] ) {
				if ( 'greendawn_public_hero_image' === $field['name'] && has_post_thumbnail( $post_id ) ) {
					return get_post_thumbnail_id( $post_id );
				}
				$uploads  = wp_get_upload_dir();
				$url      = trailingslashit( $uploads['baseurl'] ) . $field['default_upload'];
				$image_id = attachment_url_to_postid( $url );
				return $image_id ? $image_id : attachment_url_to_postid( preg_replace( '/(\.[^\/.]+)$/', '-scaled$1', $url ) );
			}
			$value = $field['default_value'] ?? '';
		}
		if ( 'url' === $field['type'] && is_string( $value ) ) {
			$parts = wp_parse_url( $value );
			if ( in_array( strtolower( $parts['host'] ?? '' ), array( 'greendawn.co.uk', 'www.greendawn.co.uk' ), true ) ) {
				$value = home_url( ( $parts['path'] ?? '/' ) . ( isset( $parts['query'] ) ? '?' . $parts['query'] : '' ) . ( isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '' ) );
			}
		}
		return $value;
	}

	/**
	 * Restricts relationship searches to published records.
	 *
	 * @param array $query SCF query arguments.
	 * @return array
	 */
	public function published_choices( array $query ): array {
		$query['post_status']  = 'publish';
		$query['has_password'] = false;
		return $query;
	}

	/** Registers fields grouped by the template’s fixed sections. */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		$tabs   = array(
			'heading'           => __( 'Hero', 'greendawn' ),
			'benefits_heading'  => __( 'Benefits', 'greendawn' ),
			'chargers_heading'  => __( 'Chargers', 'greendawn' ),
			'cta_heading'       => __( 'Call to action', 'greendawn' ),
			'process_title'     => __( 'Approved process', 'greendawn' ),
			'final_cta_heading' => __( 'Final call to action', 'greendawn' ),
			'posts_heading'     => __( 'Articles', 'greendawn' ),
		);
		$groups = array(
			'heading'           => array( 'heading', 'accent', 'intro', 'hero_image' ),
			'benefits_heading'  => array( 'benefits_heading', 'revenue_title', 'revenue_copy', 'loyalty_title', 'loyalty_copy', 'futureproof_title', 'futureproof_copy', 'sustainability_title', 'sustainability_copy' ),
			'chargers_heading'  => array( 'chargers_heading', 'chargers_intro', 'ac_title', 'ac_copy', 'dc_title', 'dc_copy', 'chargers_outro', 'chargers' ),
			'cta_heading'       => array( 'cta_heading', 'cta_copy', 'cta_label', 'cta_url' ),
			'process_title'     => array_keys( Greendawn_Process::defaults() ),
			'final_cta_heading' => array( 'final_cta_heading', 'final_cta_copy', 'final_cta_label', 'final_cta_url' ),
			'posts_heading'     => array( 'posts_heading', 'posts' ),
		);
		$fields = array();
		$schema = $this->fields();
		foreach ( $groups as $tab => $names ) {
			$fields[] = array(
				'key'   => 'field_greendawn_public_tab_' . $tab,
				'label' => $tabs[ $tab ],
				'type'  => 'tab',
			);
			foreach ( $names as $name ) {
				$field    = $schema[ $name ];
				$fields[] = $field;
				add_filter( 'acf/load_value/key=' . $field['key'], array( $this, 'load_default' ), 10, 3 );
				if ( in_array( $field['type'], array( 'relationship', 'post_object' ), true ) ) {
					add_filter( 'acf/fields/' . $field['type'] . '/query/key=' . $field['key'], array( $this, 'published_choices' ) );
				}
			}
		}
		acf_add_local_field_group(
			array(
				'key'      => 'group_greendawn_public',
				'title'    => __( 'Greendawn Public Charging', 'greendawn' ),
				'location' => array(
					array(
						array(
							'param'    => 'page_template',
							'operator' => '==',
							'value'    => self::TEMPLATE,
						),
					),
				),
				'fields'   => $fields,
			)
		);
	}
}
