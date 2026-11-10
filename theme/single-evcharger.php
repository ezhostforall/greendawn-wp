<?php
/**
 * Fixed product detail using native media and Core-owned imported specification data.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	if ( post_password_required() ) {
		?>
		<article class="container section"><h1><?php the_title(); ?></h1><?php echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress password form. ?></article>
		<?php
		continue;
	}
	$greendawn_charger = apply_filters( 'greendawn_charger_data', array(), get_the_ID() );
	$greendawn_quote   = add_query_arg( 'charger', get_post_field( 'post_name', get_the_ID() ), home_url( '/enquire/' ) );
	$greendawn_media   = wp_get_attachment_image(
		get_post_thumbnail_id(),
		'large',
		false,
		array(
			'alt'           => get_the_title(),
			'loading'       => 'eager',
			'fetchpriority' => 'high',
			'sizes'         => '(max-width: 61rem) calc(100vw - 2rem), (max-width: 95rem) 45vw, 40rem',
		)
	);
	?>
	<article <?php post_class( 'greendawn-page gd-charger' ); ?>>
		<header class="gd-charger-hero">
			<div class="gd-shell">
				<?php greendawn_breadcrumbs(); ?>
				<div class="gd-charger-hero__inner<?php echo $greendawn_media ? '' : ' gd-charger-hero__inner--text'; ?>">
					<div class="gd-charger-hero__copy">
						<h1><?php the_title(); ?></h1>
						<?php if ( ! empty( $greendawn_charger['brand'] ) || ! empty( $greendawn_charger['power'] ) ) : ?>
							<ul class="gd-charger-summary" aria-label="<?php esc_attr_e( 'Charger overview', 'greendawn' ); ?>">
								<?php foreach ( array( 'brand', 'power' ) as $greendawn_key ) : ?>
									<?php
									if ( ! empty( $greendawn_charger[ $greendawn_key ] ) ) :
										?>
										<li><?php echo esc_html( $greendawn_charger[ $greendawn_key ] ); ?></li><?php endif; ?>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<a class="gd-home-button" href="<?php echo esc_url( $greendawn_quote ); ?>" data-lead-capture-open data-lead-entry-point="charger-quote"><?php esc_html_e( 'Request Quote', 'greendawn' ); ?><?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?></a>
					</div>
					<?php
					if ( $greendawn_media ) :
						?>
						<figure class="gd-charger-media"><?php echo $greendawn_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress attachment markup. ?></figure><?php endif; ?>
				</div>
			</div>
		</header>
		<?php if ( ! empty( $greendawn_charger['description'] ) ) : ?>
			<section class="gd-section gd-charger-description" aria-labelledby="charger-description-title">
				<div class="gd-shell gd-charger-description__inner">
					<h2 id="charger-description-title"><?php esc_html_e( 'About this charger', 'greendawn' ); ?></h2>
					<div class="gd-reading-copy"><?php echo wpautop( esc_html( $greendawn_charger['description'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain text escaped before paragraph formatting. ?></div>
				</div>
			</section>
		<?php endif; ?>
		<?php if ( ! empty( $greendawn_charger['specifications'] ) ) : ?>
			<section class="gd-section gd-charger-specs" aria-labelledby="charger-specs-title">
				<div class="gd-shell">
					<h2 id="charger-specs-title"><?php esc_html_e( 'EV Charger Specification', 'greendawn' ); ?></h2>
					<dl class="gd-charger-spec-grid">
						<?php foreach ( $greendawn_charger['specifications'] as $greendawn_spec ) : ?>
							<div data-spec="<?php echo esc_attr( $greendawn_spec['name'] ); ?>"><dt><?php echo esc_html( $greendawn_spec['label'] ); ?></dt><dd><?php echo esc_html( $greendawn_spec['value'] ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
					<a class="gd-home-button gd-page-button--navy" href="<?php echo esc_url( $greendawn_quote ); ?>" data-lead-capture-open data-lead-entry-point="charger-quote"><?php esc_html_e( 'Request Quote', 'greendawn' ); ?><?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?></a>
				</div>
			</section>
		<?php endif; ?>
	</article>
	<?php
endwhile;
get_footer();
