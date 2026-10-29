<?php
/**
 * Layout "numbers": hero block with header clone + numbers grid (no modules_content).
 * ACF: layout `numbers` — `header` (clone), `content` (group) with repeater `numbers`.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

if ( get_row_layout() !== 'numbers' ) {
	return;
}
?>

<section class="section section--xl numbers-module layout-page-section layout-page-section--numbers"<?php echo starterkit_layout_row_jump_link_id_attr(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="container">
		<div class="row g-5">
			<!-- Header -->
			<?php if ( starterkit_layout_row_has_header_content() ) : ?>
				<div class="col-12 col-lg-4 stack stack--m">
					<?php get_template_part( 'template-parts/layout-parts/layout-row-header' ); ?>
				</div>
			<?php endif; ?>

			<!-- Content -->
			<?php if ( have_rows( 'content' ) ) : ?>
				<div class="col-12 col-lg-8 stack stack--m">
					<div class="row numbers-list g-5">
						<?php while ( have_rows( 'content' ) ) : the_row(); ?>
							<?php if ( have_rows( 'numbers' ) ) : ?>
								<?php while ( have_rows( 'numbers' ) ) : the_row(); ?>
									<?php
									$number_value = trim( (string) get_sub_field( 'number' ) );
									$title_value  = trim( (string) get_sub_field( 'title' ) );
									?>
									<div class="col-12 col-sm-4 numbers-item">
										<div class="numbers-item-inner">
											<?php if ( '' !== $number_value ) : ?>
												<p class="numbers-number">
													<?php echo esc_html( $number_value ); ?>
												</p>
											<?php else : ?>
												<p class="numbers-number"></p>
											<?php endif; ?>

											<?php if ( '' !== $title_value ) : ?>
												<p class="numbers-title">
													<?php echo esc_html( $title_value ); ?>
												</p>
											<?php else : ?>
												<p class="numbers-title"></p>
											<?php endif; ?>
										</div>
									</div>
								<?php endwhile; ?>
							<?php endif; ?>
						<?php endwhile; ?>
					</div>
				</div>
			<?php endif; ?>			
		</div>
	</div>	

</section>
