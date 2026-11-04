<?php
/**
 * Template Name: Contact
 * Template Post Type: page
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$wp_starter_contact_intro   = trim( (string) wp_starter_get_field( 'contact_intro', false, '' ) );
	$wp_starter_contact_phone   = trim( (string) wp_starter_get_field( 'contact_phone', false, '' ) );
	$wp_starter_contact_tel     = preg_replace( '/[^0-9+]/', '', $wp_starter_contact_phone );
	$wp_starter_contact_email   = sanitize_email( (string) wp_starter_get_field( 'contact_email', false, '' ) );
	$wp_starter_contact_address = trim( (string) wp_starter_get_field( 'contact_address', false, '' ) );
	$wp_starter_contact_form_id = shortcode_exists( 'contact-form-7' ) ? wp_starter_contact_form_id( get_theme_mod( 'wp_starter_contact_form', 0 ) ) : 0;
	?>
	<article <?php post_class(); ?>>
		<header class="feature-hero">
			<div class="container feature-hero__inner">
				<div class="layout-stack feature-hero__copy">
					<?php the_title( '<h1 class="type-display">', '</h1>' ); ?>
					<?php if ( '' !== $wp_starter_contact_intro ) : ?>
						<p class="type-lead"><?php echo esc_html( $wp_starter_contact_intro ); ?></p>
					<?php elseif ( get_the_content() ) : ?>
						<div class="prose"><?php the_content(); ?></div>
					<?php endif; ?>
				</div>
				<?php get_template_part( 'template-parts/feature-hero-media' ); ?>
			</div>
		</header>

		<div class="container section layout-grid contact-content">
			<?php if ( $wp_starter_contact_form_id ) : ?>
				<section class="card layout-stack starter-contact-form">
					<h2 class="type-subheading"><?php esc_html_e( 'Send an enquiry', 'wp-starter' ); ?></h2>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CF7 renders the form markup for a validated numeric ID.
					echo do_shortcode( sprintf( '[contact-form-7 id="%d"]', $wp_starter_contact_form_id ) );
					?>
				</section>
			<?php elseif ( ! $wp_starter_contact_phone && ! $wp_starter_contact_email && ! $wp_starter_contact_address && ( '' !== $wp_starter_contact_intro || ! get_the_content() ) ) : ?>
				<p><?php esc_html_e( 'The enquiry form is currently unavailable.', 'wp-starter' ); ?></p>
			<?php endif; ?>

			<?php if ( $wp_starter_contact_phone || $wp_starter_contact_email || $wp_starter_contact_address ) : ?>
				<section class="card layout-stack contact-details">
					<h2 class="type-subheading"><?php esc_html_e( 'Contact details', 'wp-starter' ); ?></h2>
					<?php if ( $wp_starter_contact_phone ) : ?>
						<p>
							<?php if ( $wp_starter_contact_tel && preg_match( '/^\+?[0-9]+$/D', $wp_starter_contact_tel ) ) : ?>
								<a href="<?php echo esc_url( 'tel:' . $wp_starter_contact_tel ); ?>"><?php echo esc_html( $wp_starter_contact_phone ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $wp_starter_contact_phone ); ?>
							<?php endif; ?>
						</p>
					<?php endif; ?>
					<?php if ( $wp_starter_contact_email ) : ?>
						<p><a href="<?php echo esc_url( 'mailto:' . $wp_starter_contact_email ); ?>"><?php echo esc_html( $wp_starter_contact_email ); ?></a></p>
					<?php endif; ?>
					<?php if ( $wp_starter_contact_address ) : ?>
						<p><?php echo nl2br( esc_html( $wp_starter_contact_address ) ); ?></p>
					<?php endif; ?>
				</section>
			<?php endif; ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
