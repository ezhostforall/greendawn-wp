<?php
/**
 * Template Name: Greendawn Regional Service
 * Template Post Type: page
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'greendawn-page greendawn-region' ); ?>>
		<?php
		if ( post_password_required() ) {
			get_template_part( 'template-parts/components/page-hero', null, array( 'heading' => get_the_title() ) );
			echo '<div class="gd-shell gd-section">' . get_the_password_form() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native WordPress password form.
		} else {
			$greendawn_data = greendawn_reading_page_data( get_the_ID() );
			get_template_part(
				'template-parts/components/page-hero',
				null,
				array(
					'heading'  => $greendawn_data['heading'] ?? get_the_title(),
					'intro'    => $greendawn_data['intro'] ?? '',
					'image_id' => $greendawn_data['image'] ?? 0,
					'kicker'   => $greendawn_data['name'] ?? '',
					'cta'      => array(
						'action'      => 'enquiry',
						'label'       => $greendawn_data['cta_label'] ?? '',
						'entry_point' => 'hero',
					),
				)
			);
			get_template_part(
				'template-parts/components/feature-grid',
				null,
				array(
					'heading' => __( 'Services', 'greendawn' ),
					'variant' => 'icons',
					'items'   => $greendawn_data['services'] ?? array(),
				)
			);
			$greendawn_considerations = array_map(
				static fn( $row ) => array(
					'heading' => $row['heading'],
					'body'    => $row['copy'],
				),
				$greendawn_data['considerations'] ?? array()
			);
			if ( $greendawn_considerations ) :
				?>
				<section class="gd-section gd-region-considerations" aria-labelledby="region-considerations"><div class="gd-shell gd-region-split">
					<h2 id="region-considerations"><?php esc_html_e( 'Local considerations', 'greendawn' ); ?></h2>
					<div>
					<?php
					get_template_part(
						'template-parts/components/reading-sections',
						null,
						array(
							'sections'      => $greendawn_considerations,
							'prefix'        => 'region',
							'heading_level' => 3,
						)
					);
					?>
							</div>
				</div></section>
				<?php
			endif;
			if ( ! empty( $greendawn_data['places'] ) ) :
				?>
				<section class="gd-shell gd-section gd-region-split" aria-labelledby="region-coverage"><h2 id="region-coverage"><?php esc_html_e( 'Places we serve', 'greendawn' ); ?></h2><div><?php get_template_part( 'template-parts/components/coverage-list', null, array( 'items' => $greendawn_data['places'] ) ); ?></div></section>
				<?php
			endif;
			if ( ! empty( $greendawn_data['proof_heading'] ) && ! empty( $greendawn_data['proof_copy'] ) && ! empty( $greendawn_data['proof_url'] ) ) {
				get_template_part(
					'template-parts/components/media-copy',
					null,
					array(
						'tone'     => 'dark',
						'heading'  => $greendawn_data['proof_heading'],
						'copy'     => $greendawn_data['proof_copy'],
						'image_id' => $greendawn_data['proof_image'],
						'link'     => array(
							'url'   => $greendawn_data['proof_url'],
							'label' => __( 'View published project evidence', 'greendawn' ),
						),
					)
				);
			}
			get_template_part(
				'template-parts/components/faq',
				null,
				array(
					'heading' => __( 'Useful answers before you start', 'greendawn' ),
					'items'   => $greendawn_data['faqs'] ?? array(),
				)
			);
			greendawn_reading_related( $greendawn_data['guides'] ?? array() );
			greendawn_reading_cta( $greendawn_data );
		}
		?>
	</article>
	<?php
endwhile;
get_footer();
