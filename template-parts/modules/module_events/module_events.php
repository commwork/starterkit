<?php if ( get_row_layout() == 'module_events' ) : ?>

    <!-- Veranstaltungen (CPT, news-style teasers) -->
    <?php if ( have_rows( 'content' ) ) : ?>
        <?php while ( have_rows( 'content' ) ) : the_row(); ?>

            <?php
            $kategorie_term_id = (int) get_sub_field( 'veranstaltung_kategorie' );
            $query_args        = array();
            if ( $kategorie_term_id > 0 ) {
                $query_args['kategorie_term_id'] = $kategorie_term_id;
            }
            $veranstaltungen = starterkit_get_upcoming_veranstaltungen( $query_args );
            ?>

            <?php if ( ! empty( $veranstaltungen ) ) : ?>
                <div class="row module-content module-events g-4">
                    <?php
                    foreach ( $veranstaltungen as $veranstaltung ) :
                        get_template_part(
                            'template-parts/veranstaltungen/veranstaltung-teaser-card',
                            null,
                            array(
                                'post_id' => $veranstaltung->ID,
                            )
                        );
                    endforeach;
                    ?>
                </div>
            <?php endif; ?>

        <?php endwhile; ?>
    <?php endif; ?>

<?php endif; ?>
