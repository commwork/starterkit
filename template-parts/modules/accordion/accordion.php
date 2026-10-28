<?php if ( get_row_layout() == 'accordion' ) : ?>

    <?php if ( have_rows( 'content' ) ) : ?>
        <?php while ( have_rows( 'content' ) ) : the_row(); ?>
            <?php if ( have_rows( 'accordion_row' ) ) : ?>
                <div class="row module-content stack stack--m">
                    <div class="col-12">
                        <?php $accordion_parent_id = 'accordion-module-' . wp_unique_id(); ?>
                        <div class="accordion accordion-module" id="<?php echo esc_attr( $accordion_parent_id ); ?>">
                            <?php while ( have_rows( 'accordion_row' ) ) : the_row(); ?>
                                <?php
                                $title = get_sub_field( 'title' );
                                $text  = get_sub_field( 'text' );
                                if ( $title === '' && $text === '' ) {
                                    continue;
                                }
                                $item_id = 'accordion-item-' . esc_attr( get_row_index() ) . '-' . esc_attr( uniqid() );
                                ?>
                                <div class="accordion-item">
                                    <div class="accordion-header" id="<?php echo esc_attr( $item_id ); ?>-heading">
                                        <button
                                            class="accordion-button collapsed"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#<?php echo esc_attr( $item_id ); ?>"
                                            aria-expanded="false"
                                            aria-controls="<?php echo esc_attr( $item_id ); ?>"
                                        >
                                            <span class="accordion-trigger-text"><?php echo esc_html( $title ); ?></span>
                                            <span class="accordion-icon accordion-icon--plus" aria-hidden="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                            </span>
                                            <span class="accordion-icon accordion-icon--minus" aria-hidden="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                                            </span>
                                        </button>
                                    </div>
                                    <div
                                        id="<?php echo esc_attr( $item_id ); ?>"
                                        class="accordion-collapse collapse"
                                        aria-labelledby="<?php echo esc_attr( $item_id ); ?>-heading"
                                        data-bs-parent="#<?php echo esc_attr( $accordion_parent_id ); ?>"
                                    >
                                        <div class="accordion-body txt">
                                            <?php echo wp_kses_post( $text ); ?>
                                        </div>
                                    </div>
                                </div> 
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>
    <?php endif; ?>

<?php endif; ?>
