<?php
/**
 * Constrained reading sections, lists, tables and local images.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_sections = greendawn_reading_sections( is_array( $args['sections'] ?? null ) ? $args['sections'] : array(), $args['prefix'] ?? 'guide' );
$greendawn_subheads = 3 === ( $args['heading_level'] ?? 2 );
foreach ( $greendawn_sections as $greendawn_section ) :
	$greendawn_id = $greendawn_section['id'];
	?>
	<section class="gd-reading-section" id="<?php echo esc_attr( $greendawn_id ); ?>" aria-labelledby="<?php echo esc_attr( $greendawn_id ); ?>-title">
		<?php if ( $greendawn_subheads ) : ?>
			<h3 id="<?php echo esc_attr( $greendawn_id ); ?>-title"><?php echo esc_html( $greendawn_section['heading'] ); ?></h3>
		<?php else : ?>
			<h2 id="<?php echo esc_attr( $greendawn_id ); ?>-title"><?php echo esc_html( $greendawn_section['heading'] ); ?></h2>
		<?php endif; ?>
		<?php echo greendawn_page_prose( $greendawn_section['body'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Restricted prose allowlist. ?>
		<?php if ( $greendawn_section['list_items'] ) : ?>
			<?php $greendawn_numbered = 'numbered' === $greendawn_section['list_style']; ?>
			<?php echo $greendawn_numbered ? '<ol class="gd-reading-list">' : '<ul class="gd-reading-list' . ( 'checklist' === $greendawn_section['list_style'] ? ' gd-reading-list--checklist' : '' ) . '">'; ?>
				<?php foreach ( $greendawn_section['list_items'] as $greendawn_item ) : ?>
					<li>
					<?php
					if ( 'checklist' === $greendawn_section['list_style'] ) {
						echo greendawn_component_icon( 'check' ); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed decorative path.
					?>
					<?php echo esc_html( $greendawn_item['text'] ); ?></li>
				<?php endforeach; ?>
			<?php echo $greendawn_numbered ? '</ol>' : '</ul>'; ?>
		<?php endif; ?>
		<?php if ( $greendawn_section['columns'] && $greendawn_section['table_rows'] ) : ?>
			<div class="gd-reading-table-scroll" role="region" aria-labelledby="<?php echo esc_attr( $greendawn_id ); ?>-table-caption" tabindex="0">
				<table class="gd-reading-table<?php echo count( $greendawn_section['columns'] ) > 2 ? ' gd-reading-table--wide' : ''; ?>">
					<caption id="<?php echo esc_attr( $greendawn_id ); ?>-table-caption"><?php echo esc_html( $greendawn_section['table_caption'] ); ?></caption>
					<thead><tr>
					<?php
					foreach ( $greendawn_section['columns'] as $greendawn_column ) :
						?>
						<th scope="col"><?php echo esc_html( $greendawn_column['label'] ); ?></th><?php endforeach; ?></tr></thead>
					<tbody>
						<?php foreach ( $greendawn_section['table_rows'] as $greendawn_row ) : ?>
							<tr>
							<?php
							foreach ( $greendawn_row['cells'] as $greendawn_cell ) :
								?>
								<td><?php echo nl2br( esc_html( $greendawn_cell['text'] ) ); ?></td><?php endforeach; ?></tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
		<?php
		$greendawn_image = wp_get_attachment_image(
			$greendawn_section['image'],
			'large',
			false,
			array(
				'loading' => 'lazy',
				'sizes'   => '(max-width: 61rem) calc(100vw - 2rem), 53rem',
			)
		);
		if ( $greendawn_image ) :
			?>
			<figure class="gd-reading-figure"><?php echo $greendawn_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native escaped attachment markup. ?>
				<?php
				if ( '' !== $greendawn_section['caption'] ) :
					?>
					<figcaption><?php echo esc_html( $greendawn_section['caption'] ); ?></figcaption><?php endif; ?>
			</figure>
		<?php endif; ?>
	</section>
<?php endforeach; ?>
