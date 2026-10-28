<?php if ( get_row_layout() == 'module_headline' ) : ?>
	<?php
	$module_headline_text = (string) get_sub_field( 'text' );
	$headline_tag         = (string) get_sub_field( 'headline_hirarchy' );

	if ( ! in_array( $headline_tag, array( 'h2', 'h3', 'h4' ), true ) ) {
		$headline_tag = 'h2';
	}
	?>
	<div class="row module-headline module-content stack stack--m">
		<div class="col-12">
			<div class="stack stack--s">
				<<?php echo tag_escape( $headline_tag ); ?> class="<?php echo esc_attr( $headline_tag . ' inner-module-headline' ); ?>">
					<?php echo esc_html( $module_headline_text ); ?>
				</<?php echo tag_escape( $headline_tag ); ?>>
			</div>
		</div>
	</div>

<?php endif; ?>
