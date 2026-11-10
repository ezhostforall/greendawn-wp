<?php
/**
 * Content-only fields and defaults for the fixed Greendawn Aftercare template.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps page content in Core and page composition in the theme.
 */
final class Greendawn_Aftercare implements Module_Interface {

	private const TEMPLATE = 'templates/greendawn-aftercare.php';

	/** Registers the structured editing interface and server-side data adapter. */
	public function register(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'greendawn_aftercare_field_value', array( $this, 'field_value' ), 10, 3 );
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
		$copy   = array(
			'heading'           => array( __( 'Hero heading', 'greendawn' ), 'EV Charging' ),
			'accent'            => array( __( 'Hero accent', 'greendawn' ), 'Aftercare' ),
			'intro'             => array( __( 'Hero introduction', 'greendawn' ), 'Take Control of your EV Charging: Smarter, Accountable, and Always Available', 'textarea' ),
			'story_heading'     => array( __( 'Introduction heading', 'greendawn' ), 'What It Is and Why It Matters' ),
			'story'             => array(
				__( 'Introduction', 'greendawn' ),
				'<p>EV charging aftercare is the ongoing support, monitoring, maintenance and compliance management that ensures your chargers continue to operate safely, reliably and in line with current regulations.</p><p>An EV charger is not simply a piece of hardware mounted to a wall. It is:</p><ul>
<li>
<p>A live electrical asset</p>
</li>
<li>
<p>A network-connected device</p>
</li>
<li>
<p>A payment terminal (in many cases)</p>
</li>
<li>
<p>A regulated piece of infrastructure</p>
</li>
</ul><p>Without structured aftercare, chargers can quickly become unreliable, non-compliant or financially ineffective.</p>',
				'wysiwyg',
			),
			'offerings_heading' => array( __( 'Offerings heading', 'greendawn' ), 'Our Aftercare Offering' ),
			'faq_heading'       => array( __( 'FAQs heading', 'greendawn' ), 'Frequently Asked Questions' ),
			'form_heading'      => array( __( 'Form heading', 'greendawn' ), 'Get In Touch' ),
			'posts_heading'     => array( __( 'Articles heading', 'greendawn' ), 'Our Content' ),
			'offering_1_title'  => array( __( 'Offering 1 — title', 'greendawn' ), 'Backoffice Software' ),
			'offering_1_copy'   => array(
				__( 'Offering 1 — complete content', 'greendawn' ),
				'<p>A back-office platform allows you to<strong> monitor charger usage, manage access, and handle payments</strong> where required. It also supports remote management and provides a secure system for data and compliance reporting.</p><p><strong>Without this, chargers operate with limited visibility and control.</strong></p><ul>
<li>Usage tracking and reporting</li>
<li>Access control for staff or public users</li>
<li>Payment handling and transaction records</li>
<li>Remote management and diagnostics</li>
</ul>',
				'wysiwyg',
			),
			'offering_2_title'  => array( __( 'Offering 2 — title', 'greendawn' ), 'Inspection + Testing' ),
			'offering_2_copy'   => array(
				__( 'Offering 2 — complete content', 'greendawn' ),
				'<p>Regular inspection and testing ensures chargers remain<strong> safe, compliant, and functioning as intended.</strong> All checks are carried out in line with relevant electrical standards.</p><p>Routine testing helps<strong> identify issues early and supports ongoing performance.</strong></p><ul>
<li>Electrical inspection and testing</li>
<li>Certification in line with regulations</li>
<li>Early identification of faults</li>
<li>Support for warranty compliance</li>
</ul>',
				'wysiwyg',
			),
			'offering_3_title'  => array( __( 'Offering 3 — title', 'greendawn' ), 'Complete Care Plan' ),
			'offering_3_copy'   => array(
				__( 'Offering 3 — complete content', 'greendawn' ),
				'<p>Ongoing support helps <strong>keep your chargers operational</strong> and reduces the impact of faults. This includes troubleshooting, system updates, and warranty-related assistance.</p><p>A structured support plan ensures issues are resolved efficiently, helping to <strong>minimise downtime.</strong></p><ul>
<li>Troubleshooting and technical support</li>
<li>System updates and maintenance</li>
<li>Warranty support and coordination</li>
<li>Prioritised response and reduced disruption</li>
</ul>',
				'wysiwyg',
			),
			'offering_4_title'  => array( __( 'Offering 4 — title', 'greendawn' ), 'Driver + User Support' ),
			'offering_4_copy'   => array(
				__( 'Offering 4 — complete content', 'greendawn' ),
				'<p>Driver support provides a clear <strong>point of contact for users</strong> experiencing <strong>charging or payment issues</strong>, typically via a helpline or app.</p><ul>
<li>Helpline or app-based support</li>
<li>Assistance with charging and payment issues</li>
<li>Guidance for first-time or public users</li>
<li>Support aligned with public charging requirements</li>
</ul><p><strong>Essential for public charging environments.  (Above 7kw)</strong></p>',
				'wysiwyg',
			),
		);
		$fields = array();
		foreach ( $copy as $name => $settings ) {
			$fields[ $name ] = array(
				'key'           => 'field_greendawn_aftercare_' . $name,
				'name'          => 'greendawn_aftercare_' . $name,
				'label'         => $settings[0],
				'type'          => $settings[2] ?? 'text',
				'default_value' => $settings[1],
			);
		}
		foreach ( array(
			'hero_image'  => '2026/11/commercial-ev-electrical-connection-1200.webp',
			'story_image' => '2026/04/20260220_112809-1.avif',
		) as $name => $upload ) {
			$fields[ $name ] = array(
				'key'            => 'field_greendawn_aftercare_' . $name,
				'name'           => 'greendawn_aftercare_' . $name,
				'label'          => 'hero_image' === $name ? __( 'Hero photograph', 'greendawn' ) : __( 'Story photograph', 'greendawn' ),
				'type'           => 'image',
				'return_format'  => 'id',
				'default_upload' => $upload,
				'instructions'   => __( 'Select an image from this site’s Media Library. Unset fields use the matching uploaded reference image when available. Clearing a saved selection removes the image.', 'greendawn' ),
			);
		}
		$fields['posts'] = array(
			'key'           => 'field_greendawn_aftercare_posts',
			'name'          => 'greendawn_aftercare_posts',
			'label'         => __( 'Related articles — drag to reorder', 'greendawn' ),
			'type'          => 'relationship',
			'post_type'     => array( 'post' ),
			'post_status'   => array( 'publish' ),
			'filters'       => array( 'search' ),
			'elements'      => array( 'featured_image' ),
			'return_format' => 'id',
			'instructions'  => __( 'Select published articles in display order. Clear the selection to omit the article section.', 'greendawn' ),
		);
		$fields['faqs']  = array(
			'key'          => 'field_greendawn_aftercare_faqs',
			'name'         => 'greendawn_aftercare_faqs',
			'label'        => __( 'Questions and complete answers', 'greendawn' ),
			'type'         => 'repeater',
			'layout'       => 'block',
			'collapsed'    => 'field_greendawn_aftercare_faq_question',
			'button_label' => __( 'Add FAQ', 'greendawn' ),
			'sub_fields'   => array(
				array(
					'key'      => 'field_greendawn_aftercare_faq_question',
					'name'     => 'question',
					'label'    => __( 'Question', 'greendawn' ),
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'          => 'field_greendawn_aftercare_faq_answer',
					'name'         => 'answer',
					'label'        => __( 'Answer', 'greendawn' ),
					'type'         => 'wysiwyg',
					'tabs'         => 'visual',
					'toolbar'      => 'basic',
					'media_upload' => 0,
				),
			),
			'default_rows' => array(
				array(
					'question' => 'Do I need aftercare if my chargers are brand new?',
					'answer'   => '<p>Yes.</p><p>New chargers still require:</p><ul>
<li>
<p>Active backoffice software (if accepting payments)</p>
</li>
<li>
<p>Compliance oversight</p>
</li>
<li>
<p>Driver support (for public charging above 7kW)</p>
</li>
<li>
<p>Ongoing electrical inspection and testing</p>
</li>
</ul><p>Installation alone does not cover long-term operation or regulatory responsibility.</p>',
				),
				array(
					'question' => 'Can I accept payments without a backoffice platform?',
					'answer'   => '<p><strong>No.</strong></p><p>If your chargers are designed to accept payments, they must be connected to an active backoffice system.</p><p>The backoffice:</p><ul>
<li>
<p>Handles transaction processing</p>
</li>
<li>
<p>Records payments</p>
</li>
<li>
<p>Enables reporting</p>
</li>
<li>
<p>Ensures regulatory compliance</p>
</li>
</ul><p>Backoffice software is essential for accepting transactions</p><p></p>',
				),
				array(
					'question' => 'What happens if I don’t have an aftercare plan?',
					'answer'   => '<p>Without structured aftercare:</p><ul>
<li>
<p>Full call-out rates apply</p>
</li>
<li>
<p>Downtime may increase</p>
</li>
<li>
<p>Compliance risk increases</p>
</li>
<li>
<p>Warranty protection may be affected</p>
</li>
<li>
<p>Transaction capability could be disrupted</p>
</li>
</ul><p>Reactive support is typically more expensive and less predictable than managed aftercare.</p>',
				),
			),
			'instructions' => __( 'The initial three FAQs retain the complete source answers. Editing or reordering changes content only; saved empty rows stay empty.', 'greendawn' ),
		);
		$fields['form']  = array(
			'key'           => 'field_greendawn_aftercare_form',
			'name'          => 'greendawn_aftercare_form',
			'label'         => __( 'Contact Form 7 form', 'greendawn' ),
			'type'          => 'post_object',
			'post_type'     => array( 'wpcf7_contact_form' ),
			'post_status'   => array( 'publish' ),
			'allow_null'    => 1,
			'return_format' => 'id',
			'instructions'  => __( 'Select this page’s form. Configure its fields, email recipient and messages in Contact Form 7. This selection does not use the Contact template’s Customiser setting.', 'greendawn' ),
		);
		foreach ( $fields as $name => &$field ) {
			if ( 'wysiwyg' === $field['type'] ) {
				$field['tabs']         = 'visual';
				$field['toolbar']      = 'basic';
				$field['media_upload'] = 0;
			}
			if ( 'heading' === $name || str_ends_with( $name, '_heading' ) || str_ends_with( $name, '_title' ) ) {
				$field['required'] = 1;
			}
		}
		unset( $field );
		unset( $fields['form_heading']['required'] );
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
		if ( 'repeater' === $field['type'] ) {
			return $this->faq_rows( $post_id );
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
			if ( 'repeater' === $field['type'] ) {
				return array_map(
					static fn( array $row ): array => array(
						'field_greendawn_aftercare_faq_question' => $row['question'],
						'field_greendawn_aftercare_faq_answer' => $row['answer'],
					),
					$field['default_rows']
				);
			}
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
				if ( 'greendawn_aftercare_hero_image' === $field['name'] && has_post_thumbnail( $post_id ) ) {
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
	 * Reads SCF repeater storage without depending on SCF being active.
	 *
	 * @param int $post_id Page record.
	 * @return array<int,array<string,string>>
	 */
	public function faq_rows( int $post_id ): array {
		$name = 'greendawn_aftercare_faqs';
		if ( ! metadata_exists( 'post', $post_id, $name ) ) {
			return $this->fields()['faqs']['default_rows'];
		}
		$count = get_post_meta( $post_id, $name, true );
		if ( ! is_numeric( $count ) ) {
			return array();
		}
		$rows  = array();
		$count = min( 200, max( 0, (int) $count ) );
		for ( $i = 0; $i < $count; ++$i ) {
			$row = array();
			foreach ( array( 'question', 'answer' ) as $key ) {
				$value       = get_post_meta( $post_id, $name . '_' . $i . '_' . $key, true );
				$row[ $key ] = is_scalar( $value ) ? (string) $value : '';
			}
			$rows[] = $row;
		}
		return $rows;
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
			'story_heading'     => __( 'Introduction', 'greendawn' ),
			'offerings_heading' => __( 'Offerings', 'greendawn' ),
			'faq_heading'       => __( 'FAQs and enquiry', 'greendawn' ),
			'posts_heading'     => __( 'Articles', 'greendawn' ),
		);
		$groups = array(
			'heading'           => array( 'heading', 'accent', 'intro', 'hero_image' ),
			'story_heading'     => array( 'story_heading', 'story', 'story_image' ),
			'offerings_heading' => array( 'offerings_heading', 'offering_1_title', 'offering_1_copy', 'offering_2_title', 'offering_2_copy', 'offering_3_title', 'offering_3_copy', 'offering_4_title', 'offering_4_copy' ),
			'faq_heading'       => array( 'faq_heading', 'faqs', 'form_heading', 'form' ),
			'posts_heading'     => array( 'posts_heading', 'posts' ),
		);
		$fields = array();
		$schema = $this->fields();
		foreach ( $groups as $tab => $names ) {
			$fields[] = array(
				'key'   => 'field_greendawn_aftercare_tab_' . $tab,
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
				'key'      => 'group_greendawn_aftercare',
				'title'    => __( 'Greendawn Aftercare', 'greendawn' ),
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
