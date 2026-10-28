<?php if ( get_row_layout() == 'benefits' ) : ?>

	<!-- Benefits -->
	<?php if ( have_rows( 'content' ) ) : ?>
		<div class="row module-benefits module-content benefits-list g-3">
			<?php while ( have_rows( 'content' ) ) : the_row(); ?>
				<?php if ( have_rows( 'benefits' ) ) : ?>
					<?php while ( have_rows( 'benefits' ) ) : the_row(); ?>
						<?php
						$benefit_title  = (string) get_sub_field( 'titel' );
						$benefit_text   = (string) get_sub_field( 'text' );
						$icon_options   = (string) get_sub_field( 'icon_options' );
						$benefit_icon   = get_sub_field( 'icon' );
						$benefit_icon_url = '';

						if ( '' === $icon_options ) {
							$icon_options = 'standard';
						}

						if ( 'standard' === $icon_options ) {
							$benefit_icon_url = get_template_directory_uri() . '/assets/svg/24/24-check.svg';
						} elseif ( 'upload' === $icon_options ) {
							if ( is_array( $benefit_icon ) && ! empty( $benefit_icon['url'] ) ) {
								$benefit_icon_url = (string) $benefit_icon['url'];
							} elseif ( is_numeric( $benefit_icon ) ) {
								$benefit_icon_url = (string) wp_get_attachment_image_url( (int) $benefit_icon, 'full' );
							} elseif ( is_string( $benefit_icon ) ) {
								$benefit_icon_url = trim( $benefit_icon );
							}
						}

						if ( '' === trim( $benefit_title ) && '' === trim( wp_strip_all_tags( $benefit_text ) ) ) {
							continue;
						}
						?>
						<div class="col-12 col-md-6 col-xxl-4 benefits-item">
							<div class="box box--s">
								<?php if ( '' !== $benefit_icon_url ) : ?>
									<div class="benefits-box-header">
										<img src="<?php echo esc_url( $benefit_icon_url ); ?>" alt="" class="benefits-icon" loading="lazy" decoding="async" />
									</div>
								<?php endif; ?>

								<div class="benefits-box-content">
									<?php if ( '' !== trim( $benefit_title ) ) : ?>
										<div class="h3 benefits-title">
											<?php echo esc_html( $benefit_title ); ?>
										</div>
									<?php endif; ?>

									<?php if ( '' !== trim( wp_strip_all_tags( $benefit_text ) ) ) : ?>
										<div class="benefits-text txt">
											<?php echo wp_kses_post( $benefit_text ); ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endwhile; ?>
				<?php endif; ?>
			<?php endwhile; ?>
		</div>
	<?php endif; ?>

<?php endif; ?>
