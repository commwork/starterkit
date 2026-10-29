<?php if ( get_row_layout() == 'infotext' ) : ?>

	<?php
	$headline = trim( (string) get_sub_field( 'infotext_headline' ) );
	$text     = (string) get_sub_field( 'text' );
	$has_text = trim( wp_strip_all_tags( $text ) ) !== '';
	?>
	<?php if ( $headline !== '' || $has_text ) : ?>
		<div class="row content-row stack-row stack-row--m module-infotext module-content">
			<div class="col-12">
				<div class="module-infotext__content box box--s txt lcnmb">
					<?php if ( $headline !== '' ) : ?>
						<div class="h4"><?php echo nl2br( esc_html( $headline ) ); ?></div>
					<?php endif; ?>
					<?php if ( $has_text ) : ?>
						<?php echo $text; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

<?php endif; ?>
