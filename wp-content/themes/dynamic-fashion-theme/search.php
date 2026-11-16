<?php
/**
 * Search results.
 *
 * @package Dynamic_Fashion_Theme
 */

get_header();
?>

<div class="vl-container" style="padding:2rem 0;">
	<header class="vl-archive-head">
		<h1>
			<?php
			printf(
				/* translators: %s: search query */
				esc_html__( 'Search results for “%s”', 'dynamic-fashion' ),
				esc_html( get_search_query() )
			);
			?>
		</h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="vl-product-grid">
			<?php
			$count = 0;
			while ( have_posts() ) :
				the_post();
				if ( 'fashion_product' === get_post_type() ) {
					$count++;
					dft_render_product_and_maybe_ad( $count, 4 );
				} else {
					?>
					<article <?php post_class( 'vl-surface-card' ); ?> style="padding:1rem;">
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt() ? get_the_excerpt() : get_the_content(), 20 ) ); ?></p>
					</article>
					<?php
				}
			endwhile;
			?>
		</div>
		<div class="vl-pagination"><?php the_posts_pagination(); ?></div>
	<?php else : ?>
		<div class="vl-empty"><?php esc_html_e( 'No results matched your search.', 'dynamic-fashion' ); ?></div>
	<?php endif; ?>
</div>

<?php
get_footer();
