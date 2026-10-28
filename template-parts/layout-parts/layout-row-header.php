<?php
/**
 * Layout column: header (ACF repeater/group field `header` inside the current `layouts` row).
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

if ( ! have_rows( 'header' ) ) {
	return;
}

while ( have_rows( 'header' ) ) :
	the_row();

	$titel = get_sub_field( 'titel', false );
	$titel = is_string( $titel ) ? trim( $titel ) : '';

	$has_header_content =
		get_sub_field( 'subheadline' ) ||
		$titel !== '' ||
		get_sub_field( 'intro' ) ||
		have_rows( 'buttons' );

	if ( ! $has_header_content ) {
		continue;
	}
	?>
	
	<div class="section-header stack stack--m">
		<?php if ( get_sub_field( 'subheadline' ) ) : ?>
			<p class="section-description">
				<?php the_sub_field( 'subheadline' ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $titel !== '' ) : ?>
			<?php
			$headline_tag   = starterkit_section_titel_heading_tag();
			$headline_class = starterkit_section_titel_heading_class( $headline_tag );
			starterkit_sync_teaser_headline_context_from_section_tag( $headline_tag );
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

		<?php if ( have_rows( 'buttons' ) ) : ?>
			<div class="layout-row-header-buttons stack stack--s">
				<?php
				while ( have_rows( 'buttons' ) ) :
					the_row();
					$url = get_sub_field( 'url' );
					if ( ! is_array( $url ) || empty( $url['url'] ) ) {
						continue;
					}
					?>
					<a href="<?php echo esc_url( $url['url'] ); ?>" class="btn primary arrow-right" target="<?php echo esc_attr( $url['target'] ?? '_self' ); ?>"><?php echo esc_html( $url['title'] ?? '' ); ?></a>
				<?php endwhile; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
endwhile;
