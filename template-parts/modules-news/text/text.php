<?php
/**
 * News single module: `text` (ACF `modules_news` row).
 * Isolated from page `modules_content` templates.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

if ( get_row_layout() !== 'text' ) {
	return;
}

if ( ! get_sub_field( 'headline' ) && ! get_sub_field( 'text' ) ) {
	return;
}
?>
<div class="row single-news-text single-news-module-content stack-row stack-row--m">
	<div class="col-12 single-news-text__inner stack stack--s txt lcnmb">
		<?php if ( get_sub_field( 'headline' ) ) : ?>
			<h2 class="single-news-text__headline h3">
				<?php the_sub_field( 'headline' ); ?>
			</h2>
		<?php endif; ?>
		
		<?php if ( get_sub_field( 'text' ) ) : ?>
			<div class="single-news-text__body">
				<?php the_sub_field( 'text' ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
