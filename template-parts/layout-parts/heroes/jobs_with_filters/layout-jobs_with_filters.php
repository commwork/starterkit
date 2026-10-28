<?php
/**
 * Layout "jobs_with_filters": hero with header clone + job teasers grouped by selected categories (no modules_content).
 * ACF: layout `jobs_with_filters` — `header` (clone), `content` (group) with `job_categories`
 * (taxonomy `job_kategorie`), optional `jobs_with_filters_number` (newest N jobs, hides filters).
 *
 * Kategorie / Ort / Berufsbezeichnung: filtered via Ajax (see jobs-filters.js). Initial Kategorie follows a single ACF `job_categories` choice, or URL `job_kategorie` when set.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

if ( get_row_layout() !== 'jobs_with_filters' ) {
	return;
}

$job_category_ids         = starterkit_jobs_with_filters_get_layout_category_ids();
$hide_categorie_select    = starterkit_jobs_with_filters_hide_categorie_select();
$jobs_limit               = starterkit_jobs_with_filters_get_layout_jobs_limit();
$more_info_links          = starterkit_jobs_with_filters_get_more_info_links();
$default_kategorie_slug   = starterkit_jobs_with_filters_get_default_kategorie_slug( $job_category_ids );

$terms = starterkit_jobs_with_filters_terms_from_ids( $job_category_ids );

$filter_kategorie_slug = isset( $_GET['job_kategorie'] ) ? sanitize_title( wp_unslash( (string) $_GET['job_kategorie'] ) ) : '';
$filter_ort_slug       = isset( $_GET['job_ort'] ) ? sanitize_title( wp_unslash( (string) $_GET['job_ort'] ) ) : '';
$filter_beruf_slug     = isset( $_GET['job_beruf'] ) ? sanitize_title( wp_unslash( (string) $_GET['job_beruf'] ) ) : '';

// Pre-select ACF Kategorie when exactly one is configured and URL has no override.
if ( $filter_kategorie_slug === '' && $default_kategorie_slug !== '' ) {
	$filter_kategorie_slug = $default_kategorie_slug;
}

$has_active_filters = $hide_categorie_select
	? ( $filter_ort_slug !== '' || $filter_beruf_slug !== '' )
	: ( $filter_kategorie_slug !== '' || $filter_ort_slug !== '' || $filter_beruf_slug !== '' );

$resolved            = starterkit_jobs_with_filters_resolve_slug_ids( $filter_ort_slug, $filter_beruf_slug, $filter_kategorie_slug );
$filter_ort_id       = $resolved['ort_id'];
$filter_beruf_id     = $resolved['beruf_id'];
$filter_kategorie_id = $resolved['kategorie_id'];

$uid = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'jobs-filters-' ) : uniqid( 'jobs-filters-', false );

$filter_options = starterkit_jobs_with_filters_get_available_filter_options(
	$terms,
	$filter_ort_id,
	$filter_beruf_id,
	$filter_kategorie_id
);

$initial_kategorie_id = count( $job_category_ids ) === 1 ? (int) $job_category_ids[0] : 0;
$initial_filter_options = starterkit_jobs_with_filters_get_available_filter_options(
	$terms,
	0,
	0,
	$initial_kategorie_id
);

$show_filters = $jobs_limit > 0
	? false
	: starterkit_jobs_with_filters_section_has_jobs( $terms, $filter_kategorie_id );

if ( starterkit_header_raw_has_titel() ) {
	$section_tag_peek        = starterkit_section_titel_heading_tag( false );
	$job_teaser_headline_tag = $section_tag_peek === 'h1' ? 'h2' : 'h3';
} else {
	$job_teaser_headline_tag = 'h3';
}
starterkit_set_job_teaser_headline_context( $job_teaser_headline_tag );

$jobs_filters_ajax_config = array(
	'ajaxUrl'              => admin_url( 'admin-ajax.php' ),
	'nonce'                => wp_create_nonce( 'starterkit_jobs_filters' ),
	'action'               => 'starterkit_jobs_filters',
	'categoryIds'          => array_values( array_map( 'intval', $job_category_ids ) ),
	'defaultKategorieSlug'  => $default_kategorie_slug,
	'hideKategorieSelect'   => $hide_categorie_select,
	'initialFilterOptions'  => $initial_filter_options,
	'showFilters'           => $show_filters,
	'jobTeaserHeadlineTag'  => $job_teaser_headline_tag,
	'filterLabels'         => array(
		'kategorie' => __( 'Alle Kategorien', 'starterkit' ),
		'ort'       => __( 'Alle Standorte', 'starterkit' ),
		'beruf'     => __( 'Alle Berufsbezeichnungen', 'starterkit' ),
	),
);
?>

<section
	class="section section--xl section-jobs-with-filters jobs-with-filters-module jobs-teasers layout-page-section layout-page-section--jobs_with_filters"<?php echo starterkit_layout_row_jump_link_id_attr(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-jobs-filters-config="<?php echo esc_attr( wp_json_encode( $jobs_filters_ajax_config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ) ); ?>"
>
	<div class="container stack stack--l">

		<?php if ( starterkit_layout_row_has_header_content() ) : ?>
			<div class="row">
				<div class="col-12 col-lg-8 stack stack--m">
					<?php get_template_part( 'template-parts/layout-parts/layout-row-header' ); ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $show_filters ) : ?>
		<div class="row module-content jobs-with-filters-module__filters" id="jobs-filters">
			<div class="col-12">
				<form class="jobs-with-filters-module__form" action="#" method="post" novalidate>
					<div class="jobs-with-filters-module__fields">
						<div class="jobs-with-filters-module__field">
							<label class="jobs-with-filters-module__label" for="<?php echo esc_attr( $uid ); ?>-ort"><?php esc_html_e( 'Standort', 'starterkit' ); ?></label>
							<select class="jobs-with-filters-module__select" id="<?php echo esc_attr( $uid ); ?>-ort" data-jobs-filter="ort">
								<option value=""><?php esc_html_e( 'Alle Standorte', 'starterkit' ); ?></option>
								<?php foreach ( $filter_options['ort'] as $ort_option ) : ?>
									<option value="<?php echo esc_attr( $ort_option['slug'] ); ?>"<?php selected( $filter_ort_slug, $ort_option['slug'] ); ?>>
										<?php echo esc_html( $ort_option['name'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="jobs-with-filters-module__field">
							<label class="jobs-with-filters-module__label" for="<?php echo esc_attr( $uid ); ?>-beruf"><?php esc_html_e( 'Berufsbezeichnung', 'starterkit' ); ?></label>
							<select class="jobs-with-filters-module__select" id="<?php echo esc_attr( $uid ); ?>-beruf" data-jobs-filter="beruf">
								<option value=""><?php esc_html_e( 'Alle Berufsbezeichnungen', 'starterkit' ); ?></option>
								<?php foreach ( $filter_options['beruf'] as $beruf_option ) : ?>
									<option value="<?php echo esc_attr( $beruf_option['slug'] ); ?>"<?php selected( $filter_beruf_slug, $beruf_option['slug'] ); ?>>
										<?php echo esc_html( $beruf_option['name'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="jobs-with-filters-module__field<?php echo $hide_categorie_select ? ' jobs-with-filters-module__field--hidden' : ''; ?>"<?php echo $hide_categorie_select ? ' hidden' : ''; ?>>
							<label class="jobs-with-filters-module__label" for="<?php echo esc_attr( $uid ); ?>-kategorie"><?php esc_html_e( 'Kategorien', 'starterkit' ); ?></label>
							<select class="jobs-with-filters-module__select" id="<?php echo esc_attr( $uid ); ?>-kategorie" data-jobs-filter="kategorie"<?php echo $hide_categorie_select ? ' aria-hidden="true" tabindex="-1"' : ''; ?>>
								<option value=""><?php esc_html_e( 'Alle Kategorien', 'starterkit' ); ?></option>
								<?php foreach ( $filter_options['kategorie'] as $kategorie_option ) : ?>
									<option value="<?php echo esc_attr( $kategorie_option['slug'] ); ?>"<?php selected( $filter_kategorie_slug, $kategorie_option['slug'] ); ?>>
										<?php echo esc_html( $kategorie_option['name'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>			

						<div class="jobs-with-filters-module__actions"<?php echo $has_active_filters ? '' : ' hidden'; ?>>
							<button type="button" class="btn secondary jobs-with-filters-module__reset" data-jobs-filter-reset>
								<?php esc_html_e( 'Filter zurücksetzen', 'starterkit' ); ?>
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php endif; ?>

		<div class="jobs-with-filters-module__results" aria-live="polite">
			<?php
			echo starterkit_jobs_with_filters_render_results_html( $terms, $filter_ort_id, $filter_beruf_id, $filter_kategorie_id, $jobs_limit );
			?>
		</div>

		<?php if ( ! empty( $more_info_links ) ) : ?>
		<div class="row module-content jobs-with-filters-module__more-info">
			<div class="col-12">
				<div class="stack stack--s">
					<?php foreach ( $more_info_links as $link ) : ?>
						<a href="<?php echo esc_url( $link['url'] ); ?>" class="btn primary arrow-right" target="<?php echo esc_attr( $link['target'] ?? '_self' ); ?>"><?php echo esc_html( $link['title'] ?? '' ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>

	</div>
</section>
