<?php
/**
 * News teaser card (news-style layout).
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

$icon_uri = get_template_directory_uri() . '/assets/images/icons/24/';
?>

<article class="col-12 col-md-6 col-xxl-4 post-item<?php echo is_sticky() ? ' post-item--sticky' : ''; ?>">
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

        <div class="stack">
            <?php if ( ! is_sticky() ) : ?>
                <div class="news-meta stack stack--xs">
                    <p class="meta news-meta__date">
                        <span class="news-meta__icon" aria-hidden="true">
                            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/svg/24/24-calendar.svg' ); ?>" alt="" width="24" height="24" loading="lazy" decoding="async" />
                        </span>
                        <?php echo esc_html( get_the_date() ); ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="h4">
                <?php the_title(); ?>
            </div>

            <div>
                <?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 24 ) ); ?>
            </div>
            
            <span class="btn primary arrow-right">
                <?php esc_html_e( 'Zum Artikel', 'starterkit' ); ?>
            </span>                                        
        </div>
    </a>
</article>