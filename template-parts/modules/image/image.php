<?php if ( get_row_layout() == 'image' ) : ?>

	<?php
	$image = get_sub_field( 'image' );
	$size  = 'full';
	if ( $image ) :
		$image_id      = is_array( $image ) ? (int) ( $image['ID'] ?? 0 ) : (int) $image;
		$image_caption = $image_id ? wp_get_attachment_caption( $image_id ) : '';
		// Media library "Description" is stored as attachment post_content.
		$image_description_raw = $image_id ? (string) get_post_field( 'post_content', $image_id ) : '';
		$has_image_description = trim( wp_strip_all_tags( $image_description_raw ) ) !== '';
		if ( $image_id ) :
			?>
			<div class="row content-row stack-row stack-row--m">
				<div class="col-12 content-image">
					<div class="image-container">
						<?php echo wp_get_attachment_image( $image_id, $size ); ?>
					</div>
					<?php if ( $image_caption ) : ?>
						<p class="image-caption"><?php echo esc_html( $image_caption ); ?></p>
					<?php endif; ?>

					<?php if ( $has_image_description ) : ?>
						<details class="content-image-details">
							<summary class="content-image-details__summary">
								<?php esc_html_e( 'Bildbeschreibung', 'starterkit' ); ?>
							</summary>
							<div class="content-image-details__body txt lcnmb">
								<?php echo wp_kses_post( $image_description_raw ); ?>
							</div>
						</details>
					<?php endif; ?>
				</div>
			</div>
			<?php
		endif;
	endif;
	?>

<?php endif; ?>
