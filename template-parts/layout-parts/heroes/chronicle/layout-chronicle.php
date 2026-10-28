<?php
/**
 * Layout "chronicle": hero block with header clone + timeline (no modules_content).
 * ACF: layout `chronicle` — `header` (clone), `content` (group) with repeater `year`.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

if ( get_row_layout() !== 'chronicle' ) {
	return;
}

$chronicle_block_id = 'chronicle-' . get_row_index();
?>

<section class="section section--xl chronicle-module layout-page-section layout-page-section--chronicle" data-chronicle-block="<?php echo esc_attr( $chronicle_block_id ); ?>"<?php echo starterkit_layout_row_jump_link_id_attr(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<!-- Header -->
	<div class="container container-narrow">
		<?php if ( starterkit_layout_row_has_header_content() ) : ?>
			<div class="row">
				<div class="col-12 stack stack--m">
					<?php get_template_part( 'template-parts/layout-parts/layout-row-header' ); ?>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<!-- Content -->
	<?php if ( have_rows( 'content' ) ) : ?>
		<?php while ( have_rows( 'content' ) ) : the_row(); ?>
			<?php if ( have_rows( 'year' ) ) : ?>
				<?php
				$nav_links  = array();
				$year_index = 0;
				?>
				<div class="container stack stack--m">
					<div class="row module-content">
						<div class="col-12 col-sm-12 col-lg-10 offset-lg-1">
							<div class="chronicle-content">
								<div class="chronicle-line" aria-hidden="true">
									<div class="chronicle-line-fill"></div>
								</div>
								<?php
								while ( have_rows( 'year' ) ) :
									the_row();
									$year_index++;
									$titel_jahreszahl = get_sub_field( 'titel_jahreszahl' );
									$year_anchor_id   = $chronicle_block_id . '-y-' . $year_index;
									if ( $titel_jahreszahl !== '' && $titel_jahreszahl !== null ) {
										$nav_links[] = array(
											'id'    => $year_anchor_id,
											'label' => $titel_jahreszahl,
										);
									}
									?>
								<div class="chronicle-year <?php echo ( $year_index % 2 === 0 ) ? 'even' : 'odd'; ?>">
									<span class="chronicle-year-marker" aria-hidden="true"></span>
									<div class="chronicle-year-content">
										<?php if ( $titel_jahreszahl !== '' && $titel_jahreszahl !== null ) : ?>
											<span class="year-anchor" id="<?php echo esc_attr( $year_anchor_id ); ?>"></span>
											<div class="chronicle-year-title">
												<?php echo esc_html( $titel_jahreszahl ); ?>
											</div>
										<?php endif; ?>

										<?php if ( have_rows( 'events' ) ) : ?>
											<ul class="chronicle-events">
												<?php
												while ( have_rows( 'events' ) ) :
													the_row();
													$date = get_sub_field( 'date' );
													$text = get_sub_field( 'text' );
													?>
													<li class="chronicle-event">
														<?php if ( $date !== '' && $date !== null ) : ?>
															<time class="chronicle-event-date h5"><?php echo esc_html( $date ); ?><?php if ( $titel_jahreszahl !== '' && $titel_jahreszahl !== null ) : ?><span class="chronicle-event-year"><?php echo esc_html( $titel_jahreszahl ); ?></span><?php endif; ?></time>
														<?php endif; ?>
														<?php if ( $text !== '' && $text !== null ) : ?>
															<span class="chronicle-event-text"><?php echo wp_kses_post( $text ); ?></span>
														<?php endif; ?>
													</li>
													<?php
												endwhile;
												?>
											</ul>
										<?php endif; ?>
									</div>

									<div class="chronicle-year-image">
										<?php
										$image = get_sub_field( 'image' );
										if ( $image ) :
											$image_id = is_array( $image ) ? (int) ( $image['ID'] ?? 0 ) : (int) $image;
											if ( $image_id ) :
												?>
												
													<?php
													echo wp_get_attachment_image(
														$image_id,
														'large',
														false,
														array(
															'sizes'   => '(max-width: 991px) 100vw, 50vw',
															'loading' => 'lazy',
															'class'   => 'chronicle-year-img',
														)
													);
													?>
												
												<?php
											endif;											
										endif;
										?>									
									</div>
								</div>
								<?php endwhile; ?>
							</div>
						</div>
					</div>
				</div>

				<?php if ( ! empty( $nav_links ) ) : ?>
					<nav class="chronicle-navigation" aria-label="<?php esc_attr_e( 'Chronik-Jahre', 'starterkit' ); ?>">
						<div class="chronicle-navigation-list-wrapper">
							<ul class="chronicle-navigation-list">
								<?php foreach ( $nav_links as $link ) : ?>
									<li class="chronicle-navigation-item">
										<a href="#<?php echo esc_attr( $link['id'] ); ?>" class="chronicle-navigation-link"><?php echo esc_html( $link['label'] ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</nav>
				<?php endif; ?>

			<?php endif; ?>
		<?php endwhile; ?>
	<?php endif; ?>

	<!-- Footer -->
	<?php if ( have_rows( 'footer' ) ) : ?>
		<?php while ( have_rows( 'footer' ) ) : the_row(); ?>
			<?php
				$headline = get_sub_field( 'headline' );
				$text     = get_sub_field( 'text' );
			?>
			<?php if ( $headline || $text ) : ?>
				<div class="container">
					<div class="row">
						<div class="col-12 chronicle-footer stack stack--m">
							<?php if ( $headline ) : ?>
								<h2 class="h3">
									<?php echo esc_html( $headline ); ?>
								</h2>
							<?php endif; ?>

							<?php if ( $text ) : ?>
								<div class="txt">
									<?php echo wp_kses_post( $text ); ?>
								</div>
							<?php endif; ?>

							<!-- Buttons -->
							<?php if ( ! empty( $buttons ) ) : ?>                           
								<div class="read-more-buttons">
									<?php foreach ( $buttons as $url ) : ?>
										<a href="<?php echo esc_url( $url['url'] ); ?>" target="<?php echo esc_attr( $url['target'] ?? '_self' ); ?>" class="btn primary arrow-right"><?php echo esc_html( $url['title'] ?? '' ); ?></a>
									<?php endforeach; ?>
								</div>                           
							<?php endif; ?>
						</div>                        
					</div>
				</div>
			<?php endif; ?>
		<?php endwhile; ?>
	<?php endif; ?>

</section>
