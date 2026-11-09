<?php
/**
 * Content-only fields and defaults for the fixed Greendawn About template.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps page content in Core and page composition in the theme.
 */
final class Greendawn_About implements Module_Interface {

	private const TEMPLATE = 'templates/greendawn-about.php';

	/** Registers the structured editing interface and server-side data adapter. */
	public function register(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'greendawn_about_field_value', array( $this, 'field_value' ), 10, 3 );
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
		$copy  = array(
			'heading'       => array( __( 'Hero heading', 'greendawn' ), 'EV Charging' ),
			'accent'        => array( __( 'Hero accent', 'greendawn' ), 'Done Properly' ),
			'intro'         => array( __( 'Hero introduction', 'greendawn' ), 'We design, deliver, and support EV charging infrastructure that works in the real world, not just on paper.', 'textarea' ),
			'story_heading' => array( __( 'Story heading', 'greendawn' ), 'Who We Are' ),
			'story'         => array( __( 'Story', 'greendawn' ), "We’re an EV infrastructure partner for businesses, not just installers.\n\nGreendawn was built around a simple idea: EV charging shouldn’t be complicated, unreliable, or poorly planned. Too often, businesses are sold hardware without proper consideration of power, usage, or long-term performance.\n\nWe take a different approach.\n\nWe work with workplaces, fleets, and commercial sites across the UK to deliver charging infrastructure that is practical, scalable, and built to last.", 'textarea' ),
			'process_title' => array( __( 'Process heading', 'greendawn' ), 'The Greendawn Method' ),
			'cta_heading'   => array( __( 'CTA heading', 'greendawn' ), 'Not sure where to start?' ),
			'cta_copy'      => array( __( 'CTA introduction', 'greendawn' ), 'Every site is different. Power, layout, and usage all play a part, and getting it right early makes a big difference.', 'textarea' ),
			'cta_label'     => array( __( 'CTA button label', 'greendawn' ), 'Start Your Journey' ),
			'cta_url'       => array( __( 'CTA destination', 'greendawn' ), home_url( '/enquire/' ), 'url' ),
			'team_heading'  => array( __( 'Team heading', 'greendawn' ), 'Our Team' ),
			'form_heading'  => array( __( 'Form heading', 'greendawn' ), 'Get In Touch' ),
			'posts_heading' => array( __( 'Articles heading', 'greendawn' ), 'Our Content' ),
		);
		$steps = array(
			'consult' => array( 'Consult', 'We perform a site survey to evaluate power, cable routes, and access for precise scoping and cost management.' ),
			'design'  => array( 'Design', 'Once your survey is done, we’ll give you a project proposal with costs, hardware, timeline, and grant guidance.' ),
			'install' => array( 'Install', 'Once accepted, our engineers install ensuring compliance, safety, and adherence to specifications.' ),
			'manage'  => array( 'Manage', 'We offer ongoing support with maintenance to keep your charging infrastructure reliable.' ),
		);
		foreach ( $steps as $key => $step ) {
			$copy[ $key . '_title' ] = array( $step[0] . ' — ' . __( 'title', 'greendawn' ), $step[0] );
			$copy[ $key . '_copy' ]  = array( $step[0] . ' — ' . __( 'description', 'greendawn' ), $step[1], 'textarea' );
		}
		$fields = array();
		foreach ( $copy as $name => $settings ) {
			$fields[ $name ] = array(
				'key'           => 'field_greendawn_about_' . $name,
				'name'          => 'greendawn_about_' . $name,
				'label'         => $settings[0],
				'type'          => $settings[2] ?? 'text',
				'default_value' => $settings[1],
			);
		}
		foreach ( array(
			'hero_image'  => '2026/11/greendawn-engineers-electrical-distribution-1600.webp',
			'story_image' => '2025/05/Blogs-1.avif',
		) as $name => $upload ) {
			$fields[ $name ] = array(
				'key'            => 'field_greendawn_about_' . $name,
				'name'           => 'greendawn_about_' . $name,
				'label'          => 'hero_image' === $name ? __( 'Hero photograph', 'greendawn' ) : __( 'Story photograph', 'greendawn' ),
				'type'           => 'image',
				'return_format'  => 'id',
				'default_upload' => $upload,
				'instructions'   => __( 'Select an image from this site’s Media Library. Unset fields use the matching uploaded reference image when available. Clearing a saved selection removes the image.', 'greendawn' ),
			);
		}
		foreach ( array(
			'team_members' => array( __( 'Team members — drag to reorder', 'greendawn' ), 'team_member' ),
			'posts'        => array( __( 'Related articles — drag to reorder', 'greendawn' ), 'post' ),
		) as $name => $settings ) {
			$fields[ $name ] = array(
				'key'           => 'field_greendawn_about_' . $name,
				'name'          => 'greendawn_about_' . $name,
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
			'key'           => 'field_greendawn_about_form',
			'name'          => 'greendawn_about_form',
			'label'         => __( 'Contact Form 7 form', 'greendawn' ),
			'type'          => 'post_object',
			'post_type'     => array( 'wpcf7_contact_form' ),
			'post_status'   => array( 'publish' ),
			'allow_null'    => 1,
			'return_format' => 'id',
			'instructions'  => __( 'Select this page’s form. Configure its fields, email recipient and messages in Contact Form 7. This selection does not use the Contact template’s Customiser setting.', 'greendawn' ),
		);
		foreach ( $fields as $name => &$field ) {
			if ( 'heading' === $name || str_ends_with( $name, '_heading' ) || str_ends_with( $name, '_title' ) ) {
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
				return get_posts( $query );
			}
			if ( 'image' === $field['type'] ) {
				if ( 'greendawn_about_hero_image' === $field['name'] && has_post_thumbnail( $post_id ) ) {
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
			'heading'       => __( 'Hero', 'greendawn' ),
			'story_heading' => __( 'Story', 'greendawn' ),
			'process_title' => __( 'Process', 'greendawn' ),
			'cta_heading'   => __( 'Call to action', 'greendawn' ),
			'team_heading'  => __( 'Team', 'greendawn' ),
			'form_heading'  => __( 'Form', 'greendawn' ),
			'posts_heading' => __( 'Articles', 'greendawn' ),
		);
		$groups = array(
			'heading'       => array( 'heading', 'accent', 'intro', 'hero_image' ),
			'story_heading' => array( 'story_heading', 'story', 'story_image' ),
			'process_title' => array( 'process_title', 'consult_title', 'consult_copy', 'design_title', 'design_copy', 'install_title', 'install_copy', 'manage_title', 'manage_copy' ),
			'cta_heading'   => array( 'cta_heading', 'cta_copy', 'cta_label', 'cta_url' ),
			'team_heading'  => array( 'team_heading', 'team_members' ),
			'form_heading'  => array( 'form_heading', 'form' ),
			'posts_heading' => array( 'posts_heading', 'posts' ),
		);
		$fields = array();
		$schema = $this->fields();
		foreach ( $groups as $tab => $names ) {
			$fields[] = array(
				'key'   => 'field_greendawn_about_tab_' . $tab,
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
				'key'      => 'group_greendawn_about',
				'title'    => __( 'Greendawn About Us', 'greendawn' ),
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
