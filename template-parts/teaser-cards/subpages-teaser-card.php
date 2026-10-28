<?php
/**
 * Subpage teaser card for teasers_subpages layout / modules.
 *
 * @package Baukasten
 *
 * @var array<string, mixed> $args Template args (`subpage_id`).
 */

defined( 'ABSPATH' ) || exit;

$post_id = 0;
if ( isset( $args['subpage_id'] ) ) {
	$post_id = (int) $args['subpage_id'];
} elseif ( isset( $subpage_id ) ) {
	$post_id = (int) $subpage_id;
}

if ( ! $post_id ) {
	return;
}

$url   = get_permalink( $post_id );
$title = get_the_title( $post_id );

if ( ! $url || ! $title ) {
	return;
}

$excerpt_source = get_field( 'custom_excerpt', $post_id );
if ( ! $excerpt_source ) {
	$excerpt_source = get_post_field( 'post_excerpt', $post_id );
}
if ( ! $excerpt_source ) {
	$excerpt_source = get_post_field( 'post_content', $post_id );
}
?>

<article class="col-12 col-md-6 col-xxl-4 post-item">
	<a href="<?php echo esc_url( $url ); ?>" class="wrapper-link post-item-content box box--s">
		<?php if ( has_post_thumbnail( $post_id ) ) : ?>
			<div class="post-item-image">
				<?php echo get_the_post_thumbnail( $post_id, 'img-square' ); ?>
			</div>
		<?php else : ?>
			<div class="post-item-image">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/news-post-thumb-placeholder.png' ); ?>" alt="<?php echo esc_attr( $title ); ?>">
			</div>
		<?php endif; ?>

		<div class="stack">
			<div class="h4">
				<?php echo esc_html( $title ); ?>
			</div>

			<div>
				<?php echo wp_kses_post( wp_trim_words( wp_strip_all_tags( (string) $excerpt_source ), 24 ) ); ?>
			</div>

			<span class="btn primary arrow-right">
				<?php esc_html_e( 'Mehr erfahren', 'starterkit' ); ?>
			</span>
		</div>
	</a>
</article>
