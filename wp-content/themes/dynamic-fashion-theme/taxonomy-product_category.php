<?php
/**
 * Product category archive.
 *
 * @package Dynamic_Fashion_Theme
 */

get_header();

$term  = get_queried_object();
$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

$per_page = 12;
if ( function_exists( 'vl_get_homepage_settings' ) ) {
	$shop_settings = vl_get_homepage_settings();
	$per_page      = max( 4, absint( $shop_settings['products_per_page'] ) );
}

$products = new WP_Query(
	array(
		'post_type'      => 'fashion_product',
		'posts_per_page' => $per_page,
		'paged'          => $paged,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'tax_query'      => array(
			array(
				'taxonomy' => 'product_category',
				'field'    => 'term_id',
				'terms'    => $term->term_id,
			),
		),
	)
);
$count = 0;
?>

<div class="vl-container" style="padding-top:1.5rem;padding-bottom:2.5rem;">
	<?php dft_render_ad( 'before_category' ); ?>

	<div class="vl-layout">
		<div>
			<?php if ( $products->have_posts() ) : ?>
				<div class="vl-product-grid">
					<?php
					while ( $products->have_posts() ) :
						$products->the_post();
						$count++;
						dft_render_product_and_maybe_ad( $count, 4 );
					endwhile;
					?>
				</div>

				<div class="vl-pagination">
					<?php
					echo paginate_links( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'total'   => $products->max_num_pages,
							'current' => $paged,
						)
					);
					?>
				</div>
				<?php
				wp_reset_postdata();
			else :
				?>
				<div class="vl-empty"><?php esc_html_e( 'No products in this collection yet.', 'dynamic-fashion' ); ?></div>
			<?php endif; ?>
		</div>

		<aside>
			<?php dft_render_ad( 'sidebar' ); ?>
			<div class="vl-surface-card" style="padding:1rem;margin-top:1rem;">
				<h3 style="margin-top:0;font-family:var(--vl-font-display);"><?php esc_html_e( 'Collections', 'dynamic-fashion' ); ?></h3>
				<ul class="vl-footer-nav">
					<?php
					$all_terms = get_terms(
						array(
							'taxonomy'   => 'product_category',
							'hide_empty' => false,
						)
					);
					if ( ! is_wp_error( $all_terms ) ) {
						foreach ( $all_terms as $item ) {
							echo '<li><a href="' . esc_url( get_term_link( $item ) ) . '">' . esc_html( $item->name ) . '</a></li>';
						}
					}
					?>
				</ul>
			</div>
		</aside>
	</div>
</div>

<?php
get_footer();
