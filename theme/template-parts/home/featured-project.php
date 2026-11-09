<?php
/**
 * Featured dealership programme with Media Library photography.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_project_image = absint( greendawn_home_field( 'featured_image' ) );
$greendawn_project_html  = $greendawn_project_image ? wp_get_attachment_image(
	$greendawn_project_image,
	'large',
	false,
	array(
		'loading' => 'lazy',
		'sizes'   => '(max-width: 61rem) calc(100vw - 2rem), 52vw',
	)
) : '';
$greendawn_project_url   = esc_url( greendawn_home_field( 'projects_url' ) );
$greendawn_project_class = 'gd-home-shell gd-case-study__layout' . ( '' === $greendawn_project_html ? ' gd-case-study__layout--text' : '' );
?>
<section id="projects" class="gd-journey-section gd-case-study" aria-labelledby="gd-project-title">
	<div class="<?php echo esc_attr( $greendawn_project_class ); ?>">
		<?php if ( '' !== $greendawn_project_html ) : ?>
			<figure class="gd-case-study__media">
				<?php echo $greendawn_project_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generates escaped attachment markup. ?>
				<figcaption><?php echo esc_html( greendawn_home_field( 'featured_caption' ) ); ?></figcaption>
			</figure>
		<?php endif; ?>
		<div class="gd-case-study__story">
			<p class="gd-eyebrow gd-journey-eyebrow"><?php echo esc_html( greendawn_home_field( 'featured_eyebrow' ) ); ?></p>
			<h2 id="gd-project-title"><?php echo esc_html( greendawn_home_field( 'featured_title' ) ); ?></h2>
			<p class="gd-case-study__lead"><?php echo esc_html( greendawn_home_field( 'featured_lead' ) ); ?></p>
			<div class="gd-case-study__facts">
				<?php foreach ( array( 'sites', 'chargepoints', 'brands' ) as $greendawn_fact ) : ?>
					<div>
						<strong><?php echo esc_html( greendawn_home_field( 'featured_' . $greendawn_fact . '_value' ) ); ?></strong>
						<span><?php echo esc_html( greendawn_home_field( 'featured_' . $greendawn_fact . '_label' ) ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
			<p class="gd-case-study__note"><?php echo esc_html( greendawn_home_field( 'featured_review_note' ) ); ?></p>
			<p><?php echo esc_html( greendawn_home_field( 'featured_copy' ) ); ?></p>
			<?php if ( '' !== $greendawn_project_url ) : ?>
				<a class="gd-home-text-link gd-home-text-link--dark" href="<?php echo esc_url( $greendawn_project_url ); ?>">
					<?php echo esc_html( greendawn_home_field( 'featured_link' ) ); ?>
					<?php get_template_part( 'template-parts/home/arrow' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
