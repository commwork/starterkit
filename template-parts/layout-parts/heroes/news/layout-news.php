<?php
/**
 * Layout "news": hero block with header clone + news grid (no modules_content).
 * ACF: layout `news` — `header` (clone), `content` (group) with `categories` and `news_count`.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

if ( get_row_layout() !== 'news' ) {
	return;
}

/*
 * Read `content` group without have_rows( 'content' ), so ACF context stays on this layout row
 * for layout-row-header (same issue if the_row() runs before header output).
 */
$category_ids = array();
$content      = get_sub_field( 'content' );
if ( is_array( $content ) && ! empty( $content['categories'] ) ) {
	$cats = $content['categories'];
	if ( ! is_array( $cats ) ) {
		$cats = array( $cats );
	}
	foreach ( $cats as $c ) {
		if ( is_object( $c ) && isset( $c->term_id ) ) {
			$category_ids[] = (int) $c->term_id;
		} elseif ( is_numeric( $c ) ) {
			$category_ids[] = (int) $c;
		}
	}
}
$category_ids = array_values( array_unique( array_filter( $category_ids ) ) );

$news_count = 6;
if ( is_array( $content ) && isset( $content['news_count'] ) && trim( (string) $content['news_count'] ) !== '' ) {
	$news_count = max( 1, (int) $content['news_count'] );
}

/*
 * WordPress prepends sticky posts on top of posts_per_page, so a count of 2
 * with one sticky shows 3. Those stickies take slots inside news_count.
 */
$sticky_ids = array_values( array_filter( array_map( 'intval', (array) get_option( 'sticky_posts' ) ) ) );
if ( $sticky_ids ) {
	$sticky_query_args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'post__in'            => $sticky_ids,
		'posts_per_page'      => count( $sticky_ids ),
		'ignore_sticky_posts' => true,
		'fields'              => 'ids',
		'no_found_rows'       => true,
	);
	if ( ! empty( $category_ids ) ) {
		$sticky_query_args['category__in'] = $category_ids;
	}
	$sticky_ids = array_map( 'intval', get_posts( $sticky_query_args ) );
}

$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$news_anchor = starterkit_get_layout_row_jump_link_id( 'news-hero-' . get_row_index() );

$sticky_slots           = ( $paged <= 1 ) ? min( count( $sticky_ids ), $news_count ) : 0;
$regular_count          = $news_count - $sticky_slots;
$regulars_on_first_page = $news_count - min( count( $sticky_ids ), $news_count );
$offset                 = ( $paged <= 1 ) ? 0 : $regulars_on_first_page + ( $paged - 2 ) * $news_count;

$query_args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => max( 1, $regular_count ),
	'offset'              => $offset,
	'ignore_sticky_posts' => true,
);
if ( $sticky_ids ) {
	$query_args['post__not_in'] = $sticky_ids;
}
if ( ! empty( $category_ids ) ) {
	$query_args['category__in'] = $category_ids;
}

$news_query = new WP_Query( $query_args );

if ( $regular_count < 1 ) {
	$news_query->posts      = array();
	$news_query->post_count = 0;
}

if ( $sticky_slots > 0 ) {
	$sticky_posts = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'post__in'            => array_slice( $sticky_ids, 0, $sticky_slots ),
			'posts_per_page'      => $sticky_slots,
			'ignore_sticky_posts' => true,
		)
	);
	$news_query->posts      = array_merge( $sticky_posts, $news_query->posts );
	$news_query->post_count = count( $news_query->posts );
	$news_query->current_post = -1;
}

$news_query->max_num_pages = (int) ceil( ( (int) $news_query->found_posts + count( $sticky_ids ) ) / $news_count );
?>

<section class="section section--xl section-news-module news-module layout-page-section layout-page-section--news" id="<?php echo esc_attr( $news_anchor ); ?>">
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
		<?php if ( $news_query->have_posts() ) : ?>
			<div class="row module-content g-4">
				<?php
				while ( $news_query->have_posts() ) :
					$news_query->the_post();
					?>
						<?php get_template_part( 'template-parts/teaser-cards/news-teaser-card' ); ?>
					<?php
				endwhile;
				?>
			</div>

			<?php
			$pagination = paginate_links(
				array(
					'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
					'format'       => '?paged=%#%',
					'current'      => max( 1, $paged ),
					'total'        => (int) $news_query->max_num_pages,
					'prev_text'    => '&laquo;',
					'next_text'    => '&raquo;',
					'type'         => 'list',
					'add_fragment' => '#' . $news_anchor,
				)
			);
			if ( $pagination ) {
				echo '<div class="pagination">';
				echo wp_kses_post( $pagination );
				echo '</div>';
			}
			wp_reset_postdata();
			?>
		<?php else : ?>
			<p class="not-found-text txt"><?php esc_html_e( 'Keine News gefunden.', 'starterkit' ); ?></p>
		<?php endif; ?>

	</div>
</section>
