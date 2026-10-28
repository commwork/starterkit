<?php
/**
 * Layout "veranstaltungen_hero": header clone + upcoming Veranstaltungen grid (like news hero).
 * ACF: layout `veranstaltungen_hero` — `header` (clone), `content` (group) with `categories` taxonomy.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

if ( get_row_layout() !== 'veranstaltungen_hero' ) {
	return;
}

/*
 * Read `content` group without have_rows( 'content' ), so ACF context stays on this layout row
 * for layout-row-header (same pattern as heroes/news).
 */
$category_ids = array();
$content      = get_sub_field( 'content' );
if ( is_array( $content ) && ! empty( $content['categories'] ) ) {
	$category_ids = starterkit_parse_veranstaltung_kategorie_ids( $content['categories'] );
}

$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$per_page = 6;
$events_anchor = starterkit_get_layout_row_jump_link_id( 'veranstaltungen-hero-' . get_row_index() );

$query_args = array();
if ( ! empty( $category_ids ) ) {
	$query_args['kategorie_term_ids'] = $category_ids;
}

$all_veranstaltungen = starterkit_get_upcoming_veranstaltungen( $query_args );
$total               = count( $all_veranstaltungen );
$max_num_pages       = (int) max( 1, ceil( $total / $per_page ) );
$veranstaltungen     = array_slice( $all_veranstaltungen, ( $paged - 1 ) * $per_page, $per_page );
?>

<section class="section section--xl section-news-module section-events-hero layout-page-section layout-page-section--veranstaltungen_hero" id="<?php echo esc_attr( $events_anchor ); ?>">
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
		<?php if ( ! empty( $veranstaltungen ) ) : ?>
			<div class="row module-content module-events g-4">
				<?php
				foreach ( $veranstaltungen as $veranstaltung ) :
					get_template_part(
						'template-parts/veranstaltungen/veranstaltung-teaser-card',
						null,
						array(
							'post_id'    => $veranstaltung->ID,
							'grid_class' => 'col-12 col-md-6 col-xxl-4',
						)
					);
				endforeach;
				?>
			</div>

			<?php
			if ( $max_num_pages > 1 ) {
				$pagination = paginate_links(
					array(
						'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
						'format'       => '?paged=%#%',
						'current'      => $paged,
						'total'        => $max_num_pages,
						'prev_text'    => '&laquo;',
						'next_text'    => '&raquo;',
						'type'         => 'list',
						'add_fragment' => '#' . $events_anchor,
					)
				);
				if ( $pagination ) {
					echo '<div class="pagination">';
					echo wp_kses_post( $pagination );
					echo '</div>';
				}
			}
			?>
		<?php else : ?>
			<p class="not-found-text txt"><?php esc_html_e( 'Keine Veranstaltungen gefunden.', 'starterkit' ); ?></p>
		<?php endif; ?>

	</div>
</section>
