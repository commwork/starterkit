<?php if ( get_row_layout() == 'text' ) : ?>

	<?php
	$headline = (string) get_sub_field( 'headline' );
	$text     = (string) get_sub_field( 'text' );
	?>
	<?php if ( $headline !== '' || $text !== '' ) : ?>
		<div class="row content-row stack-row stack-row--m">
			<div class="col-12 content-text stack stack--s txt lcnmb">
				<?php if ( $headline !== '' ) : ?>
					<div class="content-text__headline h5">
						<?php echo esc_html( $headline ); ?>
					</div>
				<?php endif; ?>
				<?php if ( $text !== '' ) : ?>
					<div class="content-text__body">
						<?php echo ( $text ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

<?php endif; ?>
