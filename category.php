<?php
/**
 * Category archive template for news posts.
 *
 * @package starterkit
 */

get_header();

$current_category = get_queried_object();

$used_categories = get_categories(
	array(
		'taxonomy'   => 'category',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);

$categories_by_parent = array();
$top_level_categories = array();
if ( ! empty( $used_categories ) ) {
	foreach ( $used_categories as $category ) {
		$parent = (int) $category->parent;
		if ( ! isset( $categories_by_parent[ $parent ] ) ) {
			$categories_by_parent[ $parent ] = array();
		}
		$categories_by_parent[ $parent ][] = $category;
	}
	$top_level_categories = $categories_by_parent[0] ?? array();
	if ( empty( $top_level_categories ) ) {
		$top_level_categories = $used_categories;
	}
}

$used_tags = get_tags(
	array(
		'hide_empty' => true,
	)
);
?>

<section class="ssection section--xl section-news-archive layout-page-section">
	<div class="container stack stack--l">

		<div class="row">
			<div class="col-12 col-lg-8">
				<div class="section-header stack stack--m" aria-labelledby="news-category-headline">
					<p class="section-description">
						<?php esc_html_e( 'Kategorie', 'starterkit' ); ?>
					</p>

					<h1 class="section-title" id="news-category-headline">
						<?php single_cat_title(); ?>
					</h1>
					
					<?php if ( ! empty( $current_category->description ) ) : ?>
						<div class="section-intro txt lcnmb">
							<?php echo wp_kses_post( $current_category->description ); ?>
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
					<p class="not-found-text txt"><?php esc_html_e( 'Keine News in dieser Kategorie gefunden.', 'starterkit' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="row">
			<div class="col-12">
				<div class="section-sidebar">
					<aside class="sidebar-category-tags">
						<!-- Categories -->
						<h3 class="sidebar-category-title">
							<?php esc_html_e( 'Kategorien', 'starterkit' ); ?>
						</h3>

						<?php if ( ! empty( $used_categories ) ) : ?>
							<?php foreach ( $top_level_categories as $parent_category ) : ?>
								<div class="sidebar-category-group">
									<div class="sidebar-category-parent">
										<a href="<?php echo esc_url( get_category_link( $parent_category->term_id ) ); ?>" class="btn tag">
											<?php echo esc_html( $parent_category->name ); ?>
										</a>
									</div>
									<?php
									$child_categories = $categories_by_parent[ (int) $parent_category->term_id ] ?? array();
									if ( ! empty( $child_categories ) ) :
										?>
										<div class="sidebar-category-children">
											<?php foreach ( $child_categories as $child_category ) : ?>
												<a href="<?php echo esc_url( get_category_link( $child_category->term_id ) ); ?>" class="btn tag">
													<?php echo esc_html( $child_category->name ); ?>
												</a>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>

						<!-- Tags -->
						<?php /* if ( ! empty( $used_tags ) ) : ?>
							<div class="sidebar-tags">
								<h3 class="sidebar-tags-title mt-4">
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
						<?php endif; */ ?>
					</aside>
				</div>
			</div>
		</div>

	</div>
</section>

<?php get_footer(); ?>
