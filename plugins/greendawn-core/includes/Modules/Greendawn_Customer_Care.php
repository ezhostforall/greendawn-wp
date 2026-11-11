<?php
/**
 * Content-only Customer Care page with independent support routing.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/** Owns support content without offering layout controls. */
final class Greendawn_Customer_Care implements Module_Interface {

	private const TEMPLATE = 'templates/greendawn-customer-care.php';

	/** Registers the content model and validated WhatsApp adapter. */
	public function register(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'greendawn_customer_care_field_value', array( $this, 'field_value' ), 10, 3 );
		add_filter( 'use_block_editor_for_post', array( $this, 'use_block_editor' ), 10, 2 );
		add_action( 'add_meta_boxes_page', array( $this, 'hide_body_editor' ) );
		add_action( 'admin_notices', array( $this, 'form_notice' ) );
		add_filter( 'greendawn_customer_care_whatsapp_url', array( $this, 'whatsapp_url' ), 10, 2 );
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
	 * Retains the complete source support copy and WhatsApp route.
	 *
	 * @return array<string,array>
	 */
	public function fields(): array {
		$copy   = array(
			'heading'          => array( __( 'Hero heading', 'greendawn' ), 'Customer' ),
			'accent'           => array( __( 'Hero accent', 'greendawn' ), 'Support' ),
			'intro'            => array( __( 'Hero introduction', 'greendawn' ), 'At Greendawn, our commitment to you doesn’t stop once your chargers are installed. We’re here to make sure everything runs smoothly long after the first plug-in. We provide:', 'textarea' ),
			'point_1_title'    => array( __( 'Support point 1 — emphasis', 'greendawn' ), 'Fast response times' ),
			'point_1_copy'     => array( __( 'Support point 1 — complete continuation', 'greendawn' ), 'for troubleshooting and repairs', 'textarea' ),
			'point_2_title'    => array( __( 'Support point 2 — emphasis', 'greendawn' ), 'Remote diagnostics' ),
			'point_2_copy'     => array( __( 'Support point 2 — complete continuation', 'greendawn' ), 'to solve issues quickly', 'textarea' ),
			'point_3_title'    => array( __( 'Support point 3 — emphasis', 'greendawn' ), 'Clear communication' ),
			'point_3_copy'     => array( __( 'Support point 3 — complete continuation', 'greendawn' ), 'so you always know what’s happening', 'textarea' ),
			'point_4_title'    => array( __( 'Support point 4 — emphasis', 'greendawn' ), 'Ongoing care' ),
			'point_4_copy'     => array( __( 'Support point 4 — complete continuation', 'greendawn' ), 'to keep your charging infrastructure working at its best', 'textarea' ),
			'contact_heading'  => array( __( 'Contact section heading', 'greendawn' ), 'Get In Touch' ),
			'whatsapp_heading' => array( __( 'WhatsApp heading', 'greendawn' ), 'Contact Us On Whatsapp!' ),
			'whatsapp_label'   => array( __( 'WhatsApp link label', 'greendawn' ), 'Open Whatsapp' ),
			'whatsapp_url'     => array( __( 'WhatsApp link', 'greendawn' ), 'https://wa.me/443455120044', 'url' ),
			'form_heading'     => array( __( 'Support form heading', 'greendawn' ), 'Call Back Request Form' ),
		);
		$fields = array();
		foreach ( $copy as $name => $settings ) {
			$fields[ $name ] = array(
				'key'           => 'field_greendawn_customer_care_' . $name,
				'name'          => 'greendawn_customer_care_' . $name,
				'label'         => $settings[0],
				'type'          => $settings[2] ?? 'text',
				'default_value' => $settings[1],
			);
		}
		$fields['heading']['required'] = 1;
		$fields['form']                = array(
			'key'           => 'field_greendawn_customer_care_form',
			'name'          => 'greendawn_customer_care_form',
			'label'         => __( 'Dedicated support — Contact Form 7 form', 'greendawn' ),
			'type'          => 'post_object',
			'post_type'     => array( 'wpcf7_contact_form' ),
			'post_status'   => array( 'publish' ),
			'allow_null'    => 1,
			'return_format' => 'id',
			'instructions'  => __( 'Select the separate support form with Name, Email Address, Phone Number, Issue Type and Issue Summary. Configure its support recipient and messages in Contact Form 7. No form is created or inherited from sales pages, the Customiser or the callback drawer.', 'greendawn' ),
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
			'hero'    => array( __( 'Hero', 'greendawn' ), array( 'heading', 'accent', 'intro' ) ),
			'points'  => array( __( 'Support points', 'greendawn' ), array( 'point_1_title', 'point_1_copy', 'point_2_title', 'point_2_copy', 'point_3_title', 'point_3_copy', 'point_4_title', 'point_4_copy' ) ),
			'contact' => array( __( 'Support contact', 'greendawn' ), array( 'contact_heading', 'whatsapp_heading', 'whatsapp_label', 'whatsapp_url', 'form_heading', 'form' ) ),
		);
		$fields = array();
		foreach ( $groups as $tab => $group ) {
			$fields[] = array(
				'key'   => 'field_greendawn_customer_care_tab_' . $tab,
				'label' => $group[0],
				'type'  => 'tab',
			);
			foreach ( $group[1] as $name ) {
				$field    = $schema[ $name ];
				$fields[] = $field;
				add_filter( 'acf/load_value/key=' . $field['key'], array( $this, 'load_default' ), 10, 3 );
				if ( 'url' === $field['type'] ) {
					add_filter( 'acf/validate_value/key=' . $field['key'], array( $this, 'validate_whatsapp' ), 10, 2 );
				}
			}
		}
		add_filter( 'acf/fields/post_object/query/key=field_greendawn_customer_care_form', array( $this, 'published_choices' ) );
		acf_add_local_field_group(
			array(
				'key'      => 'group_greendawn_customer_care',
				'title'    => __( 'Greendawn Customer Care', 'greendawn' ),
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
		$id = absint( get_post_meta( $post->ID, 'greendawn_customer_care_form', true ) );
		if ( shortcode_exists( 'contact-form-7' ) && 'wpcf7_contact_form' === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Customer Care needs a published Contact Form 7 support form selected in its Support contact fields. Configure and review the support recipient separately from sales before testing delivery.', 'greendawn' ) . '</p></div>';
	}

	/**
	 * Validates the explicit WhatsApp destination without making a network request.
	 *
	 * @param string $fallback Existing destination.
	 * @param string $url Editor-owned link.
	 */
	public function whatsapp_url( string $fallback, string $url ): string {
		$parts = wp_parse_url( trim( $url ) );
		if ( ! is_array( $parts ) || 'https' !== strtolower( $parts['scheme'] ?? '' ) || 'wa.me' !== strtolower( $parts['host'] ?? '' ) || isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['port'] ) || ! preg_match( '~^/[1-9][0-9]{6,14}/?$~D', $parts['path'] ?? '' ) ) {
			return '';
		}
		return esc_url_raw( trim( $url ), array( 'https' ) );
	}

	/**
	 * Keeps the support action on WhatsApp; a saved blank removes it.
	 *
	 * @param mixed $valid Existing validation.
	 * @param mixed $value Entered URL.
	 * @return mixed
	 */
	public function validate_whatsapp( $valid, $value ) {
		if ( true !== $valid || '' === $value ) {
			return $valid;
		}
		return is_string( $value ) && $this->whatsapp_url( '', $value ) ? true : __( 'Enter an HTTPS wa.me link with an international phone number, or leave this field empty.', 'greendawn' );
	}
}
