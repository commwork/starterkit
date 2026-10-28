</main>		

<footer class="site-footer">
    <div class="container-fluid">
        <div class="row g-4">

            <div class="col-12">
                <div class="col-inner">
                    <div class="row g-4">

                        <div class="col-12 col-lg-6">
                            <h2 class="h4 mb-3">
                                <strong>
                                    <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
                                </strong>
                            </h2>
                            
                            <?php if ( get_field( 'footer-adress', 'option' ) ) : ?>
                                <div class="footer-text">
                                    <?php echo get_field( 'footer-adress', 'option' ); ?>
                                </div>
                            <?php endif; ?>

                            <div class="footer-legal mt-3">
                                <?php
                                wp_nav_menu(
                                    array(
                                        'theme_location' => 'footer',
                                        'container'      => 'nav',
                                        'container_class'=> 'footer-legal-nav',
                                        'menu_class'     => 'footer-legal-menu',
                                        'fallback_cb'    => false,
                                    )
                                );
                                ?>
                            </div>

                            <div class="footer-copyright mt-3 small">
                                <p>
                                    Copyright <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>.
                                </p>
                            </div>
                        </div>

                        <?php
                        $social_links = get_field( 'social_links_with_icons', 'option' );
                        if ( is_array( $social_links ) && $social_links ) :
                            ?>
                            <div class="col-12 col-lg-6 d-flex justify-content-end">
                                <div class="footer-social">
                                    <?php
                                    foreach ( $social_links as $social_link ) :
                                        $link = isset( $social_link['social_link_url'] ) ? $social_link['social_link_url'] : null;
                                        if ( ! is_array( $link ) || empty( $link['url'] ) ) {
                                            continue;
                                        }

                                        $icon_id = isset( $social_link['social_link_icon'] ) ? (int) $social_link['social_link_icon'] : 0;
                                        $target  = isset( $link['target'] ) ? (string) $link['target'] : '';
                                        $title   = isset( $link['title'] ) ? (string) $link['title'] : '';
                                        ?>
                                        <a href="<?php echo esc_url( $link['url'] ); ?>"<?php echo '_blank' === $target ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                                            <?php
                                            if ( $icon_id ) {
                                                $alt = (string) get_post_meta( $icon_id, '_wp_attachment_image_alt', true );
                                                if ( '' === $alt ) {
                                                    $alt = $title;
                                                }
                                                echo wp_get_attachment_image(
                                                    $icon_id,
                                                    'full',
                                                    false,
                                                    array(
                                                        'alt' => $alt,
                                                    )
                                                );
                                            } elseif ( '' !== $title ) {
                                                echo esc_html( $title );
                                            }
                                            ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>

        </div>
    </div>
</footer>

<?php starterkit_render_responsive_control(); ?>

<?php wp_footer(); ?>

</body>

</html>