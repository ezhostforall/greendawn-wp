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
<header class="gd-page-hero<?php echo $greendawn_media ? '' : ' gd-page-hero--text'; ?>">
	<div class="gd-shell gd-page-hero__inner">
		<div class="gd-page-hero__copy">
			<?php greendawn_breadcrumbs(); ?>
			<h1><span><?php echo esc_html( $greendawn_heading ); ?></span>
			<?php
			if ( '' !== $greendawn_accent ) :
				?>
				<em><?php echo esc_html( $greendawn_accent ); ?></em><?php endif; ?></h1>
			<?php
			if ( '' !== $greendawn_intro ) :
				?>
				<p class="gd-page-lead"><?php echo esc_html( $greendawn_intro ); ?></p><?php endif; ?>
		</div>
		<?php if ( $greendawn_media ) : ?>
			<figure class="gd-page-hero__media"><?php echo $greendawn_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped attachment markup from WordPress. ?></figure>
		<?php endif; ?>
	</div>
</header>
