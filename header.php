<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<?php
	$queried_id          = get_queried_object_id();
	$meta_description    = '';
	$meta_title          = wp_get_document_title();
	$current_path        = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$meta_url            = is_singular() && $queried_id ? get_permalink( $queried_id ) : home_url( $current_path );
	$meta_image          = '';
	$meta_image_alt      = '';

	if ( is_singular() && $queried_id ) {
		$meta_description = (string) get_field( 'custom_excerpt', $queried_id );
	}
	if ( $meta_description === '' ) {
		$meta_description = get_bloginfo( 'description' );
	}

	if ( $queried_id && has_post_thumbnail( $queried_id ) ) {
		$meta_image     = (string) get_the_post_thumbnail_url( $queried_id, 'full' );
		$meta_image_alt = (string) get_post_meta( get_post_thumbnail_id( $queried_id ), '_wp_attachment_image_alt', true );
	}
	?>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="description" content="<?php echo esc_attr( $meta_description ); ?>" />
	<meta name="page-topic" content="Dienstleistung">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<link rel="canonical" href="<?php echo esc_url( $meta_url ); ?>">

	<meta property="og:type" content="<?php echo esc_attr( is_singular() ? 'article' : 'website' ); ?>">
	<meta property="og:title" content="<?php echo esc_attr( $meta_title ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $meta_description ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $meta_url ); ?>">
	<meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
	<?php if ( $meta_image !== '' ) : ?>
		<meta property="og:image" content="<?php echo esc_url( $meta_image ); ?>">
		<?php if ( $meta_image_alt !== '' ) : ?>
			<meta property="og:image:alt" content="<?php echo esc_attr( $meta_image_alt ); ?>">
		<?php endif; ?>
	<?php endif; ?>

	<meta name="twitter:card" content="<?php echo esc_attr( $meta_image !== '' ? 'summary_large_image' : 'summary' ); ?>">
	<meta name="twitter:title" content="<?php echo esc_attr( $meta_title ); ?>">
	<meta name="twitter:description" content="<?php echo esc_attr( $meta_description ); ?>">
	<?php if ( $meta_image !== '' ) : ?>
		<meta name="twitter:image" content="<?php echo esc_url( $meta_image ); ?>">
	<?php endif; ?>

	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

<a class="skip-link" href="#main"><?php esc_html_e( 'Zum Inhalt springen', 'starterkit' ); ?></a>

<?php 
// Main navigation
get_template_part( 'template-parts/nav/main-nav' ); ?>

<!-- Special Header text for the homepage -->
<?php
if ( is_front_page() && have_rows( 'header_content_home' ) ) :
	while ( have_rows( 'header_content_home' ) ) :
		the_row();

		$home_header_link = get_sub_field( 'link' );
		$has_home_link    = is_array( $home_header_link ) && ! empty( $home_header_link['url'] );

		$has_left_column =
			get_sub_field( 'subheadline' ) ||
			have_rows( 'titel' );

		$has_right_column =
			get_sub_field( 'intro' ) ||
			$has_home_link;

		if ( ! $has_left_column && ! $has_right_column ) {
			continue;
		}
		?>
<section class="section layout-page-section layout-page-section--layout_c home_spacial_header">
	<div class="container">
		<div class="row g-5">

			<?php if ( $has_left_column ) : ?>
			<div class="col-12 col-lg-8">
				<div class="section-header stack stack--m">
					<?php if ( get_sub_field( 'subheadline' ) ) : ?>
					<p class="section-description">
						<?php the_sub_field( 'subheadline' ); ?>
					</p>
					<?php endif; ?>

					<?php if ( have_rows( 'titel' ) ) : ?>
					<h1 class="h1 section-title">
						<?php
						while ( have_rows( 'titel' ) ) :
							the_row();
							if ( get_row_layout() === 'regular' ) :
								the_sub_field( 'text' );
							elseif ( get_row_layout() === 'strong' ) :
								?>
						<strong><?php the_sub_field( 'text' ); ?></strong>
								<?php
							elseif ( get_row_layout() === 'line_break' ) :
								?>
						<br class="line-break desktop-only">
								<?php
							endif;
						endwhile;
						?>
					</h1>
					<?php endif; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( $has_right_column ) : ?>
			<div class="col-12 col-lg-4 ">
				<div class="section-header stack stack--m">
					<div class="section-intro txt lcnmb">
						<?php if ( get_sub_field( 'intro' ) ) : ?>
							<?php echo wp_kses_post( get_sub_field( 'intro' ) ); ?>
						<?php endif; ?>
						<?php if ( $has_home_link ) : ?>
							<?php if ( get_sub_field( 'intro' ) ) : ?>
							<?php else : ?>
						<p>
							<?php endif; ?>
						<a href="<?php echo esc_url( $home_header_link['url'] ); ?>" class="btn primary arrow-right" target="<?php echo esc_attr( $home_header_link['target'] ?? '_self' ); ?>"><?php echo esc_html( $home_header_link['title'] ?? '' ); ?></a>
							<?php if ( ! get_sub_field( 'intro' ) ) : ?>
						</p>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php endif; ?>

		</div>
	</div>
</section>
		<?php
	endwhile;
endif;
?>

<?php 
// Header thumbnail
if ( is_singular() && ! is_singular( 'jobs' ) && has_post_thumbnail( get_queried_object_id() ) ) : ?>
	<div class="header-thumbnail">
		<?php echo get_the_post_thumbnail( get_queried_object_id(), 'full' ); ?>
	</div>
<?php else : ?>
	<div class="header-no-thumbnail">
		<!-- no thumbnail -->
	</div>
<?php endif; ?>

<?php if ( is_singular( 'jobs' ) ) : ?>
	<div class="header-no-thumbnail">
		<!-- no thumbnail -->
	</div>
<?php endif; ?>

<div class="header-nav-overlay" aria-hidden="true"></div>

<main id="main" tabindex="-1">
