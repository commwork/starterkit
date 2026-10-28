<?php
/**
 * Flexible page body: top-level ACF field `layouts` with header + modules columns,
 * optional per-layout container classes (see `$starterkit_layout_columns['container']`),
 * or legacy top-level flexible field `modules`.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

/**
 * Layout `dynamic_modules` (choose_layout: a/b/c): header + modules columns.
 * Layout `chronicle`: full-width chronicle hero + timeline (`heroes/chronicle/layout-chronicle.php`, no modules_content).
 * Layout `numbers`: numbers hero (`heroes/numbers/layout-numbers.php`, no modules_content).
 * Layout `news`: Aktuelles hero (`heroes/news/layout-news.php`, no modules_content).
 * Layout `news_slider`: Aktuelles slider hero (`heroes/news_slider/layout-news_slider.php`, no modules_content).
 * Layout `teasers_subpages`: Teaser Unterseiten hero (`heroes/teasers_subpages/layout-teasers_subpages.php`, no modules_content).
 * Layout `process`: Process hero (`heroes/process/layout-process.php`, no modules_content).
 * Layout `jobs_with_filters`: Jobs hero + category links (`heroes/jobs_with_filters/layout-jobs_with_filters.php`, no modules_content).
 * Layout `veranstaltungen_hero`: Veranstaltungen hero (`heroes/events_hero/layout-veranstaltungen_hero.php`, no modules_content).
 */

if ( have_rows( 'layouts' ) ) :
	$starterkit_layout_columns = array(
		'dynamic_modules' => array(
			'header'    => 'col-12 col-lg-12',
			'content'   => 'col-12 col-lg-12',
			'container' => 'container container-narrow',
		),
	);

	while ( have_rows( 'layouts' ) ) :
		the_row();

		$layout_name = get_row_layout();

		/**
		 * /////////////////////////////////////////////////
		 *  Hero elements
		 * /////////////////////////////////////////////////
		 */
		
		 // Chronicle
		if ( $layout_name === 'chronicle' ) {
			get_template_part( 'template-parts/layout-parts/heroes/chronicle/layout-chronicle' );
			continue;
		}

		// Numbers
		if ( $layout_name === 'numbers' ) {
			get_template_part( 'template-parts/layout-parts/heroes/numbers/layout-numbers' );
			continue;
		}

		// News
		if ( $layout_name === 'news' ) {
			get_template_part( 'template-parts/layout-parts/heroes/news/layout-news' );
			continue;
		}

		// News slider
		if ( $layout_name === 'news_slider' ) {
			get_template_part( 'template-parts/layout-parts/heroes/news_slider/layout-news_slider' );
			continue;
		}

		// Teasers subpages
		if ( $layout_name === 'teasers_subpages' ) {
			get_template_part( 'template-parts/layout-parts/heroes/teasers_subpages/layout-teasers_subpages' );
			continue;
		}

		// Process
		if ( $layout_name === 'process' ) {
			get_template_part( 'template-parts/layout-parts/heroes/process/layout-process' );
			continue;
		}

		// Jobs with filters (job_kategorie)
		if ( $layout_name === 'jobs_with_filters' ) {
			get_template_part( 'template-parts/layout-parts/heroes/jobs_with_filters/layout-jobs_with_filters' );
			continue;
		}

		// Veranstaltungen hero
		if ( $layout_name === 'veranstaltungen_hero' ) {
			get_template_part( 'template-parts/layout-parts/heroes/events_hero/layout-veranstaltungen_hero' );
			continue;
		}

		/**
		 * /////////////////////////////////////////////////
		 *  Regular layouts
		 * /////////////////////////////////////////////////
		 */
		if ( ! isset( $starterkit_layout_columns[ $layout_name ] ) ) {
			continue;
		}

		$cols = $starterkit_layout_columns[ $layout_name ];
		$container_class = isset( $cols['container'] ) ? $cols['container'] : 'container';

		$section_classes = array(
			'section',
			'layout-page-section',
			'layout-page-section--' . $layout_name,
		);

		if ( $layout_name === 'dynamic_modules' ) {
			$choose_layout = (string) get_sub_field( 'choose_layout' );
			if ( ! in_array( $choose_layout, array( 'layout_a', 'layout_b', 'layout_c' ), true ) ) {
				$choose_layout = 'layout_a';
			}

			$section_classes[] = 'layout-page-section--' . $choose_layout;

			if ( in_array( $choose_layout, array( 'layout_b', 'layout_c' ), true ) ) {
				$container_class = 'container';

				if ( $choose_layout === 'layout_b' ) {
					$cols['header']  = 'col-12 col-lg-4';
					$cols['content'] = 'col-12 col-lg-8';
				} elseif ( $choose_layout === 'layout_c' ) {
					$cols['header'] = 'col-12 col-lg-8';
				}
			}
		}

		$module_options  = get_sub_field( 'dynamic_modules_options' );
		$section_bgcolor = '';

		if ( is_array( $module_options ) ) {
			$section_bgcolor = (string) ( $module_options['section_bgcolor'] ?? '' );
		}

		if ( in_array( $section_bgcolor, array( 'colorset-1', 'colorset-2' ), true ) ) {
			$section_classes[] = $section_bgcolor;
		}
		?>
		<section class="<?php echo esc_attr( implode( ' ', $section_classes ) ); ?>"<?php echo starterkit_layout_row_jump_link_id_attr(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="<?php echo esc_attr( $container_class ); ?>">
				<div class="row layout-page-row align-items-start g-5">

					<!-- Header -->
					<?php if ( starterkit_layout_row_has_header_content() ) : ?>
						<div class="<?php echo esc_attr( $cols['header'] ); ?> layout-page-col layout-page-col--header">
							<?php get_template_part( 'template-parts/layout-parts/layout-row-header' ); ?>
						</div>
					<?php endif; ?>

					<!-- Modules (raw value only — formatting before have_rows breaks disabled-layout row indices).
					 More info: https://github.com/AdvancedCustomFields/acf/issues/994 -->
					<?php if ( ! empty( get_sub_field( 'modules_content', false ) ) ) : ?>
						<div class="<?php echo esc_attr( $cols['content'] ); ?> layout-page-col layout-page-col--modules">
							<div class="layout-modules">
								<?php get_template_part( 'template-parts/layout-parts/layout-row-modules' ); ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	endwhile;

elseif ( have_rows( 'modules' ) ) :

	echo '<div class="layout-modules">';
	starterkit_render_modules_loop( 'modules' );
	echo '</div>';

endif;
