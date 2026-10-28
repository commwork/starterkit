<?php
/**
 * Layout "process": hero block with header clone + process swiper (no modules_content).
 * ACF: layout `process` — `header` (clone), `content` (group/repeater) with repeater `process`
 * (icon, title, text).
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

if ( get_row_layout() !== 'process' ) {
	return;
}
?>

<section class="section section--xl section-process layout-page-section layout-page-section--process"<?php echo starterkit_layout_row_jump_link_id_attr(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="container container-narrow">
		<div class="row g-5">
			<!-- Header -->
			<?php if ( starterkit_layout_row_has_header_content() ) : ?>
				<div class="col-12">
					<?php get_template_part( 'template-parts/layout-parts/layout-row-header' ); ?>
				</div>
			<?php endif; ?>

			<!-- Content -->
			<?php if ( have_rows( 'content' ) ) : ?>
				<div class="col-12<?php echo starterkit_layout_row_has_header_content() ? ' col-lg-8' : ''; ?>">
					<div class="row">
						<div class="col-12 stack stack--m">
							<div class="process-module">
								<div class="swiper process-swiper">
									<div class="swiper-wrapper process-list">
										<?php
										while ( have_rows( 'content' ) ) :
											the_row();
											if ( ! have_rows( 'process' ) ) {
												continue;
											}
											while ( have_rows( 'process' ) ) :
												the_row();
												$process_title = trim( (string) get_sub_field( 'title' ) );
												if ( '' === $process_title ) {
													$process_title = trim( (string) get_sub_field( 'titel' ) );
												}
												$process_text       = (string) get_sub_field( 'text' );
												$process_text_plain = trim( wp_strip_all_tags( $process_text ) );
												$process_icon       = get_sub_field( 'icon' );

												$process_icon_url = '';
												if ( is_array( $process_icon ) ) {
													$process_icon_url = trim( (string) ( $process_icon['url'] ?? '' ) );
												} elseif ( is_numeric( $process_icon ) ) {
													$process_icon_url = (string) wp_get_attachment_image_url( (int) $process_icon, 'full' );
												} elseif ( is_string( $process_icon ) ) {
													$process_icon_url = trim( $process_icon );
												}

												if ( '' === $process_title && '' === $process_text_plain && '' === $process_icon_url ) {
													continue;
												}
												?>
												<div class="swiper-slide process-item">
													<div class="box box--s">
														<?php if ( '' !== $process_icon_url ) : ?>
															<img src="<?php echo esc_url( $process_icon_url ); ?>" alt="" class="process-icon" loading="lazy" decoding="async" />
														<?php else : ?>
															<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/circle-arrow-right.svg' ); ?>" alt="" class="process-icon" loading="lazy" decoding="async" />
														<?php endif; ?>

														<?php if ( '' !== $process_title ) : ?>
															<div class="h4 process-title"><?php echo esc_html( $process_title ); ?></div>
														<?php endif; ?>

														<?php if ( '' !== $process_text_plain ) : ?>
															<div class="process-text txt"><?php echo wp_kses_post( $process_text ); ?></div>
														<?php endif; ?>
													</div>
												</div>
												<?php
											endwhile;
										endwhile;
										?>
									</div>
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
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
