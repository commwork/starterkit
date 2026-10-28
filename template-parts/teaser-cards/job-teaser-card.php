<?php
/**
 * Single job teaser card (grid column). Expects the global post in the loop.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;
?>

<article class="col-12 col-md-6 col-xxl-4 post-item">
    <a href="<?php the_permalink(); ?>" class="wrapper-link post-item-content box box--s">					
        <?php if ( has_post_thumbnail() ) : ?>
            <div class="post-item-image">
                <?php
                the_post_thumbnail(
                    'img-square-large',
                    array(
                        'sizes' => '(max-width: 767px) 100vw, (max-width: 1399px) 50vw, 33vw',
                    )
                );
                ?>
            </div>
        <?php else : ?>
            <div class="post-item-image">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/news-post-thumb-placeholder.png' ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
            </div>
        <?php endif; ?>

        <div class="stack stack--s">
            <p class="category">
                <?php echo esc_html( get_the_terms( get_the_ID(), 'job_kategorie' )[0]->name ); ?>
            </p>

            <div class="h4">
                <?php the_title(); ?>
            </div>

            <div>
                <?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 24 ) ); ?>
            </div>
            
            <span class="btn primary arrow-right">
                <?php esc_html_e( 'Zum Stellenangebot', 'starterkit' ); ?>
            </span>                                        
        </div>
    </a>
</article>
