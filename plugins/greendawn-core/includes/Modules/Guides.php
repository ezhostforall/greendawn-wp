<?php
/**
 * Guides registration, editor validation and structured content lifecycle.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/** Keeps editorial content in Core independently of the active theme. */
final class Guides implements Module_Interface {

	public const TYPE      = 'gdguide';
	public const TAXONOMY  = 'gdguide_topic';
	public const TEMPLATES = array(
		'hub'    => 'templates/greendawn-guides.php',
		'region' => 'templates/greendawn-region.php',
	);
	public const SNAPSHOT  = '_greendawn_guides_snapshot';

	/**
	 * Records awaiting a complete metadata save.
	 *
	 * @var array<int,bool>
	 */
	private static array $pending = array();
	/**
	 * Prevent recursive derived-data and revision writes.
	 *
	 * @var bool
	 */
	private static bool $busy = false;

	/** Register only hooks; no records, terms or site settings are provisioned. */
	public function register(): void {
		add_action( 'init', array( $this, 'register_content' ) );
		add_action( 'init', array( $this, 'refresh_rewrites' ), 20 );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'request', array( $this, 'resolve_route' ) );
		add_filter( 'use_block_editor_for_post', array( $this, 'use_block_editor' ), 10, 2 );
		add_action( 'add_meta_boxes_page', array( $this, 'hide_body_editor' ) );
		add_filter( 'acf/validate_value', array( $this, 'validate_field' ), 20, 4 );
		add_filter( 'acf/update_value', array( $this, 'sanitise_field' ), 5, 3 );
		add_action( 'acf/validate_save_post', array( $this, 'validate_editor' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'guard_publication' ), 20, 2 );
		add_action( 'admin_notices', array( $this, 'notice' ) );
		foreach ( array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $hook ) {
			add_action( $hook, array( $this, 'meta_changed' ), 10, 3 );
		}
		add_action( 'set_object_terms', array( $this, 'terms_changed' ), 10, 4 );
		add_action( 'deleted_term_relationships', array( $this, 'terms_removed' ), 10, 3 );
		add_action( 'edited_' . self::TAXONOMY, array( $this, 'topic_edited' ) );
		add_action( 'save_post', array( $this, 'queue' ), 99 );
		add_action( 'acf/save_post', array( self::class, 'commit' ), 30 );
		add_action( 'wp_after_insert_post', array( self::class, 'commit' ), 30 );
		add_action( 'import_end', array( self::class, 'flush' ) );
		add_action( 'shutdown', array( self::class, 'flush' ) );
		add_action( '_wp_put_post_revision', array( $this, 'capture_revision' ) );
		add_filter( 'wp_save_post_revision_post_has_changed', array( $this, 'revision_changed' ), 20, 3 );
		add_action( 'wp_restore_post_revision', array( $this, 'restore_revision' ), 100, 2 );
		add_filter( 'wp_get_revision_ui_diff', array( $this, 'revision_diff' ), 10, 3 );
	}

	/** Public singles under /guides/, while the Page retains the hub URL. */
	public function register_content(): void {
		register_post_type(
			self::TYPE,
			array(
				'labels'           => array(
					'name'          => __( 'Guides', 'greendawn' ),
					'singular_name' => __( 'Guide', 'greendawn' ),
					'add_new_item'  => __( 'Add Guide', 'greendawn' ),
					'edit_item'     => __( 'Edit Guide', 'greendawn' ),
				),
				'public'           => true,
				'show_in_rest'     => false,
				'has_archive'      => false,
				'rewrite'          => array(
					'slug'       => 'guides',
					'with_front' => false,
					'feeds'      => false,
				),
				'supports'         => array( 'title', 'revisions', 'custom-fields' ),
				'capability_type'  => 'page',
				'map_meta_cap'     => true,
				'can_export'       => true,
				'delete_with_user' => false,
				'menu_icon'        => 'dashicons-book-alt',
			)
		);
		register_taxonomy(
			self::TAXONOMY,
			array( self::TYPE ),
			array(
				'labels'             => array(
					'name'          => __( 'Guide Topics', 'greendawn' ),
					'singular_name' => __( 'Guide Topic', 'greendawn' ),
				),
				'hierarchical'       => true,
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_admin_column'  => true,
				'show_in_rest'       => false,
				'rewrite'            => false,
				'query_var'          => false,
				'capabilities'       => array(
					'manage_terms' => 'edit_pages',
					'edit_terms'   => 'edit_pages',
					'delete_terms' => 'edit_pages',
					'assign_terms' => 'edit_pages',
				),
			)
		);
	}

	/** Flush after registration once, never on each public request. */
	public function refresh_rewrites(): void {
		if ( Guide_Fields::VERSION !== get_option( 'greendawn_core_guides_rewrite_version' ) ) {
			flush_rewrite_rules( false );
			update_option( 'greendawn_core_guides_rewrite_version', Guide_Fields::VERSION, false );
		}
	}

	/**
	 * Existing published/private Pages keep their URLs when CPT rules are added.
	 *
	 * @param array $query Parsed rewrite query.
	 */
	public function resolve_route( array $query ): array {
		if ( ! isset( $query[ self::TYPE ] ) || ! is_string( $query[ self::TYPE ] ) ) {
			return $query;
		}
		$path = 'guides/' . sanitize_title( $query[ self::TYPE ] );
		$page = get_page_by_path( $path, OBJECT, 'page' );
		if ( $page && in_array( $page->post_status, array( 'publish', 'private' ), true ) ) {
			unset( $query[ self::TYPE ], $query['name'], $query['post_type'] );
			$query['pagename'] = $path;
		}
		return $query;
	}

	/** Register the three field groups; future Page templates remain theme-owned. */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		foreach ( array_keys( Guide_Fields::PREFIXES ) as $kind ) {
			acf_add_local_field_group(
				array(
					'key'      => 'group_' . rtrim( Guide_Fields::PREFIXES[ $kind ], '_' ),
					'title'    => array(
						'guide'  => __( 'Guide content', 'greendawn' ),
						'hub'    => __( 'Guides hub content', 'greendawn' ),
						'region' => __( 'Regional service content', 'greendawn' ),
					)[ $kind ],
					'fields'   => Guide_Fields::fields( $kind ),
					'location' => array(
						array(
							array(
								'param'    => 'guide' === $kind ? 'post_type' : 'page_template',
								'operator' => '==',
								'value'    => 'guide' === $kind ? self::TYPE : self::TEMPLATES[ $kind ],
							),
						),
					),
				)
			);
		}
	}

	/**
	 * Preserve Gutenberg for unrelated posts and Pages.
	 *
	 * @param bool     $use_editor Existing decision.
	 * @param \WP_Post $post Current record.
	 */
	public function use_block_editor( bool $use_editor, \WP_Post $post ): bool {
		return $use_editor && ! Guide_Content::kind( $post->ID );
	}

	/**
	 * Restrict the Page body editor only on an assigned structured edit screen.
	 *
	 * @param \WP_Post $post Current Page.
	 */
	public function hide_body_editor( \WP_Post $post ): void {
		if ( Guide_Content::kind( $post->ID ) ) {
			remove_post_type_support( 'page', 'editor' );
		}
	}

	/**
	 * Validate individual fields even while saving an incomplete draft.
	 *
	 * @param mixed $valid Previous validation result.
	 * @param mixed $value Submitted value.
	 * @param array $field SCF field.
	 * @param mixed $input Input identifier.
	 * @return mixed
	 */
	public function validate_field( $valid, $value, array $field, $input ) {
		$definition = Guide_Fields::find( $field['key'] ?? '' );
		if ( true !== $valid || ! $definition ) {
			return $valid;
		}
		// SCF represents a cleared repeater/relationship as false, not an empty array.
		$value  = self::empty_list( $value, $definition );
		$errors = Guide_Content::field_errors( $value, $definition );
		return $errors ? implode( ' ', $errors ) : $valid;
	}

	/**
	 * Sanitise only our canonical fields; other modules' fields are untouched.
	 *
	 * @param mixed $value Input value.
	 * @param mixed $id SCF record identifier.
	 * @param array $field SCF field.
	 * @return mixed
	 */
	public function sanitise_field( $value, $id, array $field ) {
		$definition = Guide_Fields::find( $field['key'] ?? '' );
		return $definition ? Guide_Content::normalise( $value, $definition ) : $value;
	}

	/**
	 * Normalise SCF's empty-list representation before validation.
	 *
	 * @param mixed $value Raw value.
	 * @param array $field Definition.
	 * @return mixed
	 */
	private static function empty_list( $value, array $field ) {
		$list = in_array( $field['type'], array( 'repeater', 'relationship' ), true ) || ! empty( $field['multiple'] );
		return $list && ( false === $value || '' === $value || null === $value ) ? array() : $value;
	}

	/**
	 * Read the pending editor fields only inside a verified SCF post form.
	 *
	 * @param int  $id Record ID.
	 * @param bool $validated SCF has already authenticated its validation workflow.
	 */
	private static function pending_data( int $id, bool $validated = false ): array {
		$data = Guide_Content::read( $id );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Explicitly verified before reading the pending field payload.
		$nonce = isset( $_POST['_acf_nonce'] ) && is_string( $_POST['_acf_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_acf_nonce'] ) ) : '';
		if ( ( ! $validated && ! wp_verify_nonce( $nonce, 'post' ) ) || ! current_user_can( 'edit_post', $id ) ) {
			return $data;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validate raw nested values against the canonical field schema before normalising them.
		$values = isset( $_POST['acf'] ) && is_array( $_POST['acf'] ) ? wp_unslash( $_POST['acf'] ) : array();
		$kind   = Guide_Content::kind( $id );
		foreach ( Guide_Fields::fields( $kind ) as $field ) {
			if ( array_key_exists( $field['key'], $values ) ) {
				$data[ substr( $field['name'], strlen( Guide_Fields::PREFIXES[ $kind ] ) ) ] = self::empty_list( $values[ $field['key'] ], $field );
			}
		}
		return $data;
	}

	/**
	 * Obtain pending taxonomy IDs without accepting term names as database IDs.
	 *
	 * @param int   $id Record ID.
	 * @param array $tax_input Pending native taxonomy input.
	 */
	private static function topics( int $id, array $tax_input = array() ): array {
		if ( isset( $tax_input[ self::TAXONOMY ] ) && is_array( $tax_input[ self::TAXONOMY ] ) ) {
			return array_values( array_filter( array_map( 'absint', array_filter( $tax_input[ self::TAXONOMY ], 'is_scalar' ) ) ) );
		}
		$terms = wp_get_object_terms( $id, self::TAXONOMY, array( 'fields' => 'ids' ) );
		return is_wp_error( $terms ) ? array() : $terms;
	}

	/** SCF verifies its AJAX form nonce before this validation action. */
	public function validate_editor(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- SCF invokes this action inside its authenticated validation workflow; never saves data here.
		$id = absint( $_POST['_acf_post_id'] ?? $_POST['post_ID'] ?? 0 );
		if ( ! $id || ! Guide_Content::kind( $id ) || ! current_user_can( 'edit_post', $id ) ) {
			return;
		}
		$publish = isset( $_POST['publish'] ) || in_array( $_POST['post_status'] ?? get_post_status( $id ), array( 'publish', 'future', 'private' ), true );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- topics() extracts scalar integer IDs only.
		$tax = isset( $_POST['tax_input'] ) && is_array( $_POST['tax_input'] ) ? wp_unslash( $_POST['tax_input'] ) : array();
		foreach ( Guide_Content::errors( $id, self::pending_data( $id, true ), $publish, self::topics( $id, $tax ) ) as $error ) {
			acf_add_validation_error( '', $error );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Native API publishing also requires complete saved content; drafts stay editable.
	 *
	 * @param array $data Slashed native post data.
	 * @param array $postarr Native input.
	 */
	public function guard_publication( array $data, array $postarr ): array {
		if ( ! in_array( $data['post_status'], array( 'publish', 'future', 'private' ), true ) ) {
			return $data;
		}
		if ( 'page' === $data['post_type'] && ! empty( $data['post_parent'] ) && 'guides' === get_page_uri( $data['post_parent'] ) ) {
			$guide = get_page_by_path( $data['post_name'], OBJECT, self::TYPE );
			if ( $guide && in_array( $guide->post_status, array( 'publish', 'private', 'future' ), true ) ) {
				$data['post_status'] = 'draft';
				if ( get_current_user_id() ) {
					set_transient( 'greendawn_guide_validation_' . get_current_user_id(), array( __( 'An existing Guide owns this URL. Resolve the route before publishing the Page.', 'greendawn' ) ), MINUTE_IN_SECONDS );
				}
			}
			return $data;
		}
		if ( self::TYPE !== $data['post_type'] ) {
			return $data;
		}
		$id     = absint( $postarr['ID'] ?? 0 );
		$tax    = isset( $postarr['tax_input'] ) && is_array( $postarr['tax_input'] ) ? $postarr['tax_input'] : array();
		$errors = $id ? Guide_Content::errors( $id, self::pending_data( $id ), true, self::topics( $id, $tax ) ) : array( __( 'Save a draft and complete its SCF content before publishing.', 'greendawn' ) );
		if ( '' === trim( wp_unslash( $data['post_title'] ) ) ) {
			$errors[] = __( 'A Guide needs a title before publishing.', 'greendawn' );
		}
		$collision = get_page_by_path( 'guides/' . $data['post_name'], OBJECT, 'page' );
		if ( $collision && in_array( $collision->post_status, array( 'publish', 'private', 'future' ), true ) ) {
			$errors[] = __( 'An existing Page owns this Guide URL. Resolve the route before publishing.', 'greendawn' );
		}
		if ( $errors ) {
			$data['post_status'] = 'draft';
			if ( get_current_user_id() ) {
				set_transient( 'greendawn_guide_validation_' . get_current_user_id(), $errors, MINUTE_IN_SECONDS );
			}
		}
		return $data;
	}

	/** Explain publication held by server-side validation after the native redirect. */
	public function notice(): void {
		$key    = 'greendawn_guide_validation_' . get_current_user_id();
		$errors = get_transient( $key );
		if ( $errors && current_user_can( 'edit_pages' ) ) {
			delete_transient( $key );
			echo '<div class="notice notice-error"><p>' . esc_html( __( 'Publication held; content saved as a draft.', 'greendawn' ) . ' ' . implode( ' ', $errors ) ) . '</p></div>';
		}
	}

	/**
	 * Metadata writes may happen after save_post; batch them until a complete save.
	 *
	 * @param mixed  $meta_id Metadata ID(s).
	 * @param int    $id Record ID.
	 * @param string $key Metadata key.
	 */
	public function meta_changed( $meta_id, int $id, string $key ): void {
		if ( '_wp_page_template' === $key || self::owned_key( $key ) ) {
			$this->queue( $id );
		}
	}

	/**
	 * Only our schema prefixes, never other modules' metadata or derived snapshots.
	 *
	 * @param string $key Metadata key.
	 */
	private static function owned_key( string $key ): bool {
		static $patterns;
		if ( null === $patterns ) {
			$patterns = array();
			$walk     = static function ( array $fields, string $ancestor = '' ) use ( &$walk, &$patterns ): void {
				foreach ( $fields as $field ) {
					$name       = $ancestor . preg_quote( $field['name'], '/' );
					$patterns[] = '/^_?' . $name . '$/D';
					if ( isset( $field['sub_fields'] ) ) {
						$walk( $field['sub_fields'], $name . '_[0-9]+_' );
					}
				}
			};
			foreach ( array_keys( Guide_Fields::PREFIXES ) as $kind ) {
				$walk( Guide_Fields::fields( $kind ) );
			}
		}
		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $key ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Terms belong to revision snapshots and the search projection.
	 *
	 * @param int    $id Record ID.
	 * @param mixed  $terms Submitted terms.
	 * @param array  $tt_ids Term taxonomy IDs.
	 * @param string $taxonomy Taxonomy name.
	 */
	public function terms_changed( int $id, $terms, array $tt_ids, string $taxonomy ): void {
		if ( self::TAXONOMY === $taxonomy ) {
			$this->queue( $id );
		}
	}

	/**
	 * Explicit removals do not always emit set_object_terms.
	 *
	 * @param int    $id Record ID.
	 * @param array  $tt_ids Removed term taxonomy IDs.
	 * @param string $taxonomy Taxonomy name.
	 */
	public function terms_removed( int $id, array $tt_ids, string $taxonomy ): void {
		if ( self::TAXONOMY === $taxonomy ) {
			$this->queue( $id );
		}
	}

	/**
	 * Topic renames also change the indexed human-readable text.
	 *
	 * @param int $term_id Edited topic.
	 */
	public function topic_edited( int $term_id ): void {
		$ids = get_objects_in_term( $term_id, self::TAXONOMY );
		foreach ( is_wp_error( $ids ) ? array() : $ids as $id ) {
			$this->queue( (int) $id );
		}
	}

	/**
	 * Queue actual records only. Revisions/autosaves never become public content data.
	 *
	 * @param int $id Record ID.
	 */
	public function queue( int $id ): void {
		if ( ! self::$busy && Guide_Content::kind( $id ) && ! wp_is_post_revision( $id ) ) {
			self::$pending[ $id ] = true;
		}
	}

	/** Finish queued saves after native imports and late metadata writes. */
	public static function flush(): void {
		foreach ( array_keys( self::$pending ) as $id ) {
			self::commit( $id );
		}
	}

	/**
	 * Public lifecycle entry point for future import batches and programmatic SCF saves.
	 * This changes derived data only; callers remain responsible for write permission.
	 *
	 * @param mixed $id Native/SCF record identifier.
	 */
	public static function commit( $id ): void {
		if ( ! is_numeric( $id ) || self::$busy || ! Guide_Content::kind( (int) $id ) ) {
			return;
		}
		$id = (int) $id;
		unset( self::$pending[ $id ] );
		self::$busy = true;
		Guide_Content::rebuild( $id );
		wp_save_post_revision( $id );
		self::$busy = false;
	}

	/**
	 * Snapshot flat native values and references so absent and deliberately empty differ.
	 *
	 * @param int $id Record ID.
	 */
	private static function snapshot( int $id ): array {
		$meta = array();
		foreach ( get_post_meta( $id ) as $key => $values ) {
			if ( self::owned_key( $key ) ) {
				$meta[ $key ] = array_map( 'maybe_unserialize', $values );
			}
		}
		ksort( $meta );
		return array(
			'version'  => Guide_Fields::VERSION,
			'kind'     => Guide_Content::kind( $id ),
			'template' => get_page_template_slug( $id ),
			'meta'     => $meta,
			'topics'   => self::topics( $id ),
		);
	}

	/**
	 * Store on the revision itself: update_post_meta would redirect to the parent.
	 *
	 * @param int $revision_id New revision.
	 */
	public function capture_revision( int $revision_id ): void {
		$parent = wp_is_post_revision( $revision_id );
		if ( $parent && ! wp_is_post_autosave( $revision_id ) && Guide_Content::kind( $parent ) ) {
			update_metadata( 'post', $revision_id, self::SNAPSHOT, wp_slash( self::snapshot( $parent ) ) );
		}
	}

	/**
	 * Metadata-only edits also need a native revision.
	 *
	 * @param bool     $changed Native decision.
	 * @param \WP_Post $latest Latest revision.
	 * @param \WP_Post $post Current record.
	 */
	public function revision_changed( bool $changed, \WP_Post $latest, \WP_Post $post ): bool {
		return $changed || ( Guide_Content::kind( $post->ID ) && self::snapshot( $post->ID ) !== get_metadata( 'post', $latest->ID, self::SNAPSHOT, true ) );
	}

	/**
	 * Restore our complete snapshot after SCF's own revision hooks; remove stale rows.
	 *
	 * @param int $id Parent record.
	 * @param int $revision_id Selected revision.
	 */
	public function restore_revision( int $id, int $revision_id ): void {
		$snapshot = get_metadata( 'post', $revision_id, self::SNAPSHOT, true );
		if ( ! current_user_can( 'edit_post', $id ) || (int) wp_is_post_revision( $revision_id ) !== $id || ! is_array( $snapshot ) || Guide_Fields::VERSION !== ( $snapshot['version'] ?? '' ) || ! isset( Guide_Fields::PREFIXES[ $snapshot['kind'] ?? '' ] ) || ! is_array( $snapshot['meta'] ?? null ) || ! is_array( $snapshot['topics'] ?? null ) ) {
			return;
		}
		$expected_type = 'guide' === $snapshot['kind'] ? self::TYPE : 'page';
		if ( get_post_type( $id ) !== $expected_type ) {
			return;
		}
		self::$busy = true;
		foreach ( array_keys( get_post_meta( $id ) ) as $key ) {
			if ( self::owned_key( $key ) ) {
				delete_post_meta( $id, $key );
			}
		}
		foreach ( $snapshot['meta'] as $key => $values ) {
			if ( self::owned_key( $key ) && is_array( $values ) ) {
				foreach ( $values as $value ) {
					add_post_meta( $id, $key, wp_slash( $value ) );
				}
			}
		}
		if ( 'page' === get_post_type( $id ) && in_array( $snapshot['template'] ?? '', self::TEMPLATES, true ) ) {
			update_post_meta( $id, '_wp_page_template', $snapshot['template'] );
		}
		if ( self::TYPE === get_post_type( $id ) ) {
			$topics = array_values( array_filter( array_map( 'intval', $snapshot['topics'] ), static fn( int $term ): bool => (bool) term_exists( $term, self::TAXONOMY ) ) );
			wp_set_object_terms( $id, $topics, self::TAXONOMY );
		}
		if ( function_exists( 'acf_flush_value_cache' ) ) {
			foreach ( Guide_Fields::fields( $snapshot['kind'] ) as $field ) {
				acf_flush_value_cache( $id, $field['name'] );
			}
		}
		Guide_Content::rebuild( $id );
		if ( 'guide' === $snapshot['kind'] && in_array( get_post_status( $id ), array( 'publish', 'future', 'private' ), true ) && Guide_Content::errors( $id, Guide_Content::read( $id ), true, self::topics( $id ) ) ) {
			wp_update_post(
				array(
					'ID'          => $id,
					'post_status' => 'draft',
				)
			);
		}
		unset( self::$pending[ $id ] );
		self::$busy = false;
	}

	/**
	 * Make otherwise invisible structured changes inspectable in native revision UI.
	 *
	 * @param array               $diff Existing diff rows.
	 * @param \WP_Post|false|null $from Source revision object, if present.
	 * @param \WP_Post            $to Target revision object.
	 */
	public function revision_diff( array $diff, $from, $to ): array {
		$a = $from instanceof \WP_Post ? get_metadata( 'post', $from->ID, self::SNAPSHOT, true ) : array();
		$b = $to instanceof \WP_Post ? get_metadata( 'post', $to->ID, self::SNAPSHOT, true ) : array();
		if ( $a !== $b && ( is_array( $a ) || is_array( $b ) ) ) {
			$html = wp_text_diff( wp_json_encode( $a, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ), wp_json_encode( $b, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
			if ( $html ) {
				$diff[] = array(
					'id'   => 'greendawn-structured-content',
					'name' => __( 'Structured content', 'greendawn' ),
					'diff' => $html,
				);
			}
		}
		return $diff;
	}
}
