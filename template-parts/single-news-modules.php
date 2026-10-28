<?php
/**
 * Article-only flexible modules (ACF field `modules_news` on single posts).
 * Each layout is rendered from `template-parts/modules-news/{layout}/{layout}.php`.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

if ( ! have_rows( 'modules_news' ) ) {
	return;
}
?>
<div class="single-news-modules stack stack--m">
	<?php
	while ( have_rows( 'modules_news' ) ) :
		the_row();
		$layout = get_row_layout();
		if ( ! is_string( $layout ) || $layout === '' ) {
			continue;
		}
		$part = 'template-parts/modules-news/' . $layout . '/' . $layout;
		if ( locate_template( $part . '.php', false, false ) ) {
			get_template_part( $part );
		}
	endwhile;
	?>
</div>
