<?php
/**
 * Rank Math SEO compatibility. Rank Math owns titles, meta, canonical, Open Graph,
 * sitemaps and schema; the theme deliberately outputs none of these itself.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether Rank Math SEO is active.
 */
function wp_starter_is_rank_math_active(): bool {
	return class_exists( 'RankMath' );
}
