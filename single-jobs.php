<?php
/**
 * Template for displaying single job post
 *
 * @package starterkit
 */

get_header();

while ( have_posts() ) :
	the_post();

	$location      = get_post_meta( get_the_ID(), '_job_location', true );
	$range         = get_post_meta( get_the_ID(), '_job_range', true );
	$selection1    = get_post_meta( get_the_ID(), '_job_selection1', true );
	$selection2    = get_post_meta( get_the_ID(), '_job_selection2', true );
	$selection4    = get_post_meta( get_the_ID(), '_job_selection4', true );
	$external_url  = get_post_meta( get_the_ID(), '_job_external_url', true );
	$short_name    = get_the_excerpt();

	$hide_tarif_link_for_categories = get_field( 'link_tarifinformationen_anzeigen', 'option' );
	$show_tarifinformationen_link   = true;

	if ( ! empty( $hide_tarif_link_for_categories ) ) {
		$hide_tarif_link_for_categories = array_map( 'intval', (array) $hide_tarif_link_for_categories );
		$job_category_ids               = wp_get_post_terms( get_the_ID(), 'job_kategorie', array( 'fields' => 'ids' ) );

		if ( ! is_wp_error( $job_category_ids ) && array_intersect( $hide_tarif_link_for_categories, $job_category_ids ) ) {
			$show_tarifinformationen_link = false;
		}
	}
	?>

<section class="section section--xl single-job">
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-job__container' ); ?>>
		<header class="entry-header stack stack--m">
			<div class="container">
				<div class="section-header stack stack--m">
					<div class="entry-header-back">
						<a href="<?php echo esc_url( home_url( '/karriere/stellenangebote/aktuelle-stellenangebote/' ) ); ?>" class="btn secondary">
							<?php esc_html_e( 'Zurück zur Stellenangebote-Übersicht', 'starterkit' ); ?>
						</a>
					</div>

					<h1 class="h2 entry-title">
						<?php the_title(); ?>
					</h1>

					<?php if ( $short_name ) : ?>
						<p class="h4 job-short-name"><?php echo esc_html( $short_name ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</header>

		<div class="container">
			<div class="row g-3 single-job__intro">
				<div class="col-12 col-lg-4">
					<aside class="job-meta-info box" aria-label="<?php esc_attr_e( 'Stelleninformationen', 'starterkit' ); ?>">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/arrow-right.svg' ); ?>" alt="" class="icon-arrow-right" width="20" height="20" aria-hidden="true">

						<ul class="job-meta-list">
							<?php if ( $location ) : ?>
								<li><strong><?php esc_html_e( 'Standort:', 'starterkit' ); ?></strong>&nbsp;<span><?php echo esc_html( $location ); ?></span></li>
							<?php endif; ?>

							<?php if ( $range ) : ?>
								<li><strong><?php esc_html_e( 'Arbeitszeit:', 'starterkit' ); ?></strong>&nbsp;<span><?php echo esc_html( $range ); ?></span></li>
							<?php endif; ?>

							<?php if ( $selection1 ) : ?>
								<li><strong><?php esc_html_e( 'Bereich:', 'starterkit' ); ?></strong>&nbsp;<span><?php echo esc_html( $selection1 ); ?></span></li>
							<?php endif; ?>

							<?php if ( $selection2 ) : ?>
								<li><strong><?php esc_html_e( 'Berufsbezeichnung:', 'starterkit' ); ?></strong>&nbsp;<span><?php echo esc_html( $selection2 ); ?></span></li>
							<?php endif; ?>

							<?php if ( $selection4 ) : ?>
								<li><strong><?php esc_html_e( 'Bewerbungsfrist:', 'starterkit' ); ?></strong>&nbsp;<span><?php echo esc_html( $selection4 ); ?></span></li>
							<?php endif; ?>
						</ul>
					</aside>
				</div>

				<div class="col-12 col-lg-8 header-hero">
					<?php the_content(); ?>
				</div>
			</div>
		</div>

		<div class="container container-narrow container-content">
			<div class="entry-content txt">
				<?php the_content(); ?>
			</div>
		</div>

		<footer class="entry-footer">
			<div class="container container-narrow container-footer stack stack--m">
				<div class="row">
					<div class="col-12 entry-footer-apply">
						<?php if ( $show_tarifinformationen_link ) : ?>
							<a href="<?php echo esc_url( home_url( '/karriere/weitere-informationen-zum-thema-karriere/tarifvertrag/' ) ); ?>" class="btn secondary">
								<?php esc_html_e( 'Tarifinformationen anzeigen', 'starterkit' ); ?>
							</a>
						<?php endif; ?>

						<?php if ( $external_url ) : ?>
							<a href="<?php echo esc_url( $external_url ); ?>" target="_blank" rel="noopener noreferrer" class="btn primary">
								<?php esc_html_e( 'Jetzt bewerben', 'starterkit' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</footer>
	</article>
</section>

	<?php
endwhile;

get_footer();
