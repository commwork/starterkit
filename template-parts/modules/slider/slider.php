<?php if ( get_row_layout() == 'slider' ) : ?>

<!-- Slider -->
<?php
/**
 * ACF: layout `slider` → sub field `content` is a group with `slider_options` (title/caption toggles), `images` (gallery, IDs), optional `more_content` clone.
 * `have_rows( 'content' )` runs once for that group (establishes sub field context for get_sub_field).
 */
$slide_size = 'img-large';
?>

<div class="col-12">
    <div class="module-slider">
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

                // ACF group `slider_options` → checkbox `image_meta_show_hide` (values: slider_title, slider_caption).
                $slider_options_group = get_sub_field( 'slider_options' );
                $bildinfo_checked      = null;
                if ( is_array( $slider_options_group ) && array_key_exists( 'image_meta_show_hide', $slider_options_group ) ) {
                    $raw = $slider_options_group['image_meta_show_hide'];
                    $bildinfo_checked = is_array( $raw ) ? $raw : array();
                }
                // No saved options yet: keep previous behaviour (show title + caption when set).
                if ( null === $bildinfo_checked ) {
                    $show_slider_title   = true;
                    $show_slider_caption = true;
                } else {
                    $show_slider_title   = in_array( 'slider_title', $bildinfo_checked, true );
                    $show_slider_caption = in_array( 'slider_caption', $bildinfo_checked, true );
                }
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
                                    $display_title   = $show_slider_title && $title !== '';
                                    $display_caption = $show_slider_caption && $caption !== '';
                                    $img_attrs = array(
                                        'class'    => 'slider-slide-img',
                                        'loading'  => 0 === $slide_index ? 'eager' : 'lazy',
                                        'sizes'    => '(min-width: 1200px) 1200px, (min-width: 768px) 960px, 100vw',
                                        'decoding' => 'async',
                                        'alt'      => $alt,
                                    );
                                    $slider_caption_id = 'slider-caption-' . (int) $img_id . '-' . (int) $slide_index;
                                    $slider_collapsible_caption = $display_title && $display_caption;
                                    ?>
                                    <figure class="swiper-slide">
                                        <?php echo wp_get_attachment_image( $img_id, $slide_size, false, $img_attrs ); ?>
                                        <?php if ( $display_title || $display_caption ) : ?>
                                            <div class="slider-img-meta<?php echo $slider_collapsible_caption ? ' slider-img-meta--collapsible-caption' : ''; ?>">
                                                <div class="slider-img-meta-scroll">
                                                    <?php if ( $display_title ) : ?>
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
                                                                    aria-label="<?php echo esc_attr( __( 'Mehr Informationen zur Bildunterschrift anzeigen', 'awo_pflegeteam_hamburg' ) ); ?>"
                                                                    data-slider-caption-label-closed="<?php echo esc_attr( __( 'Mehr Info', 'awo_pflegeteam_hamburg' ) ); ?>"
                                                                    data-slider-caption-label-open="<?php echo esc_attr( __( 'Weniger Info', 'awo_pflegeteam_hamburg' ) ); ?>"
                                                                    data-slider-aria-label-closed="<?php echo esc_attr( __( 'Mehr Informationen zur Bildunterschrift anzeigen', 'awo_pflegeteam_hamburg' ) ); ?>"
                                                                    data-slider-aria-label-open="<?php echo esc_attr( __( 'Weniger Informationen zur Bildunterschrift anzeigen', 'awo_pflegeteam_hamburg' ) ); ?>"
                                                                >
                                                                    <span class="slider-meta-caption-toggle-label" aria-hidden="true"><?php esc_html_e( 'Mehr Info', 'awo_pflegeteam_hamburg' ); ?></span>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ( $display_caption ) : ?>
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
                                <div class="swiper-button-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'awo_pflegeteam_hamburg' ); ?>"></div>
                                <div class="swiper-button-next" aria-label="<?php esc_attr_e( 'Next slide', 'awo_pflegeteam_hamburg' ); ?>"></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>
