<?php
/**
 * Fixed audience rows and their decorative service photograph previews.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_solutions = array();
$greendawn_active    = '';
foreach ( array( 'fleet', 'workplace', 'public', 'multi_site', 'dealership' ) as $greendawn_index => $greendawn_key ) {
	$greendawn_image_id   = absint( greendawn_home_field( 'solution_' . $greendawn_key . '_image' ) );
	$greendawn_image_html = $greendawn_image_id ? wp_get_attachment_image(
		$greendawn_image_id,
		'large',
		false,
		array(
			'alt'     => '',
			'loading' => 'lazy',
			'sizes'   => '(max-width: 61rem) calc(100vw - 2rem), 45vw',
		)
	) : '';
	if ( '' === $greendawn_active && '' !== $greendawn_image_html ) {
		$greendawn_active = $greendawn_key;
	}
	$greendawn_solutions[] = array(
		'key'        => $greendawn_key,
		'number'     => sprintf( '%02d', $greendawn_index + 1 ),
		'title'      => greendawn_home_field( 'solution_' . $greendawn_key . '_title' ),
		'copy'       => greendawn_home_field( 'solution_' . $greendawn_key . '_copy' ),
		'link_label' => greendawn_home_field( 'solution_' . $greendawn_key . '_link' ),
		'url'        => esc_url( greendawn_home_field( 'solution_' . $greendawn_key . '_url' ) ),
		'image'      => $greendawn_image_html,
	);
}
$greendawn_solutions_class = 'gd-solutions__layout' . ( '' === $greendawn_active ? ' gd-solutions__layout--text' : '' );
?>
<section id="who-we-help" class="gd-journey-section gd-solutions" aria-labelledby="gd-solutions-title">
	<div class="gd-home-shell">
		<div class="gd-solutions__intro">
			<div>
				<p class="gd-eyebrow gd-journey-eyebrow"><?php echo esc_html( greendawn_home_field( 'solutions_eyebrow' ) ); ?></p>
				<h2 id="gd-solutions-title"><?php echo esc_html( greendawn_home_field( 'solutions_title' ) ); ?></h2>
			</div>
			<p><?php echo esc_html( greendawn_home_field( 'solutions_intro' ) ); ?></p>
		</div>
		<div class="<?php echo esc_attr( $greendawn_solutions_class ); ?>">
			<?php if ( '' !== $greendawn_active ) : ?>
				<div class="gd-solutions__media" aria-hidden="true">
					<?php foreach ( $greendawn_solutions as $greendawn_solution ) : ?>
						<?php
						if ( '' === $greendawn_solution['image'] ) {
							continue;
						}
						?>
						<figure class="gd-solution-image gd-solution-image--<?php echo esc_attr( $greendawn_solution['key'] ); ?><?php echo $greendawn_active === $greendawn_solution['key'] ? ' gd-solution-image--default' : ''; ?>">
							<?php echo $greendawn_solution['image']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generates escaped attachment markup. ?>
							<figcaption><span><?php echo esc_html( $greendawn_solution['number'] ); ?></span><?php echo esc_html( $greendawn_solution['title'] ); ?></figcaption>
						</figure>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<ol class="gd-solutions__list" role="list">
				<?php foreach ( $greendawn_solutions as $greendawn_solution ) : ?>
					<?php
					$greendawn_row_class  = 'gd-solution-row gd-solution-row--' . $greendawn_solution['key'];
					$greendawn_row_class .= $greendawn_active === $greendawn_solution['key'] ? ' gd-solution-row--default' : '';
					?>
					<li>
						<?php if ( '' !== $greendawn_solution['url'] ) : ?>
							<a class="<?php echo esc_attr( $greendawn_row_class ); ?>" href="<?php echo esc_url( $greendawn_solution['url'] ); ?>">
						<?php else : ?>
							<div class="<?php echo esc_attr( $greendawn_row_class ); ?>">
						<?php endif; ?>
							<span class="gd-solution-row__number" aria-hidden="true"><?php echo esc_html( $greendawn_solution['number'] ); ?></span>
							<div class="gd-solution-row__body">
								<h3><?php echo esc_html( $greendawn_solution['title'] ); ?></h3>
								<p><?php echo esc_html( $greendawn_solution['copy'] ); ?></p>
								<?php if ( '' !== $greendawn_solution['url'] ) : ?>
									<span class="gd-solution-row__destination"><?php echo esc_html( $greendawn_solution['link_label'] ); ?></span>
								<?php endif; ?>
							</div>
							<?php if ( '' !== $greendawn_solution['url'] ) : ?>
								<span class="gd-solution-row__arrow"><?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?></span>
							<?php endif; ?>
						<?php if ( '' !== $greendawn_solution['url'] ) : ?>
							</a>
						<?php else : ?>
							</div>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>
