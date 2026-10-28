<?php
/**
 * Jobs "with filters" layout: shared queries, HTML render, Ajax handler.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

/**
 * @return array{kategorie: string, ort: string, beruf: string}
 */
function starterkit_jobs_with_filters_taxonomies() {
	return array(
		'kategorie' => defined( 'AWO_JOB_TAX_KATEGORIE' ) ? AWO_JOB_TAX_KATEGORIE : 'job_kategorie',
		'ort'       => defined( 'AWO_JOB_TAX_ORT' ) ? AWO_JOB_TAX_ORT : 'job_ort',
		'beruf'     => defined( 'AWO_JOB_TAX_BERUFSBEZEICHNUNG' ) ? AWO_JOB_TAX_BERUFSBEZEICHNUNG : 'job_berufsbezeichnung',
	);
}

/**
 * Decode HTML entities in taxonomy term names for plain-text output (e.g. select options).
 *
 * @param string $name Raw term name.
 * @return string
 */
function starterkit_jobs_with_filters_decode_term_name( $name ) {
	if ( ! is_string( $name ) || $name === '' ) {
		return '';
	}

	return wp_specialchars_decode( $name, ENT_QUOTES );
}

/**
 * @return string
 */
function starterkit_jobs_with_filters_empty_html() {
	return starterkit_get_not_found_text_html(
		__( 'Zur Zeit sind in diesem Bereich keine Stellen ausgeschrieben.', 'starterkit' )
	);
}

/**
 * Whether the section has any jobs for the given Kategorie scope (ignores Ort/Beruf).
 *
 * @param WP_Term[] $section_terms  ACF section terms.
 * @param int       $kategorie_id   Active Kategorie term ID or 0.
 * @return bool
 */
function starterkit_jobs_with_filters_section_has_jobs( array $section_terms, $kategorie_id = 0 ) {
	return ! empty(
		starterkit_jobs_with_filters_get_display_post_ids(
			$section_terms,
			0,
			0,
			(int) $kategorie_id
		)
	);
}

/**
 * @param string   $taxonomy   Taxonomy slug.
 * @param int[]    $include_ids Optional term IDs (preserves order via include).
 * @return WP_Term[]
 */
function starterkit_jobs_with_filters_get_dropdown_terms( $taxonomy, array $include_ids = array() ) {
	if ( ! is_string( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$include_ids = starterkit_jobs_with_filters_absint_ids( $include_ids );

	$args = array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	);

	if ( ! empty( $include_ids ) ) {
		$args['include']    = $include_ids;
		$args['orderby']    = 'include';
		$args['hide_empty'] = false;
	}

	$fetched = get_terms( $args );

	return ( is_wp_error( $fetched ) || ! is_array( $fetched ) ) ? array() : $fetched;
}

/**
 * @param int[] $ids Raw IDs.
 * @return int[]
 */
function starterkit_jobs_with_filters_absint_ids( array $ids ) {
	return array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
}

/**
 * @param int[] $category_term_ids Section Kategorie term IDs.
 * @param int   $ort_id            Ort term ID or 0.
 * @param int   $beruf_id          Berufsbezeichnung term ID or 0.
 * @return array<int|string, mixed>
 */
function starterkit_jobs_with_filters_build_section_tax_query( array $category_term_ids, $ort_id, $beruf_id ) {
	$t                = starterkit_jobs_with_filters_taxonomies();
	$category_term_ids = starterkit_jobs_with_filters_absint_ids( $category_term_ids );
	$clauses          = array();

	if ( ! empty( $category_term_ids ) ) {
		$clauses[] = array(
			'taxonomy' => $t['kategorie'],
			'field'    => 'term_id',
			'terms'    => $category_term_ids,
			'operator' => 'IN',
		);
	}

	return starterkit_jobs_with_filters_normalize_tax_query(
		array_merge( $clauses, starterkit_jobs_with_filters_build_optional_tax_clauses( 0, (int) $ort_id, (int) $beruf_id ) )
	);
}

/**
 * @param int $kategorie_id Kategorie term ID or 0.
 * @param int $ort_id       Ort term ID or 0.
 * @param int $beruf_id     Beruf term ID or 0.
 * @return array<int, array<string, mixed>>
 */
function starterkit_jobs_with_filters_build_optional_tax_clauses( $kategorie_id, $ort_id, $beruf_id ) {
	$t       = starterkit_jobs_with_filters_taxonomies();
	$clauses = array();

	if ( (int) $kategorie_id > 0 ) {
		$clauses[] = array(
			'taxonomy' => $t['kategorie'],
			'field'    => 'term_id',
			'terms'    => (int) $kategorie_id,
		);
	}

	if ( (int) $ort_id > 0 ) {
		$clauses[] = array(
			'taxonomy' => $t['ort'],
			'field'    => 'term_id',
			'terms'    => (int) $ort_id,
		);
	}

	if ( (int) $beruf_id > 0 ) {
		$clauses[] = array(
			'taxonomy' => $t['beruf'],
			'field'    => 'term_id',
			'terms'    => (int) $beruf_id,
		);
	}

	return $clauses;
}

/**
 * @param array<int, array<string, mixed>> $clauses Tax clauses.
 * @return array<int|string, mixed>
 */
function starterkit_jobs_with_filters_normalize_tax_query( array $clauses ) {
	$clauses = array_values( array_filter( $clauses ) );
	if ( empty( $clauses ) ) {
		return array();
	}
	if ( count( $clauses ) === 1 ) {
		return array( $clauses[0] );
	}
	return array_merge( array( 'relation' => 'AND' ), $clauses );
}

/**
 * @param array<string, mixed> $overrides WP_Query overrides.
 * @return WP_Query
 */
function starterkit_jobs_with_filters_query_jobs( array $overrides = array() ) {
	$args = array_merge(
		array(
			'post_type'              => 'jobs',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
		),
		$overrides
	);

	return new WP_Query( $args );
}

/**
 * Normalize ACF job_categories field value to term IDs.
 *
 * @param mixed $job_categories Raw ACF value.
 * @return int[]
 */
function starterkit_jobs_with_filters_parse_category_values( $job_categories ) {
	if ( empty( $job_categories ) ) {
		return array();
	}

	$ids  = array();
	$cats = is_array( $job_categories ) ? $job_categories : array( $job_categories );

	foreach ( $cats as $cat ) {
		if ( is_object( $cat ) && isset( $cat->term_id ) ) {
			$ids[] = (int) $cat->term_id;
		} elseif ( is_numeric( $cat ) ) {
			$ids[] = (int) $cat;
		}
	}

	return starterkit_jobs_with_filters_absint_ids( $ids );
}

/**
 * Read the content group for the current jobs_with_filters layout row.
 * Uses get_sub_field( 'content' ) only (no have_rows) so header clone context stays intact.
 *
 * @return array<string, mixed>
 */
function starterkit_jobs_with_filters_get_layout_content() {
	$content = get_sub_field( 'content' );
	return is_array( $content ) ? $content : array();
}

/**
 * Whether the Kategorie filter select should be hidden (ACF content.hide_categorie_select).
 */
function starterkit_jobs_with_filters_hide_categorie_select() {
	$content = starterkit_jobs_with_filters_get_layout_content();
	return ! empty( $content['hide_categorie_select'] );
}

/**
 * Max jobs to show from ACF content.jobs_with_filters_number (0 = unlimited, filters enabled).
 *
 * @return int
 */
function starterkit_jobs_with_filters_get_layout_jobs_limit() {
	$content = starterkit_jobs_with_filters_get_layout_content();
	if ( ! isset( $content['jobs_with_filters_number'] ) || $content['jobs_with_filters_number'] === '' ) {
		return 0;
	}

	return max( 0, (int) $content['jobs_with_filters_number'] );
}

/**
 * Link rows from ACF jobs_with_filters_more_info.jobs_with_filters_more_info_links (layout-level group).
 *
 * @return array<int, array<string, mixed>>
 */
function starterkit_jobs_with_filters_get_more_info_links() {
	$more_info = get_sub_field( 'jobs_with_filters_more_info' );
	$more_info = is_array( $more_info ) ? $more_info : array();
	$rows      = isset( $more_info['jobs_with_filters_more_info_links'] ) && is_array( $more_info['jobs_with_filters_more_info_links'] )
		? $more_info['jobs_with_filters_more_info_links']
		: array();

	if ( empty( $rows ) ) {
		return array();
	}

	$links = array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) || empty( $row['url'] ) || ! is_array( $row['url'] ) || empty( $row['url']['url'] ) ) {
			continue;
		}
		$links[] = $row['url'];
	}

	return $links;
}

/**
 * Parse job_categories from the current jobs_with_filters layout row.
 *
 * @return int[]
 */
function starterkit_jobs_with_filters_get_layout_category_ids() {
	$content = starterkit_jobs_with_filters_get_layout_content();
	if ( empty( $content ) ) {
		return array();
	}

	if ( isset( $content['job_categories'] ) ) {
		return starterkit_jobs_with_filters_parse_category_values( $content['job_categories'] );
	}

	// Legacy repeater-shaped content rows.
	$ids = array();
	foreach ( $content as $row ) {
		if ( ! is_array( $row ) || ! isset( $row['job_categories'] ) ) {
			continue;
		}
		$ids = array_merge( $ids, starterkit_jobs_with_filters_parse_category_values( $row['job_categories'] ) );
	}

	return starterkit_jobs_with_filters_absint_ids( $ids );
}

/**
 * Default Kategorie filter slug from ACF (single category only).
 *
 * @param int[] $category_ids Layout category term IDs.
 * @return string
 */
function starterkit_jobs_with_filters_get_default_kategorie_slug( array $category_ids ) {
	$category_ids = starterkit_jobs_with_filters_absint_ids( $category_ids );

	if ( count( $category_ids ) !== 1 ) {
		return '';
	}

	$tax  = starterkit_jobs_with_filters_taxonomies();
	$term = get_term( $category_ids[0], $tax['kategorie'] );

	return ( $term && ! is_wp_error( $term ) ) ? (string) $term->slug : '';
}

/**
 * @param int[] $job_category_ids Term IDs (order preserved).
 * @return WP_Term[]
 */
function starterkit_jobs_with_filters_terms_from_ids( array $job_category_ids ) {
	$job_category_ids = starterkit_jobs_with_filters_absint_ids( $job_category_ids );

	if ( empty( $job_category_ids ) ) {
		return array();
	}

	$taxonomy = starterkit_jobs_with_filters_taxonomies()['kategorie'];
	$terms    = array();

	foreach ( $job_category_ids as $term_id ) {
		$term = get_term( (int) $term_id, $taxonomy );
		if ( $term instanceof WP_Term && ! is_wp_error( $term ) ) {
			$terms[] = $term;
		}
	}

	return $terms;
}

/**
 * @param WP_Term[] $section_terms  Section terms.
 * @param int       $kategorie_id   Active Kategorie filter or 0.
 * @return WP_Term[]
 */
function starterkit_jobs_with_filters_prepare_section_terms( array $section_terms, $kategorie_id = 0 ) {
	$section_terms = array_values(
		array_filter(
			$section_terms,
			static function ( $term ) {
				return $term instanceof WP_Term;
			}
		)
	);

	if ( (int) $kategorie_id <= 0 || empty( $section_terms ) ) {
		return $section_terms;
	}

	return array_values(
		array_filter(
			$section_terms,
			static function ( $term ) use ( $kategorie_id ) {
				return (int) $term->term_id === (int) $kategorie_id;
			}
		)
	);
}

/**
 * @param string $ort_slug       Sanitized slug.
 * @param string $beruf_slug     Sanitized slug.
 * @param string $kategorie_slug Sanitized slug.
 * @return array{ort_id: int, beruf_id: int, kategorie_id: int}
 */
function starterkit_jobs_with_filters_resolve_slug_ids( $ort_slug, $beruf_slug, $kategorie_slug = '' ) {
	$t = starterkit_jobs_with_filters_taxonomies();

	return array(
		'ort_id'       => ( $ort_slug !== '' && taxonomy_exists( $t['ort'] ) )
			? starterkit_jobs_with_filters_resolve_term_id_by_slug( $ort_slug, $t['ort'] )
			: 0,
		'beruf_id'     => ( $beruf_slug !== '' && taxonomy_exists( $t['beruf'] ) )
			? starterkit_jobs_with_filters_resolve_term_id_by_slug( $beruf_slug, $t['beruf'] )
			: 0,
		'kategorie_id' => ( $kategorie_slug !== '' && taxonomy_exists( $t['kategorie'] ) )
			? starterkit_jobs_with_filters_resolve_term_id_by_slug( $kategorie_slug, $t['kategorie'] )
			: 0,
	);
}

/**
 * @param string $slug     Term slug from request.
 * @param string $taxonomy Taxonomy slug.
 * @return int
 */
function starterkit_jobs_with_filters_resolve_term_id_by_slug( $slug, $taxonomy ) {
	$slug = is_string( $slug ) ? trim( $slug ) : '';
	if ( $slug === '' || ! taxonomy_exists( $taxonomy ) ) {
		return 0;
	}

	$candidates = array_unique(
		array_filter(
			array(
				$slug,
				sanitize_title( $slug ),
				urldecode( $slug ),
				sanitize_title( urldecode( $slug ) ),
			)
		)
	);

	foreach ( $candidates as $candidate ) {
		$term = get_term_by( 'slug', $candidate, $taxonomy );
		if ( $term && ! is_wp_error( $term ) ) {
			return (int) $term->term_id;
		}
	}

	return 0;
}

/**
 * Sort post IDs by ACF section term order; preserve query order within each section.
 *
 * @param int[]     $post_ids      Post IDs (typically date DESC from WP_Query).
 * @param WP_Term[] $section_terms Kategorie terms in display order.
 * @return int[]
 */
function starterkit_jobs_with_filters_order_post_ids_by_sections( array $post_ids, array $section_terms ) {
	$post_ids = starterkit_jobs_with_filters_absint_ids( $post_ids );

	if ( empty( $post_ids ) || empty( $section_terms ) ) {
		return $post_ids;
	}

	$tax           = starterkit_jobs_with_filters_taxonomies();
	$section_order = array();

	foreach ( $section_terms as $index => $term ) {
		if ( $term instanceof WP_Term ) {
			$section_order[ (int) $term->term_id ] = $index;
		}
	}

	if ( empty( $section_order ) ) {
		return $post_ids;
	}

	$post_rank = array();
	foreach ( $post_ids as $position => $post_id ) {
		$post_rank[ (int) $post_id ] = $position;
	}

	$post_terms = wp_get_object_terms(
		$post_ids,
		$tax['kategorie'],
		array(
			'fields'                 => 'all_with_object_id',
			'update_term_meta_cache' => false,
		)
	);

	$section_rank = array();
	if ( ! is_wp_error( $post_terms ) && is_array( $post_terms ) ) {
		foreach ( $post_terms as $row ) {
			if ( ! isset( $row->object_id, $row->term_id ) ) {
				continue;
			}

			$post_id = (int) $row->object_id;
			$term_id = (int) $row->term_id;

			if ( ! isset( $section_order[ $term_id ] ) ) {
				continue;
			}

			if (
				! isset( $section_rank[ $post_id ] )
				|| $section_order[ $term_id ] < $section_rank[ $post_id ]
			) {
				$section_rank[ $post_id ] = $section_order[ $term_id ];
			}
		}
	}

	usort(
		$post_ids,
		static function ( $a, $b ) use ( $section_rank, $post_rank ) {
			$a = (int) $a;
			$b = (int) $b;
			$ra = $section_rank[ $a ] ?? PHP_INT_MAX;
			$rb = $section_rank[ $b ] ?? PHP_INT_MAX;

			if ( $ra !== $rb ) {
				return $ra <=> $rb;
			}

			return ( $post_rank[ $a ] ?? 0 ) <=> ( $post_rank[ $b ] ?? 0 );
		}
	);

	return $post_ids;
}

/**
 * Post IDs that would be rendered (mirrors render_results_html queries).
 *
 * @param WP_Term[] $section_terms ACF section terms (empty = all jobs layout).
 * @param int       $ort_id        Ort term ID or 0.
 * @param int       $beruf_id      Beruf term ID or 0.
 * @param int       $kategorie_id  Kategorie term ID or 0.
 * @param int       $limit         Max posts (newest first across section categories); 0 = no limit.
 * @return int[]
 */
function starterkit_jobs_with_filters_get_display_post_ids( array $section_terms, $ort_id = 0, $beruf_id = 0, $kategorie_id = 0, $limit = 0 ) {
	static $cache = array();

	$limit                  = max( 0, (int) $limit );
	$section_terms_original = $section_terms;
	$had_acf_sections       = ! empty( $section_terms_original );

	if ( $limit > 0 ) {
		$ort_id        = 0;
		$beruf_id      = 0;
		$kategorie_id  = 0;
		$section_terms = $section_terms_original;
	} else {
		$section_terms = starterkit_jobs_with_filters_prepare_section_terms( $section_terms_original, $kategorie_id );
	}

	if ( $had_acf_sections && empty( $section_terms ) ) {
		return array();
	}

	$cache_key = md5(
		wp_json_encode(
			array(
				'sections' => array_map(
					static function ( $term ) {
						return $term instanceof WP_Term ? (int) $term->term_id : 0;
					},
					$section_terms
				),
				'ort'       => (int) $ort_id,
				'beruf'     => (int) $beruf_id,
				'kategorie' => (int) $kategorie_id,
				'limit'     => $limit,
			)
		)
	);

	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	if ( empty( $section_terms ) ) {
		$tax_query = starterkit_jobs_with_filters_normalize_tax_query(
			starterkit_jobs_with_filters_build_optional_tax_clauses( (int) $kategorie_id, (int) $ort_id, (int) $beruf_id )
		);

		$query_args = array(
			'fields'                 => 'ids',
			'update_post_term_cache' => true,
		);

		if ( ! empty( $tax_query ) ) {
			$query_args['tax_query'] = $tax_query;
		}

		$query   = starterkit_jobs_with_filters_query_jobs( $query_args );
		$post_ids = empty( $query->posts ) ? array() : array_values( array_map( 'intval', $query->posts ) );

		if ( $limit > 0 ) {
			$post_ids = array_slice( $post_ids, 0, $limit );
		}
	} else {
		$category_ids = array_map(
			static function ( $term ) {
				return $term instanceof WP_Term ? (int) $term->term_id : 0;
			},
			$section_terms
		);

		$query = starterkit_jobs_with_filters_query_jobs(
			array(
				'fields'                 => 'ids',
				'update_post_term_cache' => true,
				'tax_query'              => starterkit_jobs_with_filters_build_section_tax_query(
					$category_ids,
					(int) $ort_id,
					(int) $beruf_id
				),
			)
		);

		if ( empty( $query->posts ) || ! is_array( $query->posts ) ) {
			$post_ids = array();
		} elseif ( $limit > 0 ) {
			$post_ids = array_slice( array_values( array_map( 'intval', $query->posts ) ), 0, $limit );
		} else {
			$post_ids = starterkit_jobs_with_filters_order_post_ids_by_sections( $query->posts, $section_terms );
		}
	}

	$cache[ $cache_key ] = $post_ids;

	return $post_ids;
}

/**
 * @param int[]  $post_ids Post IDs.
 * @param string $taxonomy Taxonomy slug.
 * @param int[]  $allowed_term_ids Optional allow-list of term IDs.
 * @return array<int, array{slug: string, name: string}>
 */
function starterkit_jobs_with_filters_term_options_from_posts( array $post_ids, $taxonomy, array $allowed_term_ids = array() ) {
	if ( empty( $post_ids ) || ! is_string( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$terms = wp_get_object_terms(
		$post_ids,
		$taxonomy,
		array(
			'update_term_meta_cache' => false,
		)
	);

	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
		return array();
	}

	$allowed = array();
	if ( ! empty( $allowed_term_ids ) ) {
		$allowed = array_flip( array_map( 'absint', $allowed_term_ids ) );
	}

	$by_id = array();
	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		if ( ! empty( $allowed ) && ! isset( $allowed[ (int) $term->term_id ] ) ) {
			continue;
		}
		$by_id[ (int) $term->term_id ] = $term;
	}

	if ( empty( $by_id ) ) {
		return array();
	}

	$terms_sorted = array_values( $by_id );
	usort(
		$terms_sorted,
		static function ( $a, $b ) {
			return strcasecmp( $a->name, $b->name );
		}
	);

	$options = array();
	foreach ( $terms_sorted as $term ) {
		$options[] = array(
			'slug' => $term->slug,
			'name' => starterkit_jobs_with_filters_decode_term_name( $term->name ),
		);
	}

	return $options;
}

/**
 * @param WP_Term[] $section_terms ACF section terms (empty = all jobs).
 * @param int       $ort_id        Active Ort term ID or 0.
 * @param int       $beruf_id      Active Beruf term ID or 0.
 * @param int       $kategorie_id  Active Kategorie term ID or 0.
 * @return array{kategorie: array, ort: array, beruf: array}
 */
function starterkit_jobs_with_filters_get_available_filter_options( array $section_terms, $ort_id, $beruf_id, $kategorie_id ) {
	$t = starterkit_jobs_with_filters_taxonomies();

	$kategorie_allowed = array_map(
		static function ( $term ) {
			return $term instanceof WP_Term ? (int) $term->term_id : 0;
		},
		$section_terms
	);
	$kategorie_allowed = starterkit_jobs_with_filters_absint_ids( $kategorie_allowed );

	$kategorie_posts = starterkit_jobs_with_filters_get_display_post_ids( $section_terms, (int) $ort_id, (int) $beruf_id, 0 );
	$ort_posts       = starterkit_jobs_with_filters_get_display_post_ids( $section_terms, 0, (int) $beruf_id, (int) $kategorie_id );
	$beruf_posts     = starterkit_jobs_with_filters_get_display_post_ids( $section_terms, (int) $ort_id, 0, (int) $kategorie_id );

	return array(
		'kategorie' => starterkit_jobs_with_filters_term_options_from_posts(
			$kategorie_posts,
			$t['kategorie'],
			$kategorie_allowed
		),
		'ort'       => starterkit_jobs_with_filters_term_options_from_posts( $ort_posts, $t['ort'] ),
		'beruf'     => starterkit_jobs_with_filters_term_options_from_posts( $beruf_posts, $t['beruf'] ),
	);
}

/**
 * @param WP_Query $jobs_query Jobs query with posts.
 * @return string
 */
function starterkit_jobs_with_filters_render_jobs_loop_html( WP_Query $jobs_query ) {
	if ( ! $jobs_query->have_posts() ) {
		return '';
	}

	ob_start();
	?>
	<div class="row module-content jobs-with-filters-module__category-group g-4">
		<?php
		while ( $jobs_query->have_posts() ) :
			$jobs_query->the_post();
			get_template_part( 'template-parts/teaser-cards/job-teaser-card' );
		endwhile;
		wp_reset_postdata();
		?>
	</div>
	<?php
	return trim( (string) ob_get_clean() );
}

/**
 * @param WP_Term[] $terms               Kategorie terms in display order.
 * @param int       $filter_ort_id       Resolved Ort term ID or 0.
 * @param int       $filter_beruf_id     Resolved Beruf term ID or 0.
 * @param int       $filter_kategorie_id Resolved Kategorie term ID or 0.
 * @param int       $limit               Max posts (newest first); 0 = no limit.
 * @return string
 */
function starterkit_jobs_with_filters_render_results_html( array $terms, $filter_ort_id, $filter_beruf_id, $filter_kategorie_id = 0, $limit = 0 ) {
	$limit            = max( 0, (int) $limit );
	$had_acf_sections = ! empty( $terms );

	if ( $limit > 0 ) {
		$filter_kategorie_id = 0;
	} else {
		$terms = starterkit_jobs_with_filters_prepare_section_terms( $terms, $filter_kategorie_id );
	}

	if ( $had_acf_sections && empty( $terms ) ) {
		return starterkit_jobs_with_filters_empty_html();
	}

	$post_ids = starterkit_jobs_with_filters_get_display_post_ids(
		$terms,
		(int) $filter_ort_id,
		(int) $filter_beruf_id,
		(int) $filter_kategorie_id,
		$limit
	);

	if ( empty( $post_ids ) ) {
		return starterkit_jobs_with_filters_empty_html();
	}

	return starterkit_jobs_with_filters_render_jobs_loop_html(
		starterkit_jobs_with_filters_query_jobs(
			array(
				'post__in' => $post_ids,
				'orderby'  => 'post__in',
			)
		)
	);
}

/**
 * @param array<int> $category_ids Layout category term IDs.
 * @param string     $ort_slug     Ort slug.
 * @param string     $beruf_slug   Beruf slug.
 * @param string     $kategorie_slug Kategorie slug.
 * @return array{html: string, filterOptions: array, showFilters: bool}
 */
function starterkit_jobs_with_filters_build_response( array $category_ids, $ort_slug, $beruf_slug, $kategorie_slug ) {
	$terms    = starterkit_jobs_with_filters_terms_from_ids( $category_ids );
	$resolved = starterkit_jobs_with_filters_resolve_slug_ids( $ort_slug, $beruf_slug, $kategorie_slug );

	return array(
		'html'          => starterkit_jobs_with_filters_render_results_html(
			$terms,
			$resolved['ort_id'],
			$resolved['beruf_id'],
			$resolved['kategorie_id']
		),
		'filterOptions' => starterkit_jobs_with_filters_get_available_filter_options(
			$terms,
			$resolved['ort_id'],
			$resolved['beruf_id'],
			$resolved['kategorie_id']
		),
		'showFilters'   => starterkit_jobs_with_filters_section_has_jobs( $terms, $resolved['kategorie_id'] ),
	);
}

/**
 * @param string $raw Comma-separated IDs.
 * @return int[]
 */
function starterkit_jobs_with_filters_parse_category_ids( $raw ) {
	$parts = $raw !== '' ? explode( ',', (string) $raw ) : array();
	$ids   = array();

	foreach ( $parts as $part ) {
		$id = absint( trim( $part ) );
		if ( $id > 0 ) {
			$ids[] = $id;
		}
		if ( count( $ids ) >= 100 ) {
			break;
		}
	}

	return $ids;
}

/**
 * Ajax: return rendered job listing HTML for jobs_with_filters layout.
 */
function starterkit_jobs_filters_ajax() {
	if ( ! check_ajax_referer( 'starterkit_jobs_filters', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'bad_nonce' ), 403 );
	}

	$category_ids = starterkit_jobs_with_filters_parse_category_ids(
		isset( $_POST['category_ids'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['category_ids'] ) ) : ''
	);

	$ort_slug       = isset( $_POST['job_ort'] ) ? sanitize_title( wp_unslash( (string) $_POST['job_ort'] ) ) : '';
	$beruf_slug     = isset( $_POST['job_beruf'] ) ? sanitize_title( wp_unslash( (string) $_POST['job_beruf'] ) ) : '';
	$kategorie_slug = isset( $_POST['job_kategorie'] ) ? sanitize_title( wp_unslash( (string) $_POST['job_kategorie'] ) ) : '';

	$teaser_headline_tag = isset( $_POST['job_teaser_headline_tag'] ) ? sanitize_key( wp_unslash( (string) $_POST['job_teaser_headline_tag'] ) ) : 'h3';
	starterkit_set_job_teaser_headline_context( $teaser_headline_tag );

	wp_send_json_success(
		starterkit_jobs_with_filters_build_response( $category_ids, $ort_slug, $beruf_slug, $kategorie_slug )
	);
}
add_action( 'wp_ajax_starterkit_jobs_filters', 'starterkit_jobs_filters_ajax' );
add_action( 'wp_ajax_nopriv_starterkit_jobs_filters', 'starterkit_jobs_filters_ajax' );
