<?php if ( get_row_layout() == 'contact_information' ) : ?>

	<?php
	$contact_rows_field = have_rows( 'contct_information-fields' ) ? 'contct_information-fields' : 'contact_information-fields';
	?>

	<?php if ( have_rows( $contact_rows_field ) ) : ?>
		<div class="row content-row stack-row stack-row--m module-contact-information">
			<div class="col-12 stack stack--s">
				<?php while ( have_rows( $contact_rows_field ) ) : the_row(); ?>
					<div class="contact-information-entry stack stack--m">
						<?php if ( get_sub_field( 'name' ) ) : ?>
							<p class="h5 contact-information-name"><?php echo esc_html( (string) get_sub_field( 'name' ) ); ?></p>
						<?php endif; ?>

						<?php if ( get_sub_field( 'adress' ) ) : ?>
							<div class="contact-information-address txt lcnmb"><?php echo wp_kses_post( (string) get_sub_field( 'adress' ) ); ?></div>
						<?php endif; ?>

						<?php if ( have_rows( 'phone' ) || have_rows( 'fax' ) || have_rows( 'mail' ) ) : ?>
							<div class="contact-information-phone-mail">
								<?php if ( have_rows( 'phone' ) ) : ?>
									<div class="contact-information-phone stack stack--xs">
										<?php
										while ( have_rows( 'phone' ) ) :
											the_row();
											$phone_number = get_sub_field( 'phone_number' );
											if ( (string) $phone_number === '' ) {
												continue;
											}
											?>
											<div class="contact-information-phone-item">
												<?php esc_html_e( 'Telefon:', 'starterkit' ); ?>
												<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone_number ) ); ?>"><?php echo esc_html( $phone_number ); ?></a>
											</div>
										<?php endwhile; ?>
									</div>
								<?php endif; ?>

								<?php if ( have_rows( 'fax' ) ) : ?>
									<div class="contact-information-fax stack stack--xs">
										<?php
										while ( have_rows( 'fax' ) ) :
											the_row();
											$fax_number = get_sub_field( 'fax_number' );
											if ( (string) $fax_number === '' ) {
												continue;
											}
											?>
											<div class="contact-information-fax-item">
												<?php esc_html_e( 'Fax:', 'starterkit' ); ?>
												<span class="contact-information-fax-number"><?php echo esc_html( $fax_number ); ?></span>
											</div>
										<?php endwhile; ?>
									</div>
								<?php endif; ?>

								<?php if ( have_rows( 'mail' ) ) : ?>
									<div class="contact-information-mail stack stack--xs">
										<?php
										while ( have_rows( 'mail' ) ) :
											the_row();
											$email = get_sub_field( 'e-mail_adress' );
											if ( (string) $email === '' ) {
												continue;
											}
											$sanitized = sanitize_email( $email );
											?>
											<div class="contact-information-mail-item">
												<?php esc_html_e( 'E-Mail:', 'starterkit' ); ?>
												<a href="mailto:<?php echo esc_attr( $sanitized ); ?>"><?php echo esc_html( $email ); ?></a>
											</div>
										<?php endwhile; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

					</div>
				<?php endwhile; ?>
			</div>
		</div>
	<?php endif; ?>

<?php endif; ?>
