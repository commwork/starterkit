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
 * Layout `dynamic_modules` (`section_layout` a/b/c): header + modules columns.
 * Layout `chronicle`: full-width chronicle hero + timeline (`heroes/chronicle/layout-chronicle.php`, no modules_content).
 * Layout `numbers`: numbers hero (`heroes/numbers/layout-numbers.php`, no modules_content).
 * Layout `news`: Aktuelles hero (`heroes/news/layout-news.php`, no modules_content).
 * Layout `news_slider`: Aktuelles slider hero (`heroes/news_slider/layout-news_slider.php`, no modules_content).
 * Layout `teasers_subpages`: Teaser Unterseiten hero (`heroes/teasers_subpages/layout-teasers_subpages.php`, no modules_content).
 * Layout `process`: Process hero (`heroes/process/layout-process.php`, no modules_content).
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

		$module_options = array();

		if ( $layout_name === 'dynamic_modules' ) {
			$module_options = get_sub_field( 'dynamic_modules_options' );
			if ( ! is_array( $module_options ) ) {
				$module_options = array();
			}

			// a: header above content. b: header right. c: header left.
			$section_layout = (string) ( $module_options['section_layout'] ?? '' );
			if ( ! in_array( $section_layout, array( 'a', 'b', 'c' ), true ) ) {
				$section_layout = 'a';
			}

			$section_classes[] = 'layout-page-section--' . $section_layout;

			if ( $section_layout === 'b' ) {
				$container_class = 'container';
				$cols['header']  = 'col-12 col-xl-4 order-xl-2';
				$cols['content'] = 'col-12 col-xl-8 order-xl-1';
			} elseif ( $section_layout === 'c' ) {
				$container_class = 'container';
				$cols['header']  = 'col-12 col-xl-4';
				$cols['content'] = 'col-12 col-xl-8';
			}
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
