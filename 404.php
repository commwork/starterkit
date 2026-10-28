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

<section class="section section--xl section-404 layout-page-section">	
	<div class="container container-narrow">
		<div class="row">
			<div class="col-12 stack stack--m">
				<h1 class="page-title">
					<?php esc_html_e( 'Seite nicht gefunden', 'starterkit' ); ?>
				</h1>

				<p class="not-found-text txt section-404-description">
					<?php esc_html_e( 'Die gesuchte Seite konnte nicht gefunden werden.', 'starterkit' ); ?>
				</p>		

				<a href="<?php echo home_url(); ?>" class="btn primary arrow-left">
					<?php esc_html_e( 'Zur Startseite', 'starterkit' ); ?>
				</a>				
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>
