<?php
/**
 * Rank Math SEO compatibility. Rank Math owns titles, meta, canonical, Open Graph,
 * sitemaps and schema; the theme deliberately outputs none of these itself.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether Rank Math SEO is active.
 */
function greendawn_is_rank_math_active(): bool {
	return class_exists( 'RankMath' );
}
