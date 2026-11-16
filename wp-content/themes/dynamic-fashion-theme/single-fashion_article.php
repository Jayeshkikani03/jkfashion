<?php
/**
 * Single fashion guide template.
 *
 * @package Dynamic_Fashion_Theme
 */

get_header();
?>

<div class="vl-container" style="padding-top:1.5rem;padding-bottom:2.5rem;">
	<?php
	if ( function_exists( 'vl_render_breadcrumbs' ) ) {
		vl_render_breadcrumbs();
	}

	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'vl-article vl-surface-card' ); ?> style="padding:clamp(1.25rem,3vw,2.25rem);">
			<header>
				<p class="site-description"><?php echo esc_html( get_the_date() ); ?></p>
				<h1><?php the_title(); ?></h1>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<div style="margin:1.25rem 0;border-radius:18px;overflow:hidden;">
					<?php the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); ?>
				</div>
			<?php endif; ?>
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>

	<section class="vl-section">
		<div class="vl-section__head">
			<div>
				<h2><?php esc_html_e( 'More guides', 'dynamic-fashion' ); ?></h2>
			</div>
		</div>
		<div class="vl-guide-grid">
			<?php
			// Do not use $more — WordPress sets global $more to 0/1 in setup_postdata().
			$related_guides = new WP_Query(
				array(
					'post_type'           => 'fashion_article',
					'posts_per_page'      => 3,
					'post__not_in'        => array( get_the_ID() ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
				)
			);
			if ( $related_guides->have_posts() ) :
				while ( $related_guides->have_posts() ) :
					$related_guides->the_post();
					?>
					<article class="vl-guide-card">
						<a class="vl-guide-card__media" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
							<?php else : ?>
								<div class="vl-placeholder-art" style="color:#f7f3ec;"><?php esc_html_e( 'Guide', 'dynamic-fashion' ); ?></div>
							<?php endif; ?>
						</a>
						<div class="vl-guide-card__body">
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt() ? get_the_excerpt() : get_the_content(), 18 ) ); ?></p>
						</div>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			endif;
			?>
		</div>
	</section>
</div>

<?php
get_footer();
