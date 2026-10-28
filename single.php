<?php get_header(); ?>

<section class="section section--xl layout-page-section">
	<div class="container stack stack--l">
        
        <div class="row g-4">
            <div class="col-12 col-lg-8 order-1 order-lg-2">
                <div class="module-content txt lcnmb stack stack--m">
                    <div class="section-header">
                        <h1 class="section-title"><?php the_title(); ?></h1>
                    </div>

					<?php get_template_part( 'template-parts/single-news-modules' ); ?>
                </div>
            </div>

            <div class="col-12 col-lg-4 order-2 order-lg-1">
                <div class="section-aside txt lcnmb">
                    <a href="<?php echo home_url( '/aktuelles' ); ?>" class="btn primary arrow-left">
                        Zurück zur Aktuelles-Übersicht
                    </a>
                </div>
            </div>
        </div>

	</div>
</section>
 
<?php get_footer(); ?>
