<?php if ( get_row_layout() == 'offers_list' ) : ?>

    <!-- Offers (list) -->
    <?php if ( have_rows( 'content' ) ) : ?>
        <?php while ( have_rows( 'content' ) ) : the_row(); ?>

        <div class="row module-offers-list g-4">
            <?php if ( have_rows( 'offers' ) ) : ?>

                <?php while ( have_rows( 'offers' ) ) : the_row(); ?>
                    <?php $offer_title = (string) get_sub_field( 'titel' ); ?>
                    <div class="offers-item col-12">
                        <div class="box box--s">
                            <div class="offers-item-content stack stack--m">
                                <?php if ( $offer_title !== '' ) : ?>
                                    <div class="offers-title h3">
                                        <?php echo esc_html( $offer_title ); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ( have_rows( 'offer_list_entry' ) ) : ?>
                                    <ul class="offers-list">
                                        <?php while ( have_rows( 'offer_list_entry' ) ) : the_row(); ?>
                                            <?php $entry_text = (string) get_sub_field( 'text' ); ?>
                                            <?php if ( $entry_text !== '' ) : ?>
                                                <li>
                                                    <span class="offers-list-icon">
                                                        <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/svg/24/24-check.svg' ); ?>" alt="" width="24" height="24" />
                                                    </span>
                                                    <?php echo esc_html( $entry_text ); ?>
                                                </li>
                                            <?php endif; ?>
                                        <?php endwhile; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>

            <?php endif; ?>
        </div>

        <?php endwhile; ?>
    <?php endif; ?>

<?php endif; ?>
