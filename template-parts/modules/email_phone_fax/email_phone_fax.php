<?php if ( get_row_layout() == 'email_phone_fax' ) : ?>

	<?php if ( have_rows( 'contact_fields' ) ) : ?>
		<div class="row content-row stack-row stack-row--m module-email-phone-fax module-content">
			<div class="col-12 stack stack--s">
				<?php
				while ( have_rows( 'contact_fields' ) ) :
					the_row();

					$layout          = get_row_layout();
					$additional_text = trim( (string) get_sub_field( 'additional_text' ) );

					if ( $layout === 'phone' ) :
						$value = trim( (string) get_sub_field( 'tel_number' ) );
						if ( $value === '' && $additional_text === '' ) {
							continue;
						}
						?>
						<div class="email-phone-fax-item email-phone-fax-item--phone stack stack--xxs">
							<?php if ( $value !== '' ) : ?>
								<div class="email-phone-fax-item__value">
									<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $value ) ); ?>"><?php echo esc_html( $value ); ?></a>

									<?php if ( $additional_text !== '' ) : ?>
										<div class="email-phone-fax-item__additional"><?php echo esc_html( $additional_text ); ?></div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
						<?php
					elseif ( $layout === 'fax' ) :
						$value = trim( (string) get_sub_field( 'fax_number' ) );
						if ( $value === '' && $additional_text === '' ) {
							continue;
						}
						?>
						<div class="email-phone-fax-item email-phone-fax-item--fax stack stack--xxs">
							<?php if ( $value !== '' ) : ?>
								<div class="email-phone-fax-item__value">								
									<span class="email-phone-fax-item__number"><?php echo esc_html( $value ); ?></span>

									<?php if ( $additional_text !== '' ) : ?>
										<div class="email-phone-fax-item__additional"><?php echo esc_html( $additional_text ); ?></div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
						<?php
					elseif ( $layout === 'email' ) :
						$value = trim( (string) get_sub_field( 'email_address' ) );
						if ( $value === '' && $additional_text === '' ) {
							continue;
						}
						$sanitized_email = sanitize_email( $value );
						?>
						<div class="email-phone-fax-item email-phone-fax-item--email stack stack--xxs">
							<?php if ( $value !== '' ) : ?>
								<div class="email-phone-fax-item__value">
									<?php if ( $sanitized_email !== '' ) : ?>
										<a href="mailto:<?php echo esc_attr( $sanitized_email ); ?>"><?php echo esc_html( $value ); ?></a>
									<?php else : ?>
										<span class="email-phone-fax-item__number"><?php echo esc_html( $value ); ?></span>
									<?php endif; ?>

									<?php if ( $additional_text !== '' ) : ?>
										<div class="email-phone-fax-item__additional"><?php echo esc_html( $additional_text ); ?></div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
						<?php
					elseif ( $layout === 'address' ) :
						$value = trim( (string) get_sub_field( 'address' ) );
						if ( $value === '' && $additional_text === '' ) {
							continue;
						}
						?>
						<div class="email-phone-fax-item email-phone-fax-item--address stack stack--xxs">
							<?php if ( $value !== '' ) : ?>
								<div class="email-phone-fax-item__value">
									<span class="email-phone-fax-item__number"><?php echo esc_html( $value ); ?></span>

									<?php if ( $additional_text !== '' ) : ?>
										<div class="email-phone-fax-item__additional"><?php echo esc_html( $additional_text ); ?></div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php endwhile; ?>
			</div>
		</div>
	<?php endif; ?>

<?php endif; ?>
