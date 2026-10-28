<?php if ( get_row_layout() == 'compensation' ) : ?>

	<?php if ( have_rows( 'content' ) ) : ?>
		<div class="row module-content compensation-module">
			<div class="col-12">
				<div class="compensation-module-items">
					<?php while ( have_rows( 'content' ) ) : the_row(); ?>
						<?php if ( have_rows( 'year' ) ) : ?>
							<?php while ( have_rows( 'year' ) ) : the_row(); ?>
								<?php
								$titel  = trim( (string) get_sub_field( 'titel' ) );
								$salary = trim( (string) get_sub_field( 'salary' ) );
								if ( $titel === '' && $salary === '' ) {
									continue;
								}
								?>
								<div class="compensation-module-item">
									<?php if ( $titel !== '' ) : ?>
										<strong class="compensation-module-title">
											<?php echo esc_html( $titel ); ?>
										</strong>
									<?php endif; ?>

									<span class="compensation-module-divider">
										<span class="compensation-module-divider-line"></span>
									</span>

									<?php if ( $salary !== '' ) : ?>
										<p class="compensation-module-salary txt">
											<?php echo esc_html( $salary ); ?>
										</p>
									<?php endif; ?>
								</div>
							<?php endwhile; ?>
						<?php endif; ?>
					<?php endwhile; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

<?php endif; ?>
