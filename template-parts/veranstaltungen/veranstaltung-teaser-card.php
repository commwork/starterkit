<?php
/**
 * Veranstaltung teaser card (news-style layout).
 *
 * @package Baukasten
 *
 * @var array $args {
 *     @type int    $post_id    Veranstaltung post ID.
 *     @type string $grid_class Bootstrap column classes for the card wrapper.
 * }
 */

defined( 'ABSPATH' ) || exit;

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$permalink   = get_permalink( $post_id );
$date_label      = starterkit_veranstaltung_get_date_label( $post_id );
$time_label      = starterkit_veranstaltung_get_time_label( $post_id );
$location_label  = starterkit_veranstaltung_get_location_label( $post_id );
$cat_label       = starterkit_veranstaltung_get_category_label( $post_id );
$icon_uri        = get_template_directory_uri() . '/assets/images/icons/24/';

$content_raw = (string) get_post_field( 'post_content', $post_id );
$excerpt     = '' !== trim( wp_strip_all_tags( $content_raw ) )
	? wp_trim_words( wp_strip_all_tags( $content_raw ), 24 )
	: '';

$grid_class = isset( $args['grid_class'] ) && '' !== trim( (string) $args['grid_class'] )
	? (string) $args['grid_class']
	: 'col-12 col-lg-6';
?>

<article class="<?php echo esc_attr( $grid_class ); ?> post-item post-item--veranstaltung" id="veranstaltung-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php if ( $permalink ) : ?>
		<a href="<?php echo esc_url( $permalink ); ?>" class="wrapper-link post-item-content box box--s">
	<?php else : ?>
		<div class="post-item-content box box--s">
	<?php endif; ?>

		<div class="post-item-image">
			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<?php
				echo get_the_post_thumbnail(
					$post_id,
					'img-square-large',
					array(
						'sizes' => '(max-width: 767px) 100vw, (max-width: 1399px) 50vw, 33vw',
					)
				);
				?>
			<?php else : ?>
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/news-post-thumb-placeholder.png' ); ?>" alt="" loading="lazy" decoding="async" />
			<?php endif; ?>
		</div>

		<div class="stack">
			<div class="h4">
				<?php echo esc_html( get_the_title( $post_id ) ); ?>
			</div>

			<?php if ( '' !== $date_label || '' !== $time_label || '' !== $location_label || '' !== $cat_label ) : ?>
				<div class="veranstaltung-meta stack stack--xs">
					<?php if ( '' !== $date_label ) : ?>
						<p class="meta veranstaltung-meta__date">
							<span class="veranstaltung-meta__icon" aria-hidden="true">
								<img src="<?php echo esc_url( $icon_uri . 'calendar.svg' ); ?>" alt="" width="24" height="24" loading="lazy" decoding="async" />
							</span>
							<?php echo esc_html( $date_label ); ?>
						</p>
					<?php endif; ?>
					<?php if ( '' !== $time_label ) : ?>
						<p class="meta veranstaltung-meta__time">
							<span class="veranstaltung-meta__icon" aria-hidden="true">
								<img src="<?php echo esc_url( $icon_uri . 'clock.svg' ); ?>" alt="" width="24" height="24" loading="lazy" decoding="async" />
							</span>
							<?php echo esc_html( $time_label ); ?>
						</p>
					<?php endif; ?>
					<?php if ( '' !== $location_label ) : ?>
						<p class="meta veranstaltung-meta__location">
							<span class="veranstaltung-meta__icon" aria-hidden="true">
								<img src="<?php echo esc_url( $icon_uri . 'map-pin.svg' ); ?>" alt="" width="24" height="24" loading="lazy" decoding="async" />
							</span>
							<?php echo esc_html( $location_label ); ?>
						</p>
					<?php endif; ?>
					<?php /* if ( '' !== $cat_label ) : ?>
						<p class="meta veranstaltung-meta__category">
							<span class="veranstaltung-meta__icon" aria-hidden="true">
								<img src="<?php echo esc_url( $icon_uri . 'globe.svg' ); ?>" alt="" width="24" height="24" loading="lazy" decoding="async" />
							</span>
							<?php echo esc_html( $cat_label ); ?>
						</p>
					<?php endif; */ ?>
				</div>
			<?php endif; ?>

			<?php if ( '' !== $excerpt ) : ?>
				<div>
					<?php echo esc_html( $excerpt ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $permalink ) : ?>
				<span class="btn primary arrow-right">
					<?php esc_html_e( 'Zur Veranstaltung', 'starterkit' ); ?>
				</span>
			<?php endif; ?>
		</div>

	<?php if ( $permalink ) : ?>
		</a>
	<?php else : ?>
		</div>
	<?php endif; ?>
</article>
