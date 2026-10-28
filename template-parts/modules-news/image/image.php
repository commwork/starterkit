<?php
/**
 * News single module: `image` (ACF `modules_news` row).
 * Isolated from page `modules_content` templates.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

if ( get_row_layout() !== 'image' ) {
	return;
}

$image = get_sub_field( 'image' );
$size  = 'full';
if ( ! $image ) {
	return;
}

$image_id      = is_array( $image ) ? (int) ( $image['ID'] ?? 0 ) : (int) $image;
$image_caption = $image_id ? wp_get_attachment_caption( $image_id ) : '';
// Media library "Description" is stored as attachment post_content.
$image_description_raw = $image_id ? (string) get_post_field( 'post_content', $image_id ) : '';
$has_image_description = trim( wp_strip_all_tags( $image_description_raw ) ) !== '';

if ( ! $image_id ) {
	return;
}
?>
<div class="row single-news-image single-news-module-content stack-row stack-row--m">
	<div class="col-12 single-news-image__col">
		<div class="single-news-image__container">
			<?php echo wp_get_attachment_image( $image_id, $size ); ?>
		</div>
		<?php if ( $image_caption ) : ?>
			<p class="single-news-image__caption">
				<?php echo esc_html( $image_caption ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $has_image_description ) : ?>
			<details class="single-news-image__details">
				<summary class="single-news-image__summary">
					<?php esc_html_e( 'Detailierte Bildbeschreibung', 'starterkit' ); ?>
				</summary>
				<div class="single-news-image__description txt lcnmb">
					<?php echo wp_kses_post( $image_description_raw ); ?>
				</div>
			</details>
		<?php endif; ?>
	</div>
</div>
