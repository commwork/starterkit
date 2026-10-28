<?php get_header(); ?>

<?php 
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

<?php $has_search_query = get_search_query() !== ''; ?>

<section class="section section--xl section-search layout-page-section">	
	<div class="container container-narrow">
		<div class="row">
			<div class="col-12">
				<div class="container-search">	

					<section class="search-mask">
						<h1 class="page-title">
							<?php esc_html_e( 'Suche', 'starterkit' ); ?>
						</h1>
						
						<?php get_search_form(); ?>
					</section>

					<!-- Categories + Tags -->
					<section class="section-category-tags">
						<p class="section-category-tags-title">
							Alternativ zur Suche können Sie auch nach Kategorien oder Tags filtern:
						</p>

						<?php if ( ! empty( $used_categories ) ) : ?>
							<h2 class=" h4 section-category-tags-title">
								<?php esc_html_e( 'Kategorien', 'starterkit' ); ?>
							</h2>
							
							<div class="section-category">
								<?php foreach ( $used_categories as $category ) : ?>
									<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" class="btn tag">
										<?php echo esc_html( $category->name ); ?>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $used_tags ) ) : ?>
							<h2 class="h4 section-category-tags-title">
								<?php esc_html_e( 'Tags', 'starterkit' ); ?>
							</h2>
							
							<div class="section-tags">
								<?php foreach ( $used_tags as $tag ) : ?>
									<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="btn tag">
										<?php echo esc_html( $tag->name ); ?>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</section>

					<?php if ( ! $has_search_query ) : ?>

						<?php
						// No query yet: only the search form above, no results or message.
						?>

					<?php elseif ( have_posts() ) : ?>

						<header class="search-results-header">
							<h2 class="search-title">
								<?php
								/* translators: %s: search query. */
								printf( esc_html__( 'Suchergebnisse für: %s', 'starterkit' ), '<span>"' . esc_html( get_search_query() ) . '"</span>' );
								?>
							</h2>
						</header>

						<div class="search-results-entries">
							<?php
								while ( have_posts() ) :
									the_post(); ?>
									<?php the_title( '<h3 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' ); ?>
								<?php endwhile;

								the_posts_navigation(); 
							?>
						</div>

					<?php else : ?>

						<section class="search-results-none">
							<p class="not-found-text txt">
								<?php esc_html_e( 'Es wurden keine Treffer gefunden. Bitte versuchen Sie es mit anderen Suchbegriffen.', 'starterkit' ); ?>
							</p>
						</section>

					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>
