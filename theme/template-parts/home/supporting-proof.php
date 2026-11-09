<?php
/**
 * Supporting fleet programme case study from the homepage reference.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_proof_image = absint( greendawn_home_field( 'proof_image' ) );
$greendawn_proof_html  = $greendawn_proof_image ? wp_get_attachment_image(
	$greendawn_proof_image,
	'large',
	false,
	array(
		'loading' => 'lazy',
		'sizes'   => '(max-width: 42rem) calc(100vw - 1.5rem), (max-width: 61rem) calc(100vw - 2rem), 58vw',
	)
) : '';
$greendawn_proof_url   = esc_url( greendawn_home_field( 'projects_url' ) );
$greendawn_card_class  = 'gd-proof-card' . ( '' === $greendawn_proof_html ? ' gd-proof-card--text' : '' );
?>
<section id="supporting-proof" class="gd-journey-section gd-proof-grid" aria-labelledby="gd-proof-title">
	<div class="gd-home-shell">
		<div class="gd-proof-grid__header">
			<div>
				<p class="gd-eyebrow gd-journey-eyebrow"><?php echo esc_html( greendawn_home_field( 'proof_eyebrow' ) ); ?></p>
				<h2 id="gd-proof-title"><?php echo esc_html( greendawn_home_field( 'proof_title' ) ); ?></h2>
			</div>
			<?php if ( '' !== $greendawn_proof_url ) : ?>
				<a class="gd-home-text-link gd-home-text-link--dark" href="<?php echo esc_url( $greendawn_proof_url ); ?>">
					<?php echo esc_html( greendawn_home_field( 'proof_link' ) ); ?>
					<?php get_template_part( 'template-parts/home/arrow' ); ?>
				</a>
			<?php endif; ?>
		</div>
		<div class="gd-proof-grid__items">
			<article class="<?php echo esc_attr( $greendawn_card_class ); ?>" aria-labelledby="gd-proof-project-title">
				<?php if ( '' !== $greendawn_proof_html ) : ?>
					<figure>
						<?php echo $greendawn_proof_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generates escaped attachment markup. ?>
						<figcaption><?php echo esc_html( greendawn_home_field( 'proof_caption' ) ); ?></figcaption>
					</figure>
				<?php endif; ?>
				<div class="gd-proof-card__body">
					<h3 id="gd-proof-project-title"><?php echo esc_html( greendawn_home_field( 'proof_project_title' ) ); ?></h3>
					<strong><?php echo esc_html( greendawn_home_field( 'proof_result' ) ); ?></strong>
					<p><?php echo esc_html( greendawn_home_field( 'proof_copy' ) ); ?></p>
				</div>
			</article>
		</div>
	</div>
</section>
