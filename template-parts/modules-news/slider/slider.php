<?php
/**
 * News single module: `slider` (ACF `modules_news` row).
 * Markup/classes match page `modules_content` slider for shared CSS/JS.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

if ( get_row_layout() !== 'slider' ) {
	return;
}

$slide_size = 'img-large';
?>
<div class="row single-news-slider single-news-module-content">
	<div class="col-12">
		<?php if ( have_rows( 'content' ) ) : ?>
			<?php
			while ( have_rows( 'content' ) ) :
				the_row();

				$images_ids = get_sub_field( 'images' );
				if ( ! is_array( $images_ids ) ) {
					$images_ids = array();
				}

				$images_ids = array_values(
					array_filter(
						array_map( 'intval', $images_ids ),
						function ( $id ) {
							return $id && wp_attachment_is_image( $id );
						}
					)
				);
				?>
				<?php if ( ! empty( $images_ids ) ) : ?>
					<div class="slider-module">
						<div class="swiper slider-swiper">
							<div class="swiper-wrapper">
								<?php foreach ( $images_ids as $slide_index => $img_id ) : ?>
									<?php
									$alt     = get_post_meta( $img_id, '_wp_attachment_image_alt', true ) ?: '';
									$title   = get_the_title( $img_id ) ?: '';
									$caption = wp_get_attachment_caption( $img_id ) ?: '';
									$img_attrs = array(
										'class'    => 'slider-slide-img',
										'loading'  => 0 === $slide_index ? 'eager' : 'lazy',
										'sizes'    => '(min-width: 1200px) 1200px, (min-width: 768px) 960px, 100vw',
										'decoding' => 'async',
										'alt'      => $alt,
									);
									$slider_caption_id          = 'slider-caption-' . (int) $img_id . '-' . (int) $slide_index;
									$slider_collapsible_caption = $title !== '' && $caption !== '';
									?>
									<figure class="swiper-slide">
										<?php echo wp_get_attachment_image( $img_id, $slide_size, false, $img_attrs ); ?>
										<?php if ( $title !== '' || $caption !== '' ) : ?>
											<div class="slider-img-meta<?php echo $slider_collapsible_caption ? ' slider-img-meta--collapsible-caption' : ''; ?>">
												<div class="slider-img-meta-scroll">
													<?php if ( $title !== '' ) : ?>
														<div class="slider-meta-title-row">
															<div class="slider-meta-title h5">
																<?php echo esc_html( $title ); ?>
															</div>
															<?php if ( $slider_collapsible_caption ) : ?>
																<button
																	type="button"
																	class="slider-meta-caption-toggle"
																	aria-expanded="false"
																	aria-controls="<?php echo esc_attr( $slider_caption_id ); ?>"
																	aria-label="<?php echo esc_attr( __( 'Mehr Informationen zur Bildunterschrift anzeigen', 'starterkit' ) ); ?>"
																	data-slider-caption-label-closed="<?php echo esc_attr( __( 'Mehr Info', 'starterkit' ) ); ?>"
																	data-slider-caption-label-open="<?php echo esc_attr( __( 'Weniger Info', 'starterkit' ) ); ?>"
																	data-slider-aria-label-closed="<?php echo esc_attr( __( 'Mehr Informationen zur Bildunterschrift anzeigen', 'starterkit' ) ); ?>"
																	data-slider-aria-label-open="<?php echo esc_attr( __( 'Weniger Informationen zur Bildunterschrift anzeigen', 'starterkit' ) ); ?>"
																>
																	<span class="slider-meta-caption-toggle-label" aria-hidden="true"><?php esc_html_e( 'Mehr Info', 'starterkit' ); ?></span>
																</button>
															<?php endif; ?>
														</div>
													<?php endif; ?>
													<?php if ( $caption !== '' ) : ?>
														<p id="<?php echo esc_attr( $slider_caption_id ); ?>" class="slider-meta-caption p scrollbar">
															<?php echo esc_html( $caption ); ?>
														</p>
													<?php endif; ?>
												</div>
											</div>
										<?php endif; ?>
									</figure>
								<?php endforeach; ?>
							</div>
						</div>

						<div class="swiper-footer">
							<div class="swiper-pagination"></div>
							<div class="swiper-navigation">
								<div class="swiper-button-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'starterkit' ); ?>"></div>
								<div class="swiper-button-next" aria-label="<?php esc_attr_e( 'Next slide', 'starterkit' ); ?>"></div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			<?php endwhile; ?>
		<?php endif; ?>
	</div>
</div>
