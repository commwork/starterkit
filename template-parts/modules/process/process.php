<?php if ( get_row_layout() == 'process' ) : ?>

    <!-- Process (3 columns) -->
    <?php if ( have_rows( 'content' ) ) : ?>
        <?php while ( have_rows( 'content' ) ) : the_row(); ?>

        <div class="row module-process g-3">
            <?php if ( have_rows( 'process_repeater' ) ) : ?>
                <?php
                while ( have_rows( 'process_repeater' ) ) :
                    the_row();

                    $process_title      = trim( (string) get_sub_field( 'title' ) );
                    $process_text       = (string) get_sub_field( 'text' );
                    $process_text_plain = trim( wp_strip_all_tags( $process_text ) );
                    $icons_group        = get_sub_field( 'icons_group' );
                    $process_icon_url   = '';
                    $process_time       = '';

                    if ( is_array( $icons_group ) ) {
                        $process_icon_url = trim( (string) ( $icons_group['icon'] ?? '' ) );
                        $process_time     = trim( (string) ( $icons_group['time_of_process_event'] ?? '' ) );
                    }

                    $process_clock_svg  = baukasten_render_process_clock_svg( $process_time );

                    if ( '' === $process_title && '' === $process_text_plain && '' === $process_icon_url && '' === $process_clock_svg ) {
                        continue;
                    }
                    ?>
                    <div class="process-item col-12">
                        <div class="box box--s">
                            <div class="process-icon">
                                <?php if ( '' !== $process_clock_svg ) : ?>
                                    <?php echo $process_clock_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in baukasten_render_process_clock_svg(). ?>
                                <?php elseif ( '' !== $process_icon_url ) : ?>
                                    <img src="<?php echo esc_url( $process_icon_url ); ?>" alt="" loading="lazy" decoding="async" />
                                <?php else : ?>
                                    <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/circle-arrow-right.svg' ); ?>" alt="" loading="lazy" decoding="async" />
                                <?php endif; ?>
                            </div>

                            <?php if ( '' !== $process_title || '' !== $process_text_plain ) : ?>
                                <div class="process-content">
                                    <?php if ( '' !== $process_title ) : ?>
                                        <div class="h4 process-title">
                                            <?php echo esc_html( $process_title ); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ( '' !== $process_text_plain ) : ?>
                                        <p class="process-text">
                                            <?php echo nl2br( esc_html( $process_text ) ); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <?php endwhile; ?>
    <?php endif; ?>

<?php endif; ?>
