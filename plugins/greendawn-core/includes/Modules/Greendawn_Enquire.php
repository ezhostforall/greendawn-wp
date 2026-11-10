<?php
/**
 * Page-owned Enquire content and its explicitly selected native sales form.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/** Keeps the fixed sales journey separate from support forms and the callback drawer. */
final class Greendawn_Enquire implements Module_Interface {

	private const TEMPLATE = 'templates/greendawn-enquire.php';

	/** Registers content, editing scope and selected-form product context. */
	public function register(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'greendawn_enquire_field_value', array( $this, 'field_value' ), 10, 3 );
		add_filter( 'use_block_editor_for_post', array( $this, 'use_block_editor' ), 10, 2 );
		add_action( 'add_meta_boxes_page', array( $this, 'hide_body_editor' ) );
		add_action( 'admin_notices', array( $this, 'form_notice' ) );
		add_filter( 'wpcf7_form_tag', array( $this, 'charger_tag' ), 20 );
		add_filter( 'wpcf7_posted_data', array( $this, 'charger_data' ), 20 );
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
	 * Retains the approved source's own six-step sales process in full.
	 *
	 * @return array<string,array>
	 */
	public function fields(): array {
		$copy  = array(
			'heading'         => array( __( 'Hero heading', 'greendawn' ), 'Let’s start talking' ),
			'accent'          => array( __( 'Hero accent', 'greendawn' ), 'about your project.' ),
			'intro'           => array( __( 'Optional hero introduction', 'greendawn' ), '', 'textarea' ),
			'form_heading'    => array( __( 'Optional form heading', 'greendawn' ), '' ),
			'contact_heading' => array( __( 'Contact heading', 'greendawn' ), 'Have a Question?' ),
			'contact_copy'    => array( __( 'Contact introduction', 'greendawn' ), 'A member of our friendly and knowledgable team will be happy to help with any enquiries you have, EV related or otherwise!', 'textarea' ),
			'address_label'   => array( __( 'Address label', 'greendawn' ), 'Address' ),
			'phone_label'     => array( __( 'Telephone label', 'greendawn' ), 'Call Us' ),
			'email_label'     => array( __( 'Email label', 'greendawn' ), 'Email' ),
		);
		$steps = array(
			'consultation'   => array( 'Consultation', 'We start with a free consultation to understand your goals and recommend suitable chargers, grants, and aftercare options.' ),
			'hardware_quote' => array( 'Hardware Quote', 'An upfront quote covering hardware costs, giving full visibility before moving forward.' ),
			'site_survey'    => array( 'Site Survey', 'A Greendawn electrical engineer will assess your site to review power, cable routes, and access, to ensure your project can be scoped and priced accurately.' ),
			'full_quote'     => array( 'Full Quote', 'A final proposal of the installation, as well as any safeguarding requirements of your current electrical supply, will be provided. The quote will include electrical materials, labour, hardware, and any necessary groundwork and commissioning. Everything required for a complete installation quote.' ),
			'acceptance'     => array( 'Acceptance & Installation', 'Once approved, we schedule installation and complete all works safely and to specification.' ),
			'aftercare'      => array( 'Aftercare', 'Ongoing support including maintenance, updates, and troubleshooting to keep your system running smoothly.' ),
		);
		foreach ( $steps as $name => $step ) {
			$copy[ $name . '_title' ] = array( $step[0] . ' — ' . __( 'title', 'greendawn' ), $step[0] );
			$copy[ $name . '_copy' ]  = array( $step[0] . ' — ' . __( 'complete description', 'greendawn' ), $step[1], 'textarea' );
		}
		$fields = array();
		foreach ( $copy as $name => $settings ) {
			$fields[ $name ] = array(
				'key'           => 'field_greendawn_enquire_' . $name,
				'name'          => 'greendawn_enquire_' . $name,
				'label'         => $settings[0],
				'type'          => $settings[2] ?? 'text',
				'default_value' => $settings[1],
			);
		}
		$fields['heading']['required'] = 1;
		$fields['form']                = array(
			'key'           => 'field_greendawn_enquire_form',
			'name'          => 'greendawn_enquire_form',
			'label'         => __( 'Project enquiry — Contact Form 7 form', 'greendawn' ),
			'type'          => 'post_object',
			'post_type'     => array( 'wpcf7_contact_form' ),
			'post_status'   => array( 'publish' ),
			'allow_null'    => 1,
			'return_format' => 'id',
			'instructions'  => __( 'Select the detailed sales enquiry form. Configure its fields, recipient and messages in Contact Form 7. No form is copied, created or inherited from the Customiser or callback drawer. Shared phone, email and address remain in Appearance → Customise → Footer.', 'greendawn' ),
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
		return metadata_exists( 'post', absint( $post_id ), $field['name'] ) ? $value : ( $field['default_value'] ?? '' );
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
			'form'    => array( __( 'Project enquiry', 'greendawn' ), array( 'form_heading', 'form' ) ),
			'process' => array( __( 'Six-step process', 'greendawn' ), array( 'consultation_title', 'consultation_copy', 'hardware_quote_title', 'hardware_quote_copy', 'site_survey_title', 'site_survey_copy', 'full_quote_title', 'full_quote_copy', 'acceptance_title', 'acceptance_copy', 'aftercare_title', 'aftercare_copy' ) ),
			'contact' => array( __( 'Contact panel', 'greendawn' ), array( 'contact_heading', 'contact_copy', 'address_label', 'phone_label', 'email_label' ) ),
		);
		$fields = array();
		foreach ( $groups as $tab => $group ) {
			$fields[] = array(
				'key'   => 'field_greendawn_enquire_tab_' . $tab,
				'label' => $group[0],
				'type'  => 'tab',
			);
			foreach ( $group[1] as $name ) {
				$field    = $schema[ $name ];
				$fields[] = $field;
				add_filter( 'acf/load_value/key=' . $field['key'], array( $this, 'load_default' ), 10, 3 );
			}
		}
		add_filter( 'acf/fields/post_object/query/key=field_greendawn_enquire_form', array( $this, 'published_choices' ) );
		acf_add_local_field_group(
			array(
				'key'      => 'group_greendawn_enquire',
				'title'    => __( 'Greendawn Enquire', 'greendawn' ),
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
		$id = absint( get_post_meta( $post->ID, 'greendawn_enquire_form', true ) );
		if ( shortcode_exists( 'contact-form-7' ) && 'wpcf7_contact_form' === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Enquire needs a published Contact Form 7 sales form selected in its Project enquiry fields. Review the form’s recipient and messages in Contact Form 7 before testing delivery.', 'greendawn' ) . '</p></div>';
	}

	/**
	 * Restricts product transport to the form explicitly selected on a public Enquire Page.
	 *
	 * @param int $post_id Candidate Page record.
	 */
	private function owns_form( int $post_id ): bool {
		$form = function_exists( 'wpcf7_get_current_contact_form' ) ? wpcf7_get_current_contact_form() : null;
		$post = get_post( $post_id );
		return $form && $post && 'page' === $post->post_type && 'publish' === $post->post_status && '' === $post->post_password && self::TEMPLATE === get_page_template_slug( $post ) && $form->id() === absint( get_post_meta( $post_id, 'greendawn_enquire_form', true ) );
	}

	/**
	 * Fills explicit hidden product tags on the server, including without JavaScript.
	 *
	 * @param array $tag Raw native tag before CF7 constructs its FormTag object.
	 * @return array
	 */
	public function charger_tag( array $tag ): array {
		$names = array(
			'chargerId'    => 'id',
			'chargerTitle' => 'title',
			'chargerURL'   => 'url',
		);
		if ( 'hidden' !== $tag['basetype'] || ! isset( $names[ $tag['name'] ] ) || ! $this->owns_form( get_the_ID() ) ) {
			return $tag;
		}
		$context       = apply_filters( 'greendawn_selected_charger', array() );
		$tag['values'] = array( (string) ( $context[ $names[ $tag['name'] ] ] ?? '' ) );
		return $tag;
	}

	/**
	 * Resolves posted product titles/URLs from public records, never from client claims.
	 *
	 * @param array $data CF7 posted fields.
	 */
	public function charger_data( array $data ): array {
		$form = function_exists( 'wpcf7_get_current_contact_form' ) ? wpcf7_get_current_contact_form() : null;
		if ( ! $form || ( ! array_key_exists( 'chargerId', $data ) && ! array_key_exists( 'chargerTitle', $data ) && ! array_key_exists( 'chargerURL', $data ) ) ) {
			return $data;
		}
		// Resolve configured ownership independently of client-supplied container fields.
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'has_password'   => false,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Bounded, submission-only lookup of an explicitly configured public sales form.
					array(
						'key'   => '_wp_page_template',
						'value' => self::TEMPLATE,
					),
					array(
						'key'   => 'greendawn_enquire_form',
						'value' => $form->id(),
					),
				),
			)
		);
		if ( ! $pages ) {
			return $data;
		}
		$context              = EV_Chargers::quote_context( $data['chargerId'] ?? '' );
		$data['chargerId']    = isset( $context['id'] ) ? (string) $context['id'] : '';
		$data['chargerTitle'] = $context['title'] ?? '';
		$data['chargerURL']   = $context['url'] ?? '';
		return $data;
	}
}
