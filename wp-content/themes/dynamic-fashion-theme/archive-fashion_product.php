<?php
/**
 * All products archive.
 *
 * @package Dynamic_Fashion_Theme
 */

get_header();
?>

<div class="vl-container" style="padding-top:1.5rem;padding-bottom:2.5rem;">
	<header class="vl-archive-head">
		<h1><?php esc_html_e( 'All products', 'dynamic-fashion' ); ?></h1>
		<p><?php esc_html_e( 'Browse the full Vastralaxmi Fashion catalog.', 'dynamic-fashion' ); ?></p>
	</header>

	<?php dft_render_ad( 'before_category' ); ?>

	<?php if ( have_posts() ) : ?>
		<div class="vl-product-grid">
			<?php
			$count = 0;
			while ( have_posts() ) :
				the_post();
				$count++;
				dft_render_product_and_maybe_ad( $count, 4 );
			endwhile;
			?>
		</div>
		<div class="vl-pagination"><?php the_posts_pagination(); ?></div>
	<?php else : ?>
		<div class="vl-empty"><?php esc_html_e( 'No products published yet.', 'dynamic-fashion' ); ?></div>
	<?php endif; ?>
</div>

<?php
get_footer();
