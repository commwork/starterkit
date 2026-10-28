<?php
/**
 * Layout "teasers_subpages": hero with header clone + subpage teasers (no modules_content).
 * ACF: layout `teasers_subpages` — `header` (clone), `content` (group) with `subpages` relationship.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

if ( get_row_layout() !== 'teasers_subpages' ) {
	return;
}

/*
 * Read `content` group without have_rows( 'content' ), so ACF context stays on this layout row.
 * Otherwise the_row() inside `content` breaks get_sub_field( 'header' ) for layout-row-header.
 */
$subpages = array();
$content  = get_sub_field( 'content' );
if ( is_array( $content ) && ! empty( $content['subpages'] ) ) {
	$subpages_field = $content['subpages'];
	$subpages       = is_array( $subpages_field ) ? $subpages_field : array( $subpages_field );
}
?>

<section class="section section-teasers-subpages section--xl layout-page-section layout-page-section--teasers_subpages"<?php echo starterkit_layout_row_jump_link_id_attr(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="container stack stack--l">
			
		<!-- Header -->
		<?php if ( starterkit_layout_row_has_header_content() ) : ?>
			<div class="row">
				<div class="col-12 col-lg-8 stack stack--m">
					<?php get_template_part( 'template-parts/layout-parts/layout-row-header' ); ?>
				</div>
			</div>
		<?php endif; ?>
			
		<!-- Content -->	
		<?php if ( ! empty( $subpages ) ) : ?>
			<div class="row module-content g-4">
				<?php foreach ( $subpages as $subpage_post ) : ?>
					<?php
					$subpage_id = is_object( $subpage_post ) ? (int) $subpage_post->ID : (int) $subpage_post;
					if ( ! $subpage_id ) {
						continue;
					}

					get_template_part(
						'template-parts/teaser-cards/subpages-teaser-card',
						null,
						array(
							'subpage_id' => $subpage_id,
						)
					);
					?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
