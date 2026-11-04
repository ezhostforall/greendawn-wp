<?php
/**
 * Contact Form 7 requirement for the starter theme.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prompts administrators to set up Contact Form 7 if it is unavailable.
 */
function greendawn_contact_form_7_notice(): void {
	if ( ! current_user_can( 'manage_options' ) || shortcode_exists( 'contact-form-7' ) ) {
		return;
	}

	$install_url = add_query_arg(
		array(
			'tab'  => 'search',
			'type' => 'term',
			's'    => 'Contact Form 7',
		),
		admin_url( 'plugin-install.php' )
	);
	?>
	<div class="notice notice-warning">
		<p><?php esc_html_e( 'This theme requires Contact Form 7 for contact forms. Install and activate it to complete setup.', 'greendawn' ); ?></p>
		<?php if ( current_user_can( 'install_plugins' ) || current_user_can( 'activate_plugins' ) ) : ?>
			<p>
				<?php if ( current_user_can( 'install_plugins' ) ) : ?>
					<a href="<?php echo esc_url( $install_url ); ?>"><?php esc_html_e( 'Find Contact Form 7', 'greendawn' ); ?></a>
				<?php endif; ?>
				<?php if ( current_user_can( 'activate_plugins' ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>"><?php esc_html_e( 'View installed plugins', 'greendawn' ); ?></a>
				<?php endif; ?>
			</p>
		<?php endif; ?>
	</div>
	<?php
}
add_action( 'admin_notices', 'greendawn_contact_form_7_notice' );

/**
 * Checks whether an ID identifies a published Contact Form 7 form.
 *
 * @param mixed $value Candidate form ID.
 */
function greendawn_contact_form_id( $value ): int {
	$id = is_numeric( $value ) ? (int) $value : 0;
	if ( $id < 1 || 'wpcf7_contact_form' !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) ) {
		return 0;
	}

	return $id;
}

/**
 * Registers the administrator's form selection.
 *
 * @param WP_Customize_Manager $customizer WordPress Customiser.
 */
function greendawn_customize_contact_form( WP_Customize_Manager $customizer ): void {
	if ( ! shortcode_exists( 'contact-form-7' ) ) {
		return;
	}

	$choices = array( 0 => __( 'Select a form', 'greendawn' ) );
	$forms   = get_posts(
		array(
			'post_type'      => 'wpcf7_contact_form',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	foreach ( $forms as $form ) {
		$choices[ $form->ID ] = sprintf(
			/* translators: 1: form title, 2: form ID. */
			__( '%1$s (#%2$d)', 'greendawn' ),
			$form->post_title,
			$form->ID
		);
	}

	$customizer->add_section( 'greendawn_contact', array( 'title' => __( 'Contact', 'greendawn' ) ) );
	$customizer->add_setting(
		'greendawn_contact_form',
		array(
			'default'           => 0,
			'capability'        => 'manage_options',
			'sanitize_callback' => 'greendawn_contact_form_id',
		)
	);
	$customizer->add_control(
		'greendawn_contact_form',
		array(
			'type'        => 'select',
			'section'     => 'greendawn_contact',
			'label'       => __( 'Enquiry form', 'greendawn' ),
			'description' => __( 'Create and configure forms in Contact → Contact Forms.', 'greendawn' ),
			'choices'     => $choices,
		)
	);
}
add_action( 'customize_register', 'greendawn_customize_contact_form' );
