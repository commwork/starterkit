<?php if ( get_row_layout() == 'infotext' ) : ?>

	<?php
	$text = (string) get_sub_field( 'text' );
	?>
	<?php if ( trim( wp_strip_all_tags( $text ) ) !== '' ) : ?>
		<div class="row content-row stack-row stack-row--m module-infotext module-content">
			<div class="col-12">
				<div class="module-infotext__content box box--s txt lcnmb">
					<?php echo $text; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

<?php endif; ?>
