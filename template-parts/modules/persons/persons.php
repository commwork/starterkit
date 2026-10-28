<?php if ( get_row_layout() == 'persons' ) : ?>

	<?php
	$persons = array();
	if ( have_rows( 'content' ) ) :
		while ( have_rows( 'content' ) ) :
			the_row();
			$person_field = get_sub_field( 'person' );
			if ( empty( $person_field ) ) {
				continue;
			}
			$person_rows = is_array( $person_field ) ? $person_field : array( $person_field );
			foreach ( $person_rows as $person_row ) {
				if ( ! $person_row ) {
					continue;
				}
				$person_id = is_object( $person_row ) && isset( $person_row->ID ) ? (int) $person_row->ID : (int) $person_row;
				if ( $person_id > 0 ) {
					$persons[ $person_id ] = get_post( $person_id );
				}
			}
		endwhile;
	endif;
	?>


    <?php if ( ! empty( $persons ) ) : ?>
        <div class="row module-content persons-list g-3">
            <?php foreach ( $persons as $post ) : ?>
                <?php if ( ! $post instanceof WP_Post ) { continue; } ?>
                <?php setup_postdata( $post ); ?>
                <div class="col-12 col-sm-6 col-lg-4">
                    <?php $portrait = get_field( 'portrait', $post->ID ); ?>
                    <?php if ( $portrait ) : ?>
                        <?php $portrait_id = is_array( $portrait ) ? ( $portrait['ID'] ?? 0 ) : (int) $portrait; ?>
                        <div class="person-image"><?php echo wp_get_attachment_image( $portrait_id, 'full' ); ?></div>
                    <?php else : ?>
                        <div class="person-image ">
                            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/person-placeholder-neutral.png' ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
                        </div>
                    <?php endif; ?>

                    <div class="person-content box box--s">
                        <div class="h4 person-title">
                            <?php the_title(); ?>
                        </div>

                        <?php if ( have_rows( 'person_details' ) ) : ?>
                            <div class="person-details stack stack--s">
                                <?php while ( have_rows( 'person_details' ) ) : the_row(); ?>
                                    <?php if ( get_sub_field( 'position' ) ) : ?>
                                        <div class="person-position">
                                            <strong><?php echo esc_html( (string) get_sub_field( 'position' ) ); ?></strong>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ( get_sub_field( 'bio' ) ) : ?>
                                        <div class="person-bio"><?php echo wp_kses_post( (string) get_sub_field( 'bio' ) ); ?></div>
                                    <?php endif; ?>

                                    <?php if ( get_sub_field( 'phone' ) || get_sub_field( 'email' ) ) : ?>
                                        <div class="person-contact stack stack--xs">
                                            <?php if ( get_sub_field( 'phone' ) ) : ?>
                                                <?php $phone = get_sub_field( 'phone' ); ?>
                                                <div class="person-phone">
                                                    <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ( get_sub_field( 'email' ) ) : ?>
                                                <?php $email = get_sub_field( 'email' ); ?>
                                                <div class="person-email">
                                                    <a href="mailto:<?php echo esc_attr( sanitize_email( $email ) ); ?>"><?php echo esc_html( $email ); ?></a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php wp_reset_postdata(); ?>
        </div>
    <?php endif; ?>


<?php endif; ?>
