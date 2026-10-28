<?php if ( get_row_layout() == 'links' ) : ?>

	<?php if ( have_rows( 'links' ) ) : ?>
		<div class="row module-content">
			<div class="col-12">
				<div class="module-links stack stack--s">
					<?php
					while ( have_rows( 'links' ) ) :
						the_row();
						$url = get_sub_field( 'url' );
						if ( ! is_array( $url ) || empty( $url['url'] ) ) {
							continue;
						}
						?>
						<a href="<?php echo esc_url( $url['url'] ); ?>" class="btn primary arrow-right icon-color-primary" target="<?php echo esc_attr( $url['target'] ?? '_self' ); ?>"><?php echo esc_html( $url['title'] ?? '' ); ?></a>
					<?php endwhile; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

<?php endif; ?>
