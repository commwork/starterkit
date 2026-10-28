<?php if ( get_row_layout() == 'downloads' ) : ?>

    <!-- Downloads -->
    <?php if ( have_rows( 'content' ) ) : ?>
        <?php while ( have_rows( 'content' ) ) : the_row(); ?>
            <?php if ( have_rows( 'downloads' ) ) : ?>
                <div class="row module-downloads module-content stack stack--m">
                    <div class="col-12">
                        <ul class="downloads-list">
                            <?php while ( have_rows( 'downloads' ) ) : the_row(); ?>
                                <?php
                                    $file = get_sub_field( 'file' );
                                    if ( ! $file ) {
                                        continue;
                                    }
                                    $file_title   = ! empty( $file['title'] ) ? $file['title'] : $file['filename'];
                                    $file_ext     = pathinfo( $file['filename'], PATHINFO_EXTENSION );
                                    $file_size    = size_format( $file['filesize'], 2 );
                                    $aria_label   = sprintf(
                                        'Download %1$s (%2$s, %3$s)',
                                        $file_title,
                                        strtoupper( $file_ext ),
                                        $file_size
                                    );
                                ?>
                                <li>
                                    <a href="<?php echo esc_url( $file['url'] ); ?>" download aria-label="<?php echo esc_attr( $aria_label ); ?>" class="meta">
                                        <span class="file-icon">
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/svg/24/24-data-download.svg" alt="<?php echo esc_attr( $file_title ); ?>">
                                        </span>

                                        <span class="file-title">
                                            <?php echo esc_html( $file_title ); ?>
                                        </span>

                                        <span class="file-info">
                                            <span class="file-extension"><?php echo esc_html( $file_ext ); ?></span>
                                            <span class="file-size"><?php echo esc_html( $file_size ); ?></span>
                                        </span>
                                    </a>
                                </li>                             
                            <?php endwhile; ?>
                        </ul>
                    <?php endif; ?>
                <?php endwhile; ?>    
            </div>
        </div>                
    <?php endif; ?>

<?php endif; ?>
