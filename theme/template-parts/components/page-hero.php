<?php
/**
 * Shared internal-page hero: explicit content and media, fixed composition.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_heading = (string) ( $args['heading'] ?? '' );
$greendawn_accent  = (string) ( $args['accent'] ?? '' );
$greendawn_intro   = (string) ( $args['intro'] ?? '' );
$greendawn_kicker  = greendawn_component_text( $args['kicker'] ?? '' );
$greendawn_guide   = 'guide' === ( $args['variant'] ?? '' );
$greendawn_cta     = greendawn_component_cta( is_array( $args['cta'] ?? null ) ? $args['cta'] : array(), 'hero' );
$greendawn_media   = wp_get_attachment_image(
	absint( $args['image_id'] ?? 0 ),
	'large',
	false,
	array(
		'loading'       => 'eager',
		'fetchpriority' => 'high',
		'sizes'         => '(max-width: 61rem) calc(100vw - 2rem), (max-width: 95rem) 45vw, 42rem',
	)
);
?>
<header class="gd-page-hero<?php echo $greendawn_media ? '' : ' gd-page-hero--text'; ?><?php echo $greendawn_guide ? ' gd-page-hero--guide' : ''; ?>">
	<div class="gd-shell gd-page-hero__inner">
		<div class="gd-page-hero__copy">
			<?php greendawn_breadcrumbs(); ?>
			<?php
			if ( '' !== $greendawn_kicker ) :
				?>
				<p class="gd-eyebrow gd-page-hero__kicker"><?php echo esc_html( $greendawn_kicker ); ?></p><?php endif; ?>
			<h1><span><?php echo esc_html( $greendawn_heading ); ?></span>
			<?php
			if ( '' !== $greendawn_accent ) :
				?>
				<em><?php echo esc_html( $greendawn_accent ); ?></em><?php endif; ?></h1>
			<?php
			if ( '' !== $greendawn_intro ) :
				?>
				<p class="gd-page-lead"><?php echo esc_html( $greendawn_intro ); ?></p><?php endif; ?>
			<?php
			if ( is_array( $args['metadata'] ?? null ) ) {
				get_template_part( 'template-parts/components/editorial-meta', null, $args['metadata'] ); }
			?>
			<?php if ( '' !== esc_url( $greendawn_cta['url'] ) && '' !== $greendawn_cta['label'] ) : ?>
				<a class="gd-home-button gd-page-hero__cta" href="<?php echo esc_url( $greendawn_cta['url'] ); ?>"<?php echo $greendawn_cta['attributes']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Only allowlisted, escaped action attributes. ?>><?php echo esc_html( $greendawn_cta['label'] ); ?></a>
			<?php endif; ?>
		</div>
		<?php if ( $greendawn_media ) : ?>
			<figure class="gd-page-hero__media<?php echo esc_attr( greendawn_image_position_class( $args['image_position'] ?? '' ) ); ?>"><?php echo $greendawn_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped attachment markup from WordPress. ?></figure>
		<?php endif; ?>
	</div>
</header>
