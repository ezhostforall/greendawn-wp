<?php
/**
 * Fixed layout for individual Service entries.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$greendawn_service_description = greendawn_get_field( 'service_description', false, '' );
	$greendawn_service_benefits    = array_filter(
		(array) greendawn_get_field( 'service_benefits', false, array() ),
		static fn( $row ) => ! empty( $row['benefit'] )
	);
	$greendawn_service_faqs        = array_filter(
		(array) greendawn_get_field( 'service_faqs', false, array() ),
		static fn( $row ) => ! empty( $row['question'] ) && ! empty( $row['answer'] )
	);
	$greendawn_service_cta_label   = trim( (string) greendawn_get_field( 'service_cta_label', false, '' ) );
	$greendawn_service_cta_url     = sanitize_url( (string) greendawn_get_field( 'service_cta_url', false, '' ) );
	$greendawn_service_has_cta     = '' !== $greendawn_service_cta_label && '' !== $greendawn_service_cta_url && '#' !== $greendawn_service_cta_url;
	$greendawn_service_has_desc    = is_string( $greendawn_service_description ) && '' !== trim( $greendawn_service_description );
	$greendawn_service_has_details = $greendawn_service_has_desc || $greendawn_service_benefits || $greendawn_service_faqs;
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'service-detail' ); ?>>
		<header class="feature-hero">
			<div class="container service-breadcrumbs">
				<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
			</div>
			<div class="container feature-hero__inner">
				<div class="layout-stack feature-hero__copy">
					<?php the_title( '<h1 class="type-display">', '</h1>' ); ?>
					<?php if ( has_excerpt() ) : ?>
						<div class="type-lead"><?php the_excerpt(); ?></div>
					<?php endif; ?>
					<?php if ( $greendawn_service_has_cta ) : ?>
						<div>
							<a class="button" href="<?php echo esc_url( $greendawn_service_cta_url ); ?>"><?php echo esc_html( $greendawn_service_cta_label ); ?></a>
						</div>
					<?php endif; ?>
				</div>
				<?php get_template_part( 'template-parts/feature-hero-media' ); ?>
			</div>
		</header>

		<?php if ( $greendawn_service_has_details ) : ?>
			<div class="container container--narrow section layout-stack service-detail__content">
				<?php if ( $greendawn_service_has_desc ) : ?>
					<div class="prose">
						<?php echo wp_kses_post( $greendawn_service_description ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $greendawn_service_benefits ) : ?>
					<section class="layout-stack">
						<h2><?php esc_html_e( 'Benefits', 'greendawn' ); ?></h2>
						<ul class="service-detail__benefits">
							<?php foreach ( $greendawn_service_benefits as $greendawn_benefit ) : ?>
								<li><?php echo esc_html( $greendawn_benefit['benefit'] ); ?></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<?php if ( $greendawn_service_faqs ) : ?>
					<section class="layout-stack service-detail__faq">
						<h2><?php esc_html_e( 'Frequently asked questions', 'greendawn' ); ?></h2>
						<?php foreach ( $greendawn_service_faqs as $greendawn_faq ) : ?>
							<details>
								<summary><?php echo esc_html( $greendawn_faq['question'] ); ?></summary>
								<p><?php echo nl2br( esc_html( $greendawn_faq['answer'] ) ); ?></p>
							</details>
						<?php endforeach; ?>
					</section>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $greendawn_service_has_cta && $greendawn_service_has_details ) : ?>
			<section class="feature-cta">
				<div class="container section layout-row">
					<h2><?php esc_html_e( 'Interested in this service?', 'greendawn' ); ?></h2>
					<a class="button" href="<?php echo esc_url( $greendawn_service_cta_url ); ?>"><?php echo esc_html( $greendawn_service_cta_label ); ?></a>
				</div>
			</section>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
