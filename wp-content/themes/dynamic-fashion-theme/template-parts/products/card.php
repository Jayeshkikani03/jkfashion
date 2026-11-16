<?php
/**
 * Product card — price on image, title + time below.
 *
 * @package Dynamic_Fashion_Theme
 */

$post_id = get_the_ID();
$title   = get_the_title( $post_id );
if ( function_exists( 'vl_clean_product_title' ) ) {
	$title = vl_clean_product_title( $title );
}

$badge = '';
if ( function_exists( 'vl_product_has_flag' ) ) {
	if ( vl_product_has_flag( $post_id, '_vl_is_on_sale' ) ) {
		$badge = __( 'Sale', 'dynamic-fashion' );
	} elseif ( vl_product_has_flag( $post_id, '_vl_is_new' ) ) {
		$badge = __( 'New', 'dynamic-fashion' );
	} elseif ( vl_product_has_flag( $post_id, '_vl_is_luxury' ) ) {
		$badge = __( 'Luxury', 'dynamic-fashion' );
	} elseif ( vl_product_has_flag( $post_id, '_vl_is_trending' ) ) {
		$badge = __( 'Trending', 'dynamic-fashion' );
	}
}

$time_label = human_time_diff( get_the_time( 'U', $post_id ), current_time( 'timestamp' ) );
?>
<article <?php post_class( 'vl-product-card' ); ?>>
	<a class="vl-product-card__media" href="<?php the_permalink(); ?>">
		<?php if ( $badge ) : ?>
			<span class="vl-badge"><?php echo esc_html( $badge ); ?></span>
		<?php endif; ?>
		<span class="vl-product-card__time"><?php echo esc_html( sprintf( __( '%s ago', 'dynamic-fashion' ), $time_label ) ); ?></span>
		<?php if ( has_post_thumbnail() ) : ?>
			<?php
			the_post_thumbnail(
				'large',
				array(
					'loading'       => 'lazy',
					'alt'           => esc_attr( $title ),
					'sizes'         => '(max-width: 640px) 50vw, (max-width: 1100px) 33vw, 25vw',
					'decoding'      => 'async',
				)
			);
			?>
		<?php else : ?>
			<span class="vl-placeholder-art"><?php echo esc_html( wp_trim_words( $title, 4, '' ) ); ?></span>
		<?php endif; ?>
		<span class="vl-product-card__price-on">
			<?php
			if ( function_exists( 'vl_get_product_price_html' ) ) {
				echo vl_get_product_price_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</span>
	</a>
	<div class="vl-product-card__body">
		<h3 class="vl-product-card__title"><a href="<?php the_permalink(); ?>"><?php echo esc_html( $title ); ?></a></h3>
		<a class="vl-product-card__view" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View / Buy COD', 'dynamic-fashion' ); ?></a>
	</div>
</article>
