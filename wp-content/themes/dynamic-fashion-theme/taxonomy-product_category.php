<?php
/**
 * Product category archive.
 *
 * @package Dynamic_Fashion_Theme
 */

get_header();

$term = get_queried_object();
$sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'newest'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$orderby = 'date';
$order   = 'DESC';
$meta_key = '';

if ( 'price_asc' === $sort ) {
	$orderby  = 'meta_value_num';
	$order    = 'ASC';
	$meta_key = '_vl_price';
} elseif ( 'price_desc' === $sort ) {
	$orderby  = 'meta_value_num';
	$order    = 'DESC';
	$meta_key = '_vl_price';
}

$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

$per_page = 12;
if ( function_exists( 'vl_get_homepage_settings' ) ) {
	$shop_settings = vl_get_homepage_settings();
	$per_page      = max( 4, absint( $shop_settings['products_per_page'] ) );
}

$query_args = array(
	'post_type'      => 'fashion_product',
	'posts_per_page' => $per_page,
	'paged'          => $paged,
	'orderby'        => $orderby,
	'order'          => $order,
	'tax_query'      => array(
		array(
			'taxonomy' => 'product_category',
			'field'    => 'term_id',
			'terms'    => $term->term_id,
		),
	),
);

if ( $meta_key ) {
	$query_args['meta_key'] = $meta_key;
}

$products = new WP_Query( $query_args );
$count    = 0;
?>

<div class="vl-container" style="padding-top:1.5rem;padding-bottom:2.5rem;">
	<?php
	if ( function_exists( 'vl_render_breadcrumbs' ) ) {
		vl_render_breadcrumbs();
	}
	?>

	<header class="vl-archive-head">
		<h1><?php echo esc_html( $term->name ); ?></h1>
		<?php if ( ! empty( $term->description ) ) : ?>
			<p><?php echo esc_html( $term->description ); ?></p>
		<?php else : ?>
			<p><?php esc_html_e( 'Browse curated looks in this collection.', 'dynamic-fashion' ); ?></p>
		<?php endif; ?>
	</header>

	<?php dft_render_ad( 'before_category' ); ?>

	<div class="vl-layout">
		<div>
			<div class="vl-archive-tools">
				<span><?php echo esc_html( sprintf( _n( '%s product', '%s products', (int) $products->found_posts, 'dynamic-fashion' ), number_format_i18n( $products->found_posts ) ) ); ?></span>
				<form class="vl-sort" method="get">
					<label class="screen-reader-text" for="vl-sort"><?php esc_html_e( 'Sort products', 'dynamic-fashion' ); ?></label>
					<select id="vl-sort" name="sort" onchange="this.form.submit()">
						<option value="newest" <?php selected( $sort, 'newest' ); ?>><?php esc_html_e( 'Newest', 'dynamic-fashion' ); ?></option>
						<option value="price_asc" <?php selected( $sort, 'price_asc' ); ?>><?php esc_html_e( 'Price: Low to High', 'dynamic-fashion' ); ?></option>
						<option value="price_desc" <?php selected( $sort, 'price_desc' ); ?>><?php esc_html_e( 'Price: High to Low', 'dynamic-fashion' ); ?></option>
					</select>
				</form>
			</div>

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
