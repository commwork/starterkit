<?php
/*
 * Template Name: Jobs Archiv
 */
get_header();

$paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
$jobs_query = new WP_Query(
	array(
		'post_type'      => 'jobs',
		'post_status'    => 'publish',
		'posts_per_page' => 6,
		'paged'          => $paged,
	)
);
?>

<section class="section section--xl jobs-teasers">
	<div class="container">
		<div class="row">
			<div class="col-12 col-lg-8 stack stack--l" id="jobs-teasers-content">
                <!-- Header -->
                <?php if ( have_rows( 'header' ) ) : ?>
                    <div class="section-header stack stack--m" aria-labelledby="<?php echo esc_attr( $headline_id ); ?>">
                        <?php while ( have_rows( 'header' ) ) : the_row(); ?>
                        <p class="section-description"><?php the_sub_field( 'subheadline' ); ?></p>
                            <?php
                            $titel = get_sub_field( 'titel', false );
                            $titel = is_string( $titel ) ? trim( $titel ) : '';
                            ?>
                            <?php if ( $titel !== '' ) : ?>
                                <?php
                                $headline_tag   = starterkit_section_titel_heading_tag();
                                $headline_class = starterkit_section_titel_heading_class( $headline_tag );
                                starterkit_sync_teaser_headline_context_from_section_tag( $headline_tag );
                                ?>
                                <<?php echo tag_escape( $headline_tag ); ?> class="<?php echo esc_attr( $headline_class ); ?>">
                                    <?php echo nl2br( esc_html( $titel ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </<?php echo tag_escape( $headline_tag ); ?>>
                            <?php endif; ?>
                            <div class="section-intro txt lcnmb">
                                <?php the_sub_field( 'intro' ); ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
			</div>
		</div>

		<div class="row module-content">
			<div class="col-12">
				<?php if ( $jobs_query->have_posts() ) : ?>
					<div class="row g-4">
						<?php
						while ( $jobs_query->have_posts() ) :
							$jobs_query->the_post();
							get_template_part( 'template-parts/teaser-cards/job-teaser-card' );
						endwhile;
						?>
					</div>

				<?php
					$pagination = paginate_links(
						array(
							'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
							'format'    => '?paged=%#%',
							'current'   => max( 1, $paged ),
							'total'     => (int) $jobs_query->max_num_pages,
							'prev_text' => '',
							'next_text' => '&raquo;',
							'type'      => 'list',
							'add_fragment' => '#jobs-teasers-content',
						)
					);
					if ( $pagination ) {
						echo '<div class="pagination">';
						echo wp_kses_post( $pagination );
						echo '</div>';
					}
					wp_reset_postdata();
					?>
				<?php else : ?>
					<p class="not-found-text txt"><?php esc_html_e( 'Keine Stellenangebote gefunden.', 'starterkit' ); ?></p>
				<?php endif; ?>
			</div>

		</div>
	</div>
</section>

<?php get_footer(); ?>
