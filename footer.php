</main>		

<footer class="site-footer">
    <div class="container-fluid">
        <div class="row g-4">

            <div class="col-12">
                <div class="col-inner stack stack--m">
                    <div class="row g-4">

                        <div class="col-12 col-lg-6">
                            <h2 class="h4 mb-3">
                                AWO Soziale Dienste Vorpommern gGmbH
                            </h2>
                            
                            <p class="p">
                                Frankendamm 45<br>
                                18439 Stralsund<br>
                                Telefon: <a href="tel:0383130970">03831 3097-0</a><br>
                                E-Mail: <a href="mailto:info@awo-vorpommern.de">info@awo-vorpommern.de</a><br><br>
                            </p>

                            <div class="footer-legal">
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

                            <div class="footer-copyright mt-3">
                                <p>
                                    Copyright <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 col-lg-6 d-flex justify-content-end">
                            <div class="footer-social">
                                <a href="https://www.facebook.com/awo.stralsund" target="_blank" rel="noopener noreferrer">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/iconmonstr-facebook-3.svg" alt="Facebook">
                                </a>
                                <a href="https://www.instagram.com/awo.vorpommern/" target="_blank" rel="noopener noreferrer">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/iconmonstr-instagram-13.svg" alt="Instagram">
                                </a>
                            </div>
                        </div>
                        
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