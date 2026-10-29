<?php
/**
 * Layout "news_slider": hero block with header clone + latest news slider (no modules_content).
 * ACF: layout `news_slider` — `header` (clone), `content` (group/repeater; currently not required).
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

if ( get_row_layout() !== 'news_slider' ) {
	return;
}

$news_query = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 5,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

?>

<section class="section section--xl section-news-slider layout-page-section"<?php echo starterkit_layout_row_jump_link_id_attr(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="container stack stack--l">
		<div class="row g-5">

			<!-- Header -->
			<?php if ( starterkit_layout_row_has_header_content() ) : ?>
				<div class="col-12 col-lg-8 stack stack--m">
					<?php get_template_part( 'template-parts/layout-parts/layout-row-header' ); ?>
				</div>
			<?php endif; ?>

			<!-- Content -->
			<?php if ( $news_query->have_posts() ) : ?>		
				<div class="col-12 module-content stack stack--m">
					<div class="news-slider-breakout">
						<div class="swiper news-slider-swiper">
							<div class="swiper-wrapper">
							<?php
							while ( $news_query->have_posts() ) :
								$news_query->the_post();
								get_template_part(
									'template-parts/teaser-cards/news-teaser-card',
									null,
									array(
										'class' => 'swiper-slide',
									)
								);
							endwhile;
							?>
								<article class="swiper-slide post-item news-slider-slide--cta">
									<div class="post-item-content">
										<a href="<?php echo esc_url( starterkit_news_archive_url() ); ?>" class="wrapper-link box box--s">
											<div class="stack news-slider-cta-stack">
												<h2 class="h4">
													<?php esc_html_e( 'Mehr Info', 'starterkit' ); ?>
												</h2>

												<p>
													<?php esc_html_e( 'Du möchtest alle News auf einen Blick sehen?', 'starterkit' ); ?>
												</p>

												<span class="btn primary--white arrow-right">
													<?php esc_html_e( 'Zur News-Übersicht', 'starterkit' ); ?>
												</span>
											</div>
										</a>
									</div>
								</article>
							</div>
						</div>
					</div>

					<div class="swiper-footer">
						<div class="swiper-pagination"></div>
					</div>
				</div>
				
			<?php wp_reset_postdata(); ?>

			<?php else : ?>
				<p class="not-found-text txt">
					<?php esc_html_e( 'Keine News gefunden.', 'starterkit' ); ?>
				</p>
			<?php endif; ?>

		</div>		
	</div>
</section>
