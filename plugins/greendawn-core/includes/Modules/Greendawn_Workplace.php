<?php
/**
 * Content-only fields and defaults for the fixed Greendawn Workplace template.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps page content in Core and page composition in the theme.
 */
final class Greendawn_Workplace implements Module_Interface {

	private const TEMPLATE = 'templates/greendawn-workplace.php';

	/** Registers the structured editing interface and server-side data adapter. */
	public function register(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'greendawn_workplace_field_value', array( $this, 'field_value' ), 10, 3 );
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
			'heading'          => array( __( 'Hero heading', 'greendawn' ), 'EV Charging For' ),
			'accent'           => array( __( 'Hero accent', 'greendawn' ), 'Your Workplace' ),
			'intro'            => array( __( 'Hero introduction', 'greendawn' ), 'Take Control of Workplace Charging: Smarter, Accountable, and Always Available for Your Staff', 'textarea' ),
			'benefits_heading' => array( __( 'Benefits heading', 'greendawn' ), 'Why Install Workplace EV Charging?' ),
			'chargers_heading' => array( __( 'Chargers heading', 'greendawn' ), 'Recommended Workplace EV Chargers' ),
			'chargers_intro'   => array( __( 'Chargers introduction', 'greendawn' ), 'Workplace charging is all about dwell time, most vehicles are parked for several hours during the working day. A 7–22kW charger provides the perfect balance: fast enough to fully charge during a typical shift, but cost-effective to install and operate compared to rapid chargers. This makes them ideal for both staff and fleet vehicles.', 'textarea' ),
			'cta_heading'      => array( __( 'CTA heading', 'greendawn' ), 'Not sure which setup is right for your workplace?' ),
			'cta_copy'         => array( __( 'CTA introduction', 'greendawn' ), "Every site is different. Power availability, parking layout, and how your team will use the chargers all play a part.\nWe’ll help you work out what makes sense before anything is installed.", 'textarea' ),
			'cta_label'        => array( __( 'CTA button label', 'greendawn' ), 'Start Your Journey' ),
			'cta_url'          => array( __( 'CTA destination', 'greendawn' ), home_url( '/enquire/' ), 'url' ),
			'process_title'    => array( __( 'Process heading', 'greendawn' ), 'Our Process' ),
			'form_heading'     => array( __( 'Form heading (optional)', 'greendawn' ), '' ),
			'posts_heading'    => array( __( 'Articles heading', 'greendawn' ), 'Our Content' ),
		);
		$benefits = array(
			'cost'           => array( 'Cost Savings', 'Charging on-site is up to 4–5x cheaper than relying on public chargers, giving you predictable, controlled costs.' ),
			'reliability'    => array( 'Convenience & Reliability', 'Staff have consistent access to charging without delays, queues, or fluctuating rates.' ),
			'energy'         => array( 'Energy Management', 'Set charging policies, balance usage between staff and fleet vehicles, and optimise your site’s power capacity.' ),
			'sustainability' => array( 'Sustainability', 'Reduces your business’s carbon footprint and demonstrates commitment to green initiatives.' ),
		);
		foreach ( $benefits as $key => $benefit ) {
			$copy[ $key . '_title' ] = array( $benefit[0] . ' — ' . __( 'title', 'greendawn' ), $benefit[0] );
			$copy[ $key . '_copy' ]  = array( $benefit[0] . ' — ' . __( 'description', 'greendawn' ), $benefit[1], 'textarea' );
		}
		$steps = array(
			'consult' => array( 'Consult', 'Every project begins with a consultation and then an on-site survey. We assess your power supply, layout, and access requirements to ensure accurate scoping.' ),
			'design'  => array( 'Design', 'Based on the survey, we deliver a clear proposal including costs, charger recommendations, grant eligibility, and timeline. Once approved, we confirm project details and move to delivery.' ),
			'install' => array( 'Install', 'Our experienced engineers carry out the installation with minimal disruption, ensuring compliance, safety, and a high standard of workmanship throughout.' ),
			'manage'  => array( 'Manage', 'After installation, we continue to support you with maintenance, software updates, and fault response, ensuring your charging infrastructure remains reliable and easy to manage.' ),
		);
		foreach ( $steps as $key => $step ) {
			$copy[ $key . '_title' ] = array( $step[0] . ' — ' . __( 'title', 'greendawn' ), $step[0] );
			$copy[ $key . '_copy' ]  = array( $step[0] . ' — ' . __( 'description', 'greendawn' ), $step[1], 'textarea' );
		}
		$fields = array();
		foreach ( $copy as $name => $settings ) {
			$fields[ $name ] = array(
				'key'           => 'field_greendawn_workplace_' . $name,
				'name'          => 'greendawn_workplace_' . $name,
				'label'         => $settings[0],
				'type'          => $settings[2] ?? 'text',
				'default_value' => $settings[1],
			);
		}
		foreach ( array(
			'hero_image'      => '2026/11/commercial-ev-workplace-charging-1200.webp',
			'process_image_1' => '2025/05/Blogs-1.avif',
			'process_image_2' => '2024/11/Electricians-at-fuse-board.png',
		) as $name => $upload ) {
			$fields[ $name ] = array(
				'key'            => 'field_greendawn_workplace_' . $name,
				'name'           => 'greendawn_workplace_' . $name,
				'label'          => 'hero_image' === $name ? __( 'Hero photograph', 'greendawn' ) : __( 'Process photograph', 'greendawn' ) . ' ' . substr( $name, -1 ),
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
				'key'           => 'field_greendawn_workplace_' . $name,
				'name'          => 'greendawn_workplace_' . $name,
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
		$fields['form'] = array(
			'key'           => 'field_greendawn_workplace_form',
			'name'          => 'greendawn_workplace_form',
			'label'         => __( 'Contact Form 7 form', 'greendawn' ),
			'type'          => 'post_object',
			'post_type'     => array( 'wpcf7_contact_form' ),
			'post_status'   => array( 'publish' ),
			'allow_null'    => 1,
			'return_format' => 'id',
			'instructions'  => __( 'Select this page’s form. Configure its fields, email recipient and messages in Contact Form 7. This selection does not use the Contact template’s Customiser setting.', 'greendawn' ),
		);
		foreach ( $fields as $name => &$field ) {
			if ( 'form_heading' !== $name && ( 'heading' === $name || str_ends_with( $name, '_heading' ) || str_ends_with( $name, '_title' ) ) ) {
				$field['required'] = 1;
			}
		}
		unset( $field );
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
				if ( 'greendawn_workplace_hero_image' === $field['name'] && has_post_thumbnail( $post_id ) ) {
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
			'heading'          => __( 'Hero', 'greendawn' ),
			'benefits_heading' => __( 'Benefits', 'greendawn' ),
			'chargers_heading' => __( 'Chargers', 'greendawn' ),
			'cta_heading'      => __( 'Call to action', 'greendawn' ),
			'process_title'    => __( 'Process', 'greendawn' ),
			'form_heading'     => __( 'Form', 'greendawn' ),
			'posts_heading'    => __( 'Articles', 'greendawn' ),
		);
		$groups = array(
			'heading'          => array( 'heading', 'accent', 'intro', 'hero_image' ),
			'benefits_heading' => array( 'benefits_heading', 'cost_title', 'cost_copy', 'reliability_title', 'reliability_copy', 'energy_title', 'energy_copy', 'sustainability_title', 'sustainability_copy' ),
			'chargers_heading' => array( 'chargers_heading', 'chargers_intro', 'chargers' ),
			'cta_heading'      => array( 'cta_heading', 'cta_copy', 'cta_label', 'cta_url' ),
			'process_title'    => array( 'process_title', 'consult_title', 'consult_copy', 'design_title', 'design_copy', 'install_title', 'install_copy', 'manage_title', 'manage_copy', 'process_image_1', 'process_image_2' ),
			'form_heading'     => array( 'form_heading', 'form' ),
			'posts_heading'    => array( 'posts_heading', 'posts' ),
		);
		$fields = array();
		$schema = $this->fields();
		foreach ( $groups as $tab => $names ) {
			$fields[] = array(
				'key'   => 'field_greendawn_workplace_tab_' . $tab,
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
				'key'      => 'group_greendawn_workplace',
				'title'    => __( 'Greendawn Workplace Charging', 'greendawn' ),
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
