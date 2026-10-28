<?php
/**
 * Tag archive template for news posts.
 *
 * @package starterkit
 */

get_header();

$current_tag = get_queried_object();

$used_categories = get_categories(
	array(
		'taxonomy'   => 'category',
		'hide_empty' => true,
	)
);

$used_tags = get_tags(
	array(
		'hide_empty' => true,
	)
);
?>

<section class="section section--xl section-news-archive layout-page-section">
<div class="container stack stack--l">

	<div class="row">
		<div class="col-12 col-lg-8">
			<div class="section-header stack stack--m" aria-labelledby="news-tag-headline">
				<p class="section-description">
					Tags/Themen
				</p>

				<h1 class="section-title" id="news-tag-headline">
					<?php single_tag_title(); ?>
				</h1>
				
				<?php if ( ! empty( $current_tag->description ) ) : ?>
					<div class="section-intro txt lcnmb">
						<?php echo wp_kses_post( $current_tag->description ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>		
	</div>

	<div class="row">
		<div class="col-12 module-content">
			<?php if ( have_posts() ) : ?>
				<?php starterkit_set_job_teaser_headline_context( 'h2' ); ?>
				<div class="row g-4">
					<?php while ( have_posts() ) : the_post(); ?>
						<?php get_template_part( 'template-parts/teaser-cards/news-teaser-card' ); ?>
					<?php endwhile; ?>
				</div>

			<?php
				$pagination = paginate_links(
					array(
						'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
						'format'    => '?paged=%#%',
						'current'   => max( 1, get_query_var( 'paged' ) ),
						'total'     => (int) $wp_query->max_num_pages,
						'prev_text' => '',
						'next_text' => '&raquo;',
						'type'      => 'list',
					)
				);
				if ( $pagination ) {
					echo '<div class="pagination">';
					echo wp_kses_post( $pagination );
					echo '</div>';
				}
			?>
			<?php else : ?>
				<p class="not-found-text txt">
					<?php esc_html_e( 'Keine News mit diesem Thema/Tag gefunden.', 'starterkit' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<div class="row">
		<div class="col-12">
			<div class="section-sidebar">
				<aside class="sidebar-category-tags">
					<!-- Tags -->
					<?php if ( ! empty( $used_tags ) ) : ?>
						<div class="sidebar-tags">
							<h3 class="sidebar-tags-title">
								<?php esc_html_e( 'Themen (Tags)', 'starterkit' ); ?>
							</h3>

							<p>
								<small>Alle News zum jeweiligen Thema/Tag anzeigen lassen.</small>
							</p>
							
							<div class="sidebar-tags-list">
								<?php foreach ( $used_tags as $tag ) : ?>
									<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="btn tag">
										<?php echo esc_html( $tag->name ); ?>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</aside>
			</div>
		</div>
	</div>

</div>
</div>
</section>

<?php get_footer(); ?>
