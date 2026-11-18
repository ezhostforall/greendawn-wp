<?php
/**
 * Campaign content, featured selection and date phases.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/** Registers and resolves editable campaign records. */
final class Campaigns implements Module_Interface {

	public const TYPE            = 'gdcampaign';
	public const WORKPLACE_SLUG  = 'workplace-charging-grant';
	public const FEATURED_OPTION = 'greendawn_core_featured_campaign';

	/** Register WordPress and SCF hooks. */
	public function register(): void {
		add_action( 'init', array( $this, 'register_type' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'request', array( $this, 'workplace_route' ) );
		add_filter( 'post_type_link', array( $this, 'workplace_link' ), 10, 2 );
		add_action( 'admin_post_greendawn_core_publish_workplace_campaign', array( $this, 'publish_workplace' ) );
		add_action( 'save_post_' . self::TYPE, array( self::class, 'refresh_public_cache' ), 50, 0 );
		add_action( 'trashed_post', array( $this, 'refresh_trashed_campaign' ) );
		add_action( 'update_option_' . self::FEATURED_OPTION, array( self::class, 'refresh_public_cache' ), 10, 0 );
		add_action( 'add_option_' . self::FEATURED_OPTION, array( self::class, 'refresh_public_cache' ), 10, 0 );
		add_action( 'greendawn_core_campaign_boundary', array( self::class, 'refresh_public_cache' ) );
	}

	/** Register editable campaign landing pages. */
	public function register_type(): void {
		register_post_type(
			self::TYPE,
			array(
				'labels'       => array(
					'name'          => __( 'Campaigns', 'greendawn-core' ),
					'singular_name' => __( 'Campaign', 'greendawn-core' ),
					'add_new_item'  => __( 'Add campaign', 'greendawn-core' ),
					'edit_item'     => __( 'Edit campaign', 'greendawn-core' ),
				),
				'public'       => true,
				'has_archive'  => false,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-megaphone',
				'supports'     => array( 'title', 'thumbnail', 'revisions' ),
				'rewrite'      => array(
					'slug'       => 'campaigns',
					'with_front' => false,
				),
			)
		);
		if ( '1' !== get_option( 'greendawn_core_campaign_rewrites' ) ) {
			add_action( 'init', array( $this, 'refresh_rewrites' ), 90 );
		}
	}

	/** Refresh the campaign permalink rules for new installations. */
	public function refresh_rewrites(): void {
		flush_rewrite_rules( false );
		update_option( 'greendawn_core_campaign_rewrites', '1', false );
	}

	/**
	 * Resolve the vanity URL for the advert destination.
	 *
	 * @param array $vars WordPress request query variables.
	 */
	public function workplace_route( array $vars ): array {
		global $wp;
		if ( self::WORKPLACE_SLUG !== $wp->request || self::root_collision() ) {
			return $vars;
		}
		$post = get_page_by_path( self::WORKPLACE_SLUG, OBJECT, array( self::TYPE ) );
		if ( $post && 'publish' === $post->post_status && '' === $post->post_password ) {
			unset( $vars['pagename'], $vars['name'], $vars['error'], $vars[ self::TYPE ] );
			$vars['post_type'] = self::TYPE;
			$vars['p']         = $post->ID;
		}
		return $vars;
	}

	/**
	 * Expose the vanity URL as the first campaign’s canonical link.
	 *
	 * @param string   $url  Native post permalink.
	 * @param \WP_Post $post Campaign post.
	 */
	public function workplace_link( string $url, \WP_Post $post ): string {
		return self::TYPE === $post->post_type && self::WORKPLACE_SLUG === $post->post_name && 'publish' === $post->post_status && '' === $post->post_password && ! self::root_collision()
			? home_url( '/' . self::WORKPLACE_SLUG . '/' )
			: $url;
	}

	/** Check whether a native page or post already owns the grant slug. */
	private static function root_collision(): bool {
		return null !== get_page_by_path( self::WORKPLACE_SLUG, OBJECT, array( 'page', 'post' ) );
	}

	/** Campaign field names map directly from the editor to post metadata. */
	private static function fields(): array {
		return array(
			'Dates'            => array(
				'start'    => array( 'Promotion begins (optional)', 'date_picker' ),
				'planning' => array( 'Planning target (optional)', 'date_picker' ),
				'expiry'   => array( 'Promotion ends (inclusive)', 'date_picker' ),
			),
			'Landing page'     => array(
				'hero_image'       => array( 'Hero image', 'image' ),
				'kicker'           => array( 'Hero kicker' ),
				'headline'         => array( 'Hero headline' ),
				'accent'           => array( 'Hero accent' ),
				'intro'            => array( 'Hero intro', 'textarea' ),
				'late_intro'       => array( 'Hero intro after planning target', 'textarea' ),
				'closed_intro'     => array( 'Hero intro after expiry', 'textarea' ),
				'cta'              => array( 'Primary CTA' ),
				'late_cta'         => array( 'Primary CTA after planning target' ),
				'closed_cta'       => array( 'Primary CTA after expiry' ),
				'guide_link'       => array( 'Guide link label' ),
				'guide_title'      => array( 'Guide heading' ),
				'guide_intro'      => array( 'Guide intro', 'textarea' ),
				'closed_guide'     => array( 'Guide heading after expiry' ),
				'closed_summary'   => array( 'Guide intro after expiry', 'textarea' ),
				'faq_question'     => array( 'Grant FAQ question' ),
				'faq_answer'       => array( 'Grant FAQ answer', 'textarea' ),
				'voucher_question' => array( 'Voucher FAQ question' ),
				'voucher_answer'   => array( 'Voucher FAQ answer', 'textarea' ),
				'guidance_url'     => array( 'Official guidance URL', 'url' ),
				'panel_title'      => array( 'Contact panel heading' ),
				'panel_copy'       => array( 'Contact panel copy', 'textarea' ),
				'closed_panel'     => array( 'Contact panel copy after expiry', 'textarea' ),
				'planning_label'   => array( 'Planning card label' ),
				'planning_hint'    => array( 'Planning card context' ),
				'late_hint'        => array( 'Planning card context after target' ),
				'expiry_label'     => array( 'Expiry card label' ),
				'expiry_hint'      => array( 'Expiry card context' ),
			),
			'Sitewide notices' => array(
				'bar_title'      => array( 'Bar heading' ),
				'bar_copy'       => array( 'Bar supporting text' ),
				'late_bar'       => array( 'Bar supporting text after planning target' ),
				'bar_cta'        => array( 'Bar link label' ),
				'popup_title'    => array( 'Pop-up heading' ),
				'popup_planning' => array( 'Pop-up planning heading' ),
				'popup_note'     => array( 'Pop-up planning context', 'textarea' ),
				'late_popup'     => array( 'Pop-up planning heading after target' ),
				'late_note'      => array( 'Pop-up planning context after target', 'textarea' ),
				'popup_expiry'   => array( 'Pop-up expiry heading' ),
				'popup_copy'     => array( 'Pop-up summary', 'textarea' ),
				'popup_cta'      => array( 'Pop-up enquiry link label' ),
				'popup_guide'    => array( 'Pop-up guide link label' ),
				'drawer_title'   => array( 'Drawer notice heading' ),
				'drawer_copy'    => array( 'Drawer notice copy', 'textarea' ),
				'late_drawer'    => array( 'Drawer notice copy after planning target', 'textarea' ),
				'drawer_cta'     => array( 'Drawer grant enquiry button' ),
				'drawer_context' => array( 'Drawer grant contact intro', 'textarea' ),
			),
		);
	}

	/** Register campaign field groups when SCF is available. */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		foreach ( self::fields() as $group => $definitions ) {
			$fields = array();
			foreach ( $definitions as $key => $definition ) {
				$field = array(
					'key'   => 'field_greendawn_campaign_' . $key,
					'name'  => 'greendawn_campaign_' . $key,
					'label' => $definition[0],
					'type'  => $definition[1] ?? 'text',
				);
				if ( 'image' === $field['type'] ) {
					$field['return_format'] = 'id';
					$field['preview_size']  = 'medium';
					$field['instructions']  = __( 'Select the landing page hero photograph. If empty, the Featured Image or campaign default is used.', 'greendawn-core' );
				}
				if ( 'date_picker' === $field['type'] ) {
					$field['display_format'] = 'j F Y';
					$field['return_format']  = 'Ymd';
				}
				if ( in_array( $key, array( 'intro', 'late_intro', 'bar_title', 'bar_copy', 'popup_planning', 'popup_expiry', 'drawer_title', 'drawer_copy' ), true ) ) {
					$field['instructions'] = __( 'Use {{planning}}, {{planning_short}}, or {{expiry}} to display the corresponding campaign date.', 'greendawn-core' );
				}
				$fields[] = $field;
			}
			acf_add_local_field_group(
				array(
					'key'      => 'group_greendawn_campaign_' . sanitize_title( $group ),
					'title'    => $group,
					'fields'   => $fields,
					'location' => array(
						array(
							array(
								'param'    => 'post_type',
								'operator' => '==',
								'value'    => self::TYPE,
							),
						),
					),
				)
			);
		}
	}

	/**
	 * Accept only public campaign records.
	 *
	 * @param mixed $id Candidate campaign ID.
	 */
	public static function public_id( $id ): int {
		$post = is_scalar( $id ) && ctype_digit( (string) $id ) && (int) $id > 0 ? get_post( (int) $id ) : null;
		return $post && self::TYPE === $post->post_type && 'publish' === $post->post_status && '' === $post->post_password ? $post->ID : 0;
	}

	/**
	 * Read the campaign fields from saved post metadata.
	 *
	 * @param int $id Campaign post ID.
	 */
	public static function content( int $id ): array {
		$content = array();
		foreach ( self::fields() as $definitions ) {
			foreach ( $definitions as $key => $definition ) {
				$content[ $key ] = (string) get_post_meta( $id, 'greendawn_campaign_' . $key, true );
			}
		}
		return $content;
	}

	/**
	 * Resolve a local date or the first second after an inclusive deadline.
	 *
	 * @param string $date      SCF date in Ymd format.
	 * @param bool   $inclusive Whether to return the day after the date.
	 */
	public static function boundary( string $date, bool $inclusive = false ): ?int {
		if ( ! preg_match( '/^[0-9]{8}$/D', $date ) ) {
			return null;
		}
		$day = \DateTimeImmutable::createFromFormat( '!Ymd', $date, new \DateTimeZone( 'Europe/London' ) );
		if ( ! $day || $day->format( 'Ymd' ) !== $date ) {
			return null;
		}
		return ( $inclusive ? $day->modify( '+1 day' ) : $day )->getTimestamp();
	}

	/**
	 * Resolve campaign copy and its scheduled phases.
	 *
	 * @param int $id Campaign post ID.
	 */
	public static function campaign( int $id ): array {
		$content = self::content( $id );
		$start   = self::boundary( $content['start'] );
		$late    = self::boundary( $content['planning'], true );
		$end     = self::boundary( $content['expiry'], true );
		$now     = (int) apply_filters( 'greendawn_core_campaign_time', time() );
		$zone    = new \DateTimeZone( 'Europe/London' );
		$dates   = array(
			'{{planning}}'       => $late ? wp_date( 'j F Y', $late - 1, $zone ) : '',
			'{{planning_short}}' => $late ? wp_date( 'j F', $late - 1, $zone ) : '',
			'{{expiry}}'         => $end ? wp_date( 'j F Y', $end - 1, $zone ) : '',
		);
		foreach ( $content as $key => $copy ) {
			if ( ! in_array( $key, array( 'start', 'planning', 'expiry', 'guidance_url' ), true ) ) {
				$content[ $key ] = strtr( $copy, $dates );
			}
		}
		$phase = ! $end || $now >= $end ? 'closed' : ( $start && $now < $start ? 'upcoming' : ( $late && $now >= $late ? 'late' : 'live' ) );
		return $content + array(
			'id'       => $id,
			'url'      => get_permalink( $id ),
			'phase'    => $phase,
			'startsAt' => $start,
			'lateAt'   => $late,
			'endsAt'   => $end,
		);
	}

	/** Resolve the featured campaign for the sitewide notices. */
	public static function featured(): array {
		$id = self::public_id( get_option( self::FEATURED_OPTION, 0 ) );
		return $id ? self::campaign( $id ) : array();
	}

	/** Resolve the requested campaign landing page. */
	public static function current(): array {
		return is_singular( self::TYPE ) && ! post_password_required() ? self::campaign( get_queried_object_id() ) : array();
	}

	/**
	 * Refresh cached notices after a campaign is trashed.
	 *
	 * @param int $id Trashed post ID.
	 */
	public function refresh_trashed_campaign( int $id ): void {
		if ( self::TYPE === get_post_type( $id ) ) {
			self::refresh_public_cache();
		}
	}

	/** Purge cached pages on edits and at the upcoming campaign boundary, when SiteGround is active. */
	public static function refresh_public_cache(): void {
		if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
			sg_cachepress_purge_cache();
		}
		$now  = time();
		$next = PHP_INT_MAX;
		$ids  = get_posts(
			array(
				'post_type'      => self::TYPE,
				'post_status'    => 'publish',
				'has_password'   => false,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		update_meta_cache( 'post', $ids );
		foreach ( $ids as $id ) {
			$campaign = self::campaign( $id );
			foreach ( array( 'startsAt', 'lateAt', 'endsAt' ) as $key ) {
				$time = $campaign[ $key ];
				if ( $time && $time > $now && $time < $next ) {
					$next = $time;
				}
			}
		}
		$scheduled = wp_next_scheduled( 'greendawn_core_campaign_boundary' );
		if ( $scheduled === $next ) {
			return;
		}
		wp_clear_scheduled_hook( 'greendawn_core_campaign_boundary' );
		if ( PHP_INT_MAX !== $next ) {
			wp_schedule_single_event( $next, 'greendawn_core_campaign_boundary' );
		}
	}

	/** Publish the reviewed starting record through an explicit administrator action. */
	public function publish_workplace(): void {
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'publish_posts' ) ) {
			wp_die( esc_html__( 'You cannot publish campaigns.', 'greendawn-core' ) );
		}
		check_admin_referer( 'greendawn_core_publish_workplace_campaign' );
		$id = self::provision_workplace();
		if ( is_wp_error( $id ) ) {
			wp_die( esc_html( $id->get_error_message() ) );
		}
		update_option( self::FEATURED_OPTION, $id, false );
		wp_safe_redirect( admin_url( 'post.php?post=' . $id . '&action=edit' ) );
		exit;
	}

	/** Keep editorial changes if the setup action is run again. */
	public static function provision_workplace() {
		if ( self::root_collision() ) {
			return new \WP_Error( 'campaign_route_taken', __( 'The workplace charging grant URL is already used by another WordPress record.', 'greendawn-core' ) );
		}
		$existing = get_page_by_path( self::WORKPLACE_SLUG, OBJECT, array( self::TYPE ) );
		if ( $existing ) {
			$id = self::public_id( $existing->ID );
			return $id ? $id : new \WP_Error( 'campaign_not_public', __( 'Publish the existing workplace charging campaign before featuring it.', 'greendawn-core' ) );
		}
		if ( self::WORKPLACE_SLUG !== wp_unique_post_slug( self::WORKPLACE_SLUG, 0, 'publish', self::TYPE, 0 ) ) {
			return new \WP_Error( 'campaign_slug_taken', __( 'The workplace charging campaign slug is already in use.', 'greendawn-core' ) );
		}
		$values = require dirname( __DIR__ ) . '/campaign-workplace-charging.php';
		$meta   = array();
		foreach ( $values as $name => $value ) {
			$meta[ 'greendawn_campaign_' . $name ]  = $value;
			$meta[ '_greendawn_campaign_' . $name ] = 'field_greendawn_campaign_' . $name;
		}
		return wp_insert_post(
			array(
				'post_type'   => self::TYPE,
				'post_status' => 'publish',
				'post_name'   => self::WORKPLACE_SLUG,
				'post_title'  => 'Workplace Charging Scheme',
				'meta_input'  => $meta,
			),
			true
		);
	}
}
