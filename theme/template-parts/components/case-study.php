<?php
/**
 * Project story with facts and an ordered set of photographs.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_title  = (string) ( $args['title'] ?? '' );
$greendawn_facts  = preg_split( '/\R/u', (string) ( $args['facts'] ?? '' ), -1, PREG_SPLIT_NO_EMPTY );
$greendawn_images = $args['images'] ?? array();
$greendawn_status = (string) ( $args['status'] ?? '' );
$greendawn_id     = wp_unique_id( 'gd-case-study-' );
?>
<section class="gd-section gd-case-study" aria-labelledby="<?php echo esc_attr( $greendawn_id ); ?>">
	<div class="gd-shell gd-case-study__layout<?php echo $greendawn_images ? '' : ' gd-case-study__layout--text'; ?>">
		<div class="gd-case-study__story">
			<h2 id="<?php echo esc_attr( $greendawn_id ); ?>"><?php echo esc_html( $greendawn_title ); ?></h2>
			<?php if ( $greendawn_facts ) : ?>
				<ul class="gd-case-study__facts" role="list">
					<?php foreach ( $greendawn_facts as $greendawn_fact ) : ?>
						<li><?php echo esc_html( trim( $greendawn_fact ) ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<div class="gd-case-study__copy">
				<?php echo greendawn_page_prose( (string) ( $args['body'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The shared prose helper applies a restricted HTML allowlist. ?>
			</div>
			<?php if ( '' !== $greendawn_status ) : ?>
				<p class="gd-case-study__status"><?php echo esc_html( $greendawn_status ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $greendawn_images ) : ?>
			<?php
			get_template_part(
				'template-parts/components/photo-grid',
				null,
				array(
					'project' => $greendawn_title,
					'images'  => $greendawn_images,
				)
			);
			?>
		<?php endif; ?>
	</div>
</section>
