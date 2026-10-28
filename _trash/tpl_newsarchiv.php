<?php
/*
 * Template Name: News Archiv
 */
get_header();

$paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
$news_query = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 6,
		'paged'          => $paged,
	)
);

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

<!-- <section class="section section--xl section-news-archive section--bg-grey layout-page-section"> -->
<section class="section section--xl section-news-archive layout-page-section">
<div class="container stack stack--l">

	<!-- Content -->
	<div class="row">
		<div class="col-12 module-content">
			<?php if ( $news_query->have_posts() ) : ?>
				<div class="row g-4">
					<?php while ( $news_query->have_posts() ) : $news_query->the_post(); ?>
						<?php get_template_part( 'template-parts/news/news-teaser-card' ); ?>
					<?php endwhile; ?>
				</div>

				<?php
					$pagination = paginate_links(
						array(
							'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
							'format'    => '?paged=%#%',
							'current'   => max( 1, $paged ),
							'total'     => (int) $news_query->max_num_pages,
							'prev_text' => '&laquo;',
							'next_text' => '&raquo;',
							'type'      => 'list',
							'add_fragment' => '#news-archive-content',
						)
					);
					if ( $pagination ) {
						echo '<div class="col-12 pagination">';
						echo wp_kses_post( $pagination );
						echo '</div>';
					}
					wp_reset_postdata();
				?>

				<?php else : ?>
					<p>
						<?php esc_html_e( 'Keine News gefunden.', 'starterkit' ); ?>
					</p>
			<?php endif; ?>
		</div>
	</div>

	<div class="row">
		<div class="col-12">
			<div class="section-sidebar">
				<aside class="sidebar-category-tags">
					<!-- Categories -->
					<?php if ( ! empty( $used_categories ) ) : ?>
						<div class="sidebar-category">
							<h3 class="sidebar-categories-title">
								<?php esc_html_e( 'Kategorien', 'starterkit' ); ?>
							</h3>

							<p>
								<small>Alle News zur jeweiligen Kategorie anzeigen lassen.</small>
							</p>
							
							<div class="sidebar-categories-list">
								<?php foreach ( $top_level_categories as $parent_category ) : ?>
									<div class="sidebar-category-group stack stack--xs">
										<div class="sidebar-category-parent">
											<a href="<?php echo esc_url( get_category_link( $parent_category->term_id ) ); ?>" class="btn category">
												<?php echo esc_html( $parent_category->name ); ?>
											</a>
										</div>
										<?php
										$child_categories = $categories_by_parent[ (int) $parent_category->term_id ] ?? array();
										if ( ! empty( $child_categories ) ) :
											?>
											<div class="sidebar-category-children">
												<?php foreach ( $child_categories as $child_category ) : ?>
													<a href="<?php echo esc_url( get_category_link( $child_category->term_id ) ); ?>" class="btn category">
														<?php echo esc_html( $child_category->name ); ?>
													</a>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

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
</section>

<?php get_footer(); ?>
