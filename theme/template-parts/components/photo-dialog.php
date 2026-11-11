<?php
/**
 * Shared native dialog for Gallery photo links.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
?>
<dialog class="gd-photo-dialog" data-gd-photo-dialog aria-label="<?php esc_attr_e( 'Project photograph viewer', 'greendawn' ); ?>">
	<div class="gd-photo-dialog__toolbar">
		<p class="gd-photo-dialog__position" data-gd-photo-position role="status"></p>
		<form method="dialog">
			<button type="submit" autofocus><?php esc_html_e( 'Close', 'greendawn' ); ?></button>
		</form>
	</div>
	<figure>
		<img alt="" hidden>
		<figcaption hidden></figcaption>
	</figure>
	<p data-gd-photo-error hidden><?php esc_html_e( 'This photograph could not be loaded.', 'greendawn' ); ?></p>
	<div class="gd-photo-dialog__actions">
		<button type="button" data-gd-photo-prev><?php esc_html_e( 'Previous', 'greendawn' ); ?></button>
		<a data-gd-photo-original><?php esc_html_e( 'Open image file', 'greendawn' ); ?></a>
		<button type="button" data-gd-photo-next><?php esc_html_e( 'Next', 'greendawn' ); ?></button>
	</div>
</dialog>
