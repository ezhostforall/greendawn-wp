<?php
/**
 * Content-only Business Installations landing page and testimonial media.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/** Owns business landing content without offering layout controls. */
final class Greendawn_Business implements Module_Interface {

	private const TEMPLATE = 'templates/greendawn-business-installations.php';

	/** Registers the content model and trusted video adapter. */
	public function register(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'greendawn_business_field_value', array( $this, 'field_value' ), 10, 3 );
		add_filter( 'use_block_editor_for_post', array( $this, 'use_block_editor' ), 10, 2 );
		add_action( 'add_meta_boxes_page', array( $this, 'hide_body_editor' ) );
		add_action( 'admin_notices', array( $this, 'form_notice' ) );
		add_filter( 'greendawn_page_video', array( $this, 'video_value' ), 10, 2 );
	}

	/**
	 * Disables Gutenberg only for this fixed composition.
	 *
	 * @param bool     $use_editor Existing decision.
	 * @param \WP_Post $post Page record.
	 */
	public function use_block_editor( bool $use_editor, \WP_Post $post ): bool {
		return $use_editor && self::TEMPLATE !== get_page_template_slug( $post );
	}

	/**
	 * Hides layout editing while retaining imported content in storage.
	 *
	 * @param \WP_Post $post Page record.
	 */
	public function hide_body_editor( \WP_Post $post ): void {
		if ( self::TEMPLATE === get_page_template_slug( $post ) ) {
			remove_post_type_support( 'page', 'editor' );
		}
	}

	/**
	 * Retains all source service summaries and three complete testimonials.
	 *
	 * @return array<string,array>
	 */
	public function fields(): array {
		$copy   = array(
			'heading'                   => array( __( 'Hero heading', 'greendawn' ), 'EV Charger Installation for Businesses' ),
			'accent'                    => array( __( 'Hero accent', 'greendawn' ), 'That Think Ahead' ),
			'intro'                     => array( __( 'Hero introduction', 'greendawn' ), 'We work with fleets, offices, retail parks, and commercial landlords to survey, plan, and install EV infrastructure.', 'textarea' ),
			'help_heading'              => array( __( 'Services heading', 'greendawn' ), 'How We Can Help You:' ),
			'help_intro'                => array( __( 'Services introduction', 'greendawn' ), 'Whether you’re looking to power a single site or roll out EV charging across your portfolio, we make the process simple, strategic, and fully compliant', 'textarea' ),
			'form_heading'              => array( __( 'Form heading', 'greendawn' ), 'Speak To Our Team- For Expert Advice' ),
			'form_intro'                => array( __( 'Optional form introduction', 'greendawn' ), '', 'textarea' ),
			'testimonials_heading'      => array( __( 'Testimonials heading', 'greendawn' ), 'Who we work with…' ),
			'testimonials_intro'        => array( __( 'Testimonials introduction', 'greendawn' ), 'Typically, our clients have large installations on single sites, or have multiple sites with changing requirements.  For example, we work extensively in the hotel and leisure space, and have major relationships with some of the UKs largest car dealership networks.', 'textarea' ),
			'service_1_title'           => array( __( 'Service 1 — title', 'greendawn' ), 'Site Assessment & Consultation' ),
			'service_1_copy'            => array( __( 'Service 1 — complete description', 'greendawn' ), 'We’ll assess your site’s layout, power capacity, and goals to recommend the right charging solution for your business — no obligation.', 'textarea' ),
			'service_2_title'           => array( __( 'Service 2 — title', 'greendawn' ), 'Professional Installation' ),
			'service_2_copy'            => array( __( 'Service 2 — complete description', 'greendawn' ), 'Our certified installers handle everything — groundworks, hardware, software setup, and final testing — all with minimal disruption to your business.', 'textarea' ),
			'service_3_title'           => array( __( 'Service 3 — title', 'greendawn' ), 'Ongoing Support & Maintenance' ),
			'service_3_copy'            => array( __( 'Service 3 — complete description', 'greendawn' ), 'We offer optional maintenance packages, remote diagnostics, and on-site callouts to keep your chargers working smoothly.', 'textarea' ),
			'testimonial_1_title'       => array( __( 'Testimonial 1 — organisation', 'greendawn' ), 'Johnsons Cars' ),
			'testimonial_1_quote'       => array( __( 'Testimonial 1 — complete quote', 'greendawn' ), '"Working with Greendawn has taken the headache out of our EV charging rollout, they have demonstrated a deep understanding of my business in a sector that is complex and always changing. I know I can rely on Greendawn to provide me with the best solution in the most cost-effective way"', 'textarea' ),
			'testimonial_1_attribution' => array( __( 'Testimonial 1 — optional attribution', 'greendawn' ), '' ),
			'testimonial_1_video'       => array( __( 'Testimonial 1 — YouTube URL', 'greendawn' ), 'https://youtu.be/t96HrzEREM0', 'url' ),
			'testimonial_2_title'       => array( __( 'Testimonial 2 — organisation', 'greendawn' ), 'Elite Hotels' ),
			'testimonial_2_quote'       => array( __( 'Testimonial 2 — complete quote', 'greendawn' ), '"Greendawn have successfully installed EV charging stations at all our hotels in the south of England. They provide excellent service & support and have proved to be a great partner."', 'textarea' ),
			'testimonial_2_attribution' => array( __( 'Testimonial 2 — optional attribution', 'greendawn' ), '' ),
			'testimonial_2_video'       => array( __( 'Testimonial 2 — YouTube URL', 'greendawn' ), '', 'url' ),
			'testimonial_3_title'       => array( __( 'Testimonial 3 — organisation', 'greendawn' ), 'Salvation Army' ),
			'testimonial_3_quote'       => array( __( 'Testimonial 3 — complete quote', 'greendawn' ), '"Greendawn stood out with their sustainability and values aligning with ours, as well as the flexable tariff setting and nationwide coverage. It has been a smooth process from start to finnish with Greendawn; we look forward to working with them on many more projects."', 'textarea' ),
			'testimonial_3_attribution' => array( __( 'Testimonial 3 — optional attribution', 'greendawn' ), '' ),
			'testimonial_3_video'       => array( __( 'Testimonial 3 — YouTube URL', 'greendawn' ), '', 'url' ),
		);
		$fields = array();
		foreach ( $copy as $name => $settings ) {
			$fields[ $name ] = array(
				'key'           => 'field_greendawn_business_' . $name,
				'name'          => 'greendawn_business_' . $name,
				'label'         => $settings[0],
				'type'          => $settings[2] ?? 'text',
				'default_value' => $settings[1],
			);
		}
		foreach ( array(
			'testimonial_1_image' => '2026/11/johnsons-dealership-ev-charging-1200.webp',
			'testimonial_2_image' => '2024/11/elite-hotels.png',
			'testimonial_3_image' => '2024/11/SA.png',
		) as $name => $upload ) {
			$fields[ $name ] = array(
				'key'            => 'field_greendawn_business_' . $name,
				'name'           => 'greendawn_business_' . $name,
				'label'          => __( 'Local testimonial photograph or logo', 'greendawn' ),
				'type'           => 'image',
				'return_format'  => 'id',
				'default_upload' => $upload,
				'instructions'   => __( 'Select a local Media Library attachment. Unset fields resolve the matching imported source upload; clearing a saved image removes it. Video artwork is never downloaded from YouTube.', 'greendawn' ),
			);
		}
		$fields['heading']['required'] = 1;
		$fields['form']                = array(
			'key'           => 'field_greendawn_business_form',
			'name'          => 'greendawn_business_form',
			'label'         => __( 'Project enquiry — Contact Form 7 form', 'greendawn' ),
			'type'          => 'post_object',
			'post_type'     => array( 'wpcf7_contact_form' ),
			'post_status'   => array( 'publish' ),
			'allow_null'    => 1,
			'return_format' => 'id',
			'instructions'  => __( 'Select the detailed sales enquiry form. Configure its fields, recipient and messages in Contact Form 7. You may select the same reviewed detailed form as Enquire. No form is created, copied or inherited from another Page, the Customiser or the callback drawer.', 'greendawn' ),
		);
		return $fields;
	}

	/**
	 * Supplies raw metadata/defaults with or without SCF, preserving saved blanks.
	 *
	 * @param mixed  $fallback Existing value.
	 * @param string $name Short field name.
	 * @param int    $post_id Page record.
	 * @return mixed
	 */
	public function field_value( $fallback, string $name, int $post_id ) {
		$field = $this->fields()[ $name ] ?? null;
		return $field ? $this->load_default( get_post_meta( $post_id, $field['name'], true ), $post_id, $field ) : $fallback;
	}

	/**
	 * Provides unsaved initial content without overwriting saved changes/clears.
	 *
	 * @param mixed $value Existing value.
	 * @param mixed $post_id SCF identifier.
	 * @param array $field Field schema.
	 * @return mixed
	 */
	public function load_default( $value, $post_id, array $field ) {
		$post_id = absint( $post_id );
		if ( metadata_exists( 'post', $post_id, $field['name'] ) ) {
			return $value;
		}
		if ( 'image' === $field['type'] ) {
			$url = trailingslashit( wp_get_upload_dir()['baseurl'] ) . $field['default_upload'];
			$id  = attachment_url_to_postid( $url );
			return $id ? $id : attachment_url_to_postid( preg_replace( '/(\.[^\/.]+)$/', '-scaled$1', $url ) );
		}
		return $field['default_value'] ?? '';
	}

	/**
	 * Lists published forms only.
	 *
	 * @param array $query Existing SCF query.
	 */
	public function published_choices( array $query ): array {
		$query['post_status']  = 'publish';
		$query['has_password'] = false;
		return $query;
	}

	/** Registers content tabs scoped to the fixed template. */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		$schema = $this->fields();
		$groups = array(
			'hero'         => array( __( 'Hero', 'greendawn' ), array( 'heading', 'accent', 'intro' ) ),
			'services'     => array( __( 'How we help', 'greendawn' ), array( 'help_heading', 'help_intro', 'service_1_title', 'service_1_copy', 'service_2_title', 'service_2_copy', 'service_3_title', 'service_3_copy' ) ),
			'form'         => array( __( 'Sales enquiry', 'greendawn' ), array( 'form_heading', 'form_intro', 'form' ) ),
			'testimonials' => array( __( 'Testimonials', 'greendawn' ), array( 'testimonials_heading', 'testimonials_intro', 'testimonial_1_title', 'testimonial_1_quote', 'testimonial_1_attribution', 'testimonial_1_image', 'testimonial_1_video', 'testimonial_2_title', 'testimonial_2_quote', 'testimonial_2_attribution', 'testimonial_2_image', 'testimonial_2_video', 'testimonial_3_title', 'testimonial_3_quote', 'testimonial_3_attribution', 'testimonial_3_image', 'testimonial_3_video' ) ),
		);
		$fields = array();
		foreach ( $groups as $tab => $group ) {
			$fields[] = array(
				'key'   => 'field_greendawn_business_tab_' . $tab,
				'label' => $group[0],
				'type'  => 'tab',
			);
			foreach ( $group[1] as $name ) {
				$field    = $schema[ $name ];
				$fields[] = $field;
				add_filter( 'acf/load_value/key=' . $field['key'], array( $this, 'load_default' ), 10, 3 );
				if ( 'url' === $field['type'] ) {
					add_filter( 'acf/validate_value/key=' . $field['key'], array( $this, 'validate_video' ), 10, 2 );
				}
			}
		}
		add_filter( 'acf/fields/post_object/query/key=field_greendawn_business_form', array( $this, 'published_choices' ) );
		acf_add_local_field_group(
			array(
				'key'      => 'group_greendawn_business',
				'title'    => __( 'Greendawn Business EV Charging Installations', 'greendawn' ),
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

	/** Warns administrators about an unconfigured/unavailable form on this edit screen. */
	public function form_notice(): void {
		global $post;
		$screen = get_current_screen();
		if ( ! current_user_can( 'manage_options' ) || ! $screen || 'page' !== $screen->id || ! $post instanceof \WP_Post || self::TEMPLATE !== get_page_template_slug( $post ) ) {
			return;
		}
		$id = absint( get_post_meta( $post->ID, 'greendawn_business_form', true ) );
		if ( shortcode_exists( 'contact-form-7' ) && 'wpcf7_contact_form' === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Business EV Charging Installations needs a published Contact Form 7 sales form selected in its Sales enquiry fields. Review the form’s recipient and messages in Contact Form 7 before testing delivery.', 'greendawn' ) . '</p></div>';
	}

	/**
	 * Converts a supported HTTPS YouTube URL into trusted playback/fallback destinations.
	 *
	 * @param array  $fallback Existing video data.
	 * @param string $url Editor-owned URL.
	 * @return array<string,string>
	 */
	public function video_value( array $fallback, string $url ): array {
		$parts = wp_parse_url( trim( $url ) );
		if ( ! is_array( $parts ) || 'https' !== strtolower( $parts['scheme'] ?? '' ) || isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['port'] ) ) {
			return array();
		}
		$host = strtolower( $parts['host'] ?? '' );
		$path = $parts['path'] ?? '';
		$id   = '';
		if ( 'youtu.be' === $host ) {
			$id = trim( $path, '/' );
		} elseif ( in_array( $host, array( 'youtube.com', 'www.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com' ), true ) ) {
			if ( preg_match( '~^/(?:embed|shorts)/([A-Za-z0-9_-]{11})/?$~D', $path, $matches ) ) {
				$id = $matches[1];
			} elseif ( '/watch' === $path && isset( $parts['query'] ) ) {
				wp_parse_str( $parts['query'], $query );
				$id = is_string( $query['v'] ?? null ) ? $query['v'] : '';
			}
		}
		if ( ! preg_match( '/^[A-Za-z0-9_-]{11}$/D', $id ) ) {
			return array();
		}
		return array(
			'url'   => 'https://youtu.be/' . $id,
			'embed' => 'https://www.youtube-nocookie.com/embed/' . $id,
		);
	}

	/**
	 * Rejects unsupported embed sources in the structured editor.
	 *
	 * @param mixed $valid Existing validation result.
	 * @param mixed $value Entered URL.
	 * @return mixed
	 */
	public function validate_video( $valid, $value ) {
		if ( true !== $valid || '' === $value ) {
			return $valid;
		}
		return is_string( $value ) && $this->video_value( array(), $value ) ? true : __( 'Enter an HTTPS YouTube video URL, or leave this field empty.', 'greendawn' );
	}
}
