<?php
/**
 * Shared campaign presentation and responsive notice hooks.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

use Greendawn_Core\Modules\Campaigns;

/**
 * Resolve the featured and current campaigns once for this page.
 *
 * @return array{featured: array, current: array}
 */
function greendawn_campaign_context(): array {
	static $context;
	if ( null === $context ) {
		$context = class_exists( Campaigns::class )
			? array(
				'featured' => Campaigns::featured(),
				'current'  => Campaigns::current(),
			)
			: array(
				'featured' => array(),
				'current'  => array(),
			);
	}
	return $context;
}

/** Load the campaign presentation only when the page uses it. */
function greendawn_campaign_styles(): void {
	$campaigns = greendawn_campaign_context();
	if ( $campaigns['current'] || ( $campaigns['featured'] && 'closed' !== $campaigns['featured']['phase'] ) ) {
		greendawn_enqueue_entry( 'greendawn-campaign' );
	}
}
add_action( 'wp_enqueue_scripts', 'greendawn_campaign_styles' );

/**
 * Serialises phase boundaries so a cached page can switch at the correct UK time.
 *
 * @param array $campaign Campaign content and dates.
 */
function greendawn_campaign_timeline( array $campaign ): string {
	return (string) wp_json_encode( array_intersect_key( $campaign, array_flip( array( 'startsAt', 'lateAt', 'endsAt' ) ) ) );
}

/**
 * Renders two editable versions of a text node and shows the current phase on the server.
 *
 * @param string $before Early copy.
 * @param string $after  Late copy.
 * @param string $phase  Current phase.
 */
function greendawn_campaign_variant_text( string $before, string $after, string $phase ): void {
	if ( '' === $after || $before === $after ) {
		echo esc_html( $before );
		return;
	}
	?>
	<span data-gd-campaign-variant="live upcoming closed" <?php echo 'late' === $phase ? 'hidden' : ''; ?>><?php echo esc_html( $before ); ?></span>
	<span data-gd-campaign-variant="late" <?php echo 'late' !== $phase ? 'hidden' : ''; ?>><?php echo esc_html( $after ); ?></span>
	<?php
}

/**
 * Resolve the ordinary enquiry link for unavailable drawer sessions.
 *
 * @param array $campaign Campaign content and dates.
 */
function greendawn_campaign_enquiry_fallback( array $campaign ): string {
	$url = esc_url_raw( (string) get_theme_mod( 'greendawn_header_cta_url', '' ) );
	// The campaign fallback remains a normal enquiry link if the drawer cannot start.
	return $url && '#' !== $url && url_to_postid( $url ) !== $campaign['id'] ? $url : home_url( '/enquire/' );
}

/**
 * Render the landing-page actions for each campaign phase.
 *
 * @param array $campaign Campaign content and dates.
 */
function greendawn_campaign_actions( array $campaign ): void {
	$fallback = greendawn_campaign_enquiry_fallback( $campaign );
	$labels   = array(
		'live'            => $campaign['cta'],
		'late'            => greendawn_campaign_copy( $campaign['late_cta'], $campaign['cta'] ),
		'closed upcoming' => greendawn_campaign_copy( $campaign['closed_cta'], __( 'Discuss your project', 'greendawn' ) ),
	);
	foreach ( $labels as $phases => $label ) :
		$open = 'closed upcoming' !== $phases;
		?>
		<a class="gd-campaign-button" href="<?php echo esc_url( $fallback ); ?>" data-gd-campaign-variant="<?php echo esc_attr( $phases ); ?>" <?php echo in_array( $campaign['phase'], explode( ' ', $phases ), true ) ? '' : 'hidden'; ?> data-lead-capture-open data-lead-entry-point="campaign-landing"
		<?php
		if ( $open ) :
			?>
			data-lead-campaign="<?php echo esc_attr( (string) $campaign['id'] ); ?>"<?php endif; ?>>
			<?php echo esc_html( greendawn_campaign_copy( $label, __( 'Discuss your project', 'greendawn' ) ) ); ?>
			<?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?>
		</a>
		<?php
	endforeach;
}

/** Render the featured notice above the site layout. */
function greendawn_campaign_bar(): void {
	$featured = greendawn_campaign_context()['featured'];
	if ( $featured && 'closed' !== $featured['phase'] && $featured['bar_title'] ) {
		get_template_part( 'template-parts/campaign/bar', null, $featured );
	}
}
add_action( 'greendawn_before_site', 'greendawn_campaign_bar' );

/** Render the featured pop-up in the shared footer. */
function greendawn_campaign_popup(): void {
	$featured = greendawn_campaign_context()['featured'];
	if ( $featured && 'closed' !== $featured['phase'] && $featured['popup_title'] ) {
		get_template_part( 'template-parts/campaign/popup', null, $featured );
	}
}
add_action( 'wp_footer', 'greendawn_campaign_popup', 4 );

/**
 * Expose the public note and dates used by the enquiry drawer.
 *
 * @param array $campaign Campaign content and dates.
 */
function greendawn_campaign_drawer_data( array $campaign ): array {
	if ( ! $campaign || 'closed' === $campaign['phase'] ) {
		return array();
	}
	return array_intersect_key(
		$campaign,
		array_flip( array( 'id', 'startsAt', 'lateAt', 'endsAt', 'drawer_title', 'drawer_copy', 'late_drawer', 'drawer_cta', 'drawer_context' ) )
	);
}

/**
 * Give incomplete campaign records a usable display label.
 *
 * @param string $copy     Saved campaign copy.
 * @param string $fallback Copy to use if the field is empty.
 */
function greendawn_campaign_copy( string $copy, string $fallback ): string {
	return '' !== $copy ? $copy : $fallback;
}
