<?php if ( get_row_layout() == 'text_image_teaser_2_col' ) : ?>

	<?php if ( have_rows( 'content' ) ) : ?>
		<?php while ( have_rows( 'content' ) ) : the_row(); ?>
			<?php if ( have_rows( 'text_image_teaser_2_col_teaser' ) ) : ?>
				<div class="row module-text-image-teaser-2-col module-content stack-row stack-row--m">
					<?php
					while ( have_rows( 'text_image_teaser_2_col_teaser' ) ) :
						the_row();

						$title     = '';
						$icon_url  = '';
						$icon_alt  = '';
						$text_html = '';
						$links     = array();

						if ( have_rows( 'text_group' ) ) {
							while ( have_rows( 'text_group' ) ) {
								the_row();
								$title = trim( (string) get_sub_field( 'text_group_title' ) );

								$text_group_text_icon = get_sub_field( 'text_group_text_icon' );
								if ( is_array( $text_group_text_icon ) && ! empty( $text_group_text_icon['url'] ) ) {
									$icon_url = (string) $text_group_text_icon['url'];
									$icon_alt = (string) ( $text_group_text_icon['alt'] ?? '' );
								}

								if ( have_rows( 'text_group_text_links' ) ) {
									while ( have_rows( 'text_group_text_links' ) ) {
										the_row();

										if ( get_row_layout() === 'text' ) {
											$text_html .= (string) get_sub_field( 'editor' );
										} elseif ( get_row_layout() === 'links' && have_rows( 'links_repeater' ) ) {
											while ( have_rows( 'links_repeater' ) ) {
												the_row();
												$link = get_sub_field( 'link' );
												if ( is_array( $link ) && ! empty( $link['url'] ) ) {
													$links[] = $link;
												}
											}
										}
									}
								}
							}
						}

						$image_id = 0;
						if ( have_rows( 'image_group' ) ) {
							while ( have_rows( 'image_group' ) ) {
								the_row();
								$image = get_sub_field( 'image' );
								if ( ! $image ) {
									continue;
								}

								$image_id = is_array( $image ) ? (int) ( $image['ID'] ?? 0 ) : (int) $image;
								if ( $image_id ) {
									break;
								}
							}
						}

						$has_text_content = '' !== $icon_url || '' !== $title || '' !== trim( wp_strip_all_tags( $text_html ) ) || ! empty( $links );

						if ( ! $has_text_content && ! $image_id ) {
							continue;
						}

						$text_col_class  = $image_id ? 'col-12 col-lg-6' : 'col-12';
						$image_col_class = $has_text_content ? 'col-12 col-lg-6' : 'col-12';
						?>
						<div class="col-12 text-image-teaser-2-col-item">
							<div class="row g-3 content-row">
								<?php if ( $has_text_content ) : ?>
									<div class="<?php echo esc_attr( $text_col_class ); ?> text-image-teaser-2-col-item__text">
										<div class="text-image-teaser-2-col-item__inner box box--s">
											<?php if ( '' !== $icon_url || '' !== $title || '' !== trim( wp_strip_all_tags( $text_html ) ) ) : ?>
												<div class="text-image-teaser-2-col-item__content">
													<?php if ( '' !== $icon_url ) : ?>
														<div class="text-image-teaser-2-col-item__icon">
															<img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $icon_alt ); ?>" loading="lazy" decoding="async" />
														</div>
													<?php endif; ?>

													<?php if ( '' !== $title ) : ?>
														<div class="h3 text-image-teaser-2-col-item__title">
															<?php echo esc_html( $title ); ?>
														</div>
													<?php endif; ?>

													<?php if ( '' !== trim( wp_strip_all_tags( $text_html ) ) ) : ?>
														<div class="text-image-teaser-2-col-item__body txt lcnmb">
															<?php echo wp_kses_post( $text_html ); ?>
														</div>
													<?php endif; ?>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $links ) ) : ?>
												<div class="text-image-teaser-2-col-item__links stack stack--s">
													<?php foreach ( $links as $link ) : ?>
														<a href="<?php echo esc_url( $link['url'] ); ?>" class="btn primary arrow-right" target="<?php echo esc_attr( $link['target'] ?? '_self' ); ?>"><?php echo esc_html( $link['title'] ?? '' ); ?></a>
													<?php endforeach; ?>
												</div>
											<?php endif; ?>
										</div>
									</div>
								<?php endif; ?>

								<?php if ( $image_id ) : ?>
									<div class="<?php echo esc_attr( $image_col_class ); ?> text-image-teaser-2-col-item__image">
										<div class="text-image-teaser-2-col-item__image-container">
											<?php echo wp_get_attachment_image( $image_id, 'full' ); ?>
										</div>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endwhile; ?>
				</div>
			<?php endif; ?>
		<?php endwhile; ?>
	<?php endif; ?>

<?php endif; ?>
