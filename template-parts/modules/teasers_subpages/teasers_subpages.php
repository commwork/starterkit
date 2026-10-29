<?php if ( get_row_layout() == 'teasers_subpages' ) : ?>

    <?php
        /* Buttons */
        $buttons = [];
        $section_extra_class = '';
        $module_options = get_sub_field( 'module_options' );
        if ( is_array( $module_options ) && ! empty( $module_options['bg_grey'] ) ) {
            $section_extra_class = ' bg_grey';
        }
        $header_raw = get_sub_field( 'header' );
        if ( is_array( $header_raw ) ) {
            $header_rows = ( isset( $header_raw[0] ) && is_array( $header_raw[0] ) ) ? $header_raw : array( $header_raw );
            foreach ( $header_rows as $header_row ) {
                if ( ! is_array( $header_row ) || ! isset( $header_row['buttons'] ) || ! is_array( $header_row['buttons'] ) ) {
                    continue;
                }
                foreach ( $header_row['buttons'] as $button_row ) {
                    $url = is_array( $button_row ) && isset( $button_row['url'] ) ? $button_row['url'] : $button_row;
                    if ( is_array( $url ) && ! empty( $url['url'] ) ) {
                        $buttons[] = $url;
                    }
                }
            }
        }
    ?>

    <section class="section section-teasers-subpages section--xl<?php echo esc_attr( $section_extra_class ); ?>">
        <div class="container stack stack--m">

            <!-- Header -->
            <?php if ( have_rows( 'header' ) ) : ?>
                <?php while ( have_rows( 'header' ) ) : the_row(); ?>
                    <?php
                        $titel = get_sub_field( 'titel', false );
                        $titel = is_string( $titel ) ? trim( $titel ) : '';
                        $has_header_content =
                            get_sub_field( 'subheadline' ) ||
                            $titel !== '' ||
                            get_sub_field( 'intro' );
                    ?>
                    <?php if ( $has_header_content ) : ?>
                        <div class="row">
                            <div class="col-12 stack stack--m">
                                <div class="section-header stack stack--s">
                                    <?php if ( get_sub_field( 'subheadline' ) ) : ?>
                                        <p class="section-description">
                                            <?php the_sub_field( 'subheadline' ); ?>
                                        </p>
                                    <?php endif; ?>

                                    <?php if ( $titel !== '' ) : ?>
                                        <?php
                                        $headline_tag   = starterkit_section_titel_heading_tag();
                                        $headline_class = starterkit_section_titel_heading_class( $headline_tag );
                                        ?>
                                        <<?php echo tag_escape( $headline_tag ); ?> class="<?php echo esc_attr( $headline_class ); ?>">
                                            <?php echo nl2br( esc_html( $titel ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                        </<?php echo tag_escape( $headline_tag ); ?>>
                                    <?php endif; ?>

                                    <?php if ( get_sub_field( 'intro' ) ) : ?>
                                        <div class="section-intro txt lcnmb">
                                            <?php echo wp_kses_post( get_sub_field( 'intro' ) ); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php endif; ?>

            <!-- Content -->
            <?php if ( have_rows( 'content' ) ) : ?>
                <div class="row module-content">
                    <div class="col-12">
                        <div class="teasers-subpages-list stack stack--s">
                            <?php while ( have_rows( 'content' ) ) : the_row(); ?>
                                <div class="row g-5">
                                    <?php $subpages = get_sub_field( 'subpages' ); ?>
                                    <?php if ( $subpages && is_array( $subpages ) ) : ?>
                                        <?php foreach ( $subpages as $subpage_post ) : ?>
                                            <div class="col-12 col-md-6 col-lg-4">
                                                <?php
                                                    $subpage_id = is_object( $subpage_post ) ? (int) $subpage_post->ID : (int) $subpage_post;
                                                    if ( ! $subpage_id ) {
                                                        continue;
                                                    }
                                                    $subpage_url   = get_permalink( $subpage_id );
                                                    $subpage_title = get_the_title( $subpage_id );
                                                    $subpage_excerpt = get_field( 'custom_excerpt', $subpage_id );
                                                    if ( ! $subpage_url || ! $subpage_title ) {
                                                        continue;
                                                    }
                                                ?>
                                                <a href="<?php echo esc_url( $subpage_url ); ?>" class="box box--s">
                                                    <div class="teasers-subpages-item-content stack stack--s">
                                                        <!-- Post thumbnail -->
                                                        <?php if ( has_post_thumbnail( $subpage_id ) ) : ?>
                                                            <div class="teasers-subpages-item-content-thumbnail">
                                                                <?php echo get_the_post_thumbnail( $subpage_id, 'full' ); ?>
                                                            </div>
                                                        <?php endif; ?>

                                                        <!-- Title -->
                                                        <div class="h4">
                                                            <?php echo esc_html( $subpage_title ); ?>
                                                        </div>

                                                        <!-- Excerpt -->
                                                        <?php if ( $subpage_excerpt ) : ?>
                                                            <div class="teasers-subpages-item-content-excerpt">
                                                                <?php echo wp_kses_post( $subpage_excerpt ); ?>
                                                            </div>
                                                        <?php endif; ?>

                                                        <span class="btn primary arrow-right btn--teasers-subpages">
                                                            <?php esc_html_e( 'Mehr erfahren', 'starterkit' ); ?>
                                                        </span>
                                                    </div>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Buttons -->
            <?php if ( ! empty( $buttons ) ) : ?>
                <div class="row read-more-buttons">
                    <div class="col-12">
                        <?php foreach ( $buttons as $url ) : ?>
                            <a href="<?php echo esc_url( $url['url'] ); ?>" target="<?php echo esc_attr( $url['target'] ?? '_self' ); ?>" class="btn primary arrow-right"><?php echo esc_html( $url['title'] ?? '' ); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </section>

<?php endif; ?>
