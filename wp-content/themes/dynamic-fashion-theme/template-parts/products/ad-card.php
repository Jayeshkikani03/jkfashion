<?php
/**
 * Product-grid ad card (same size as product cards).
 *
 * @package Dynamic_Fashion_Theme
 *
 * @var WP_Post|null $ad Optional ad post passed via set_query_var.
 */

$ad = get_query_var( 'vl_ad_card' );
if ( ! $ad instanceof WP_Post ) {
	return;
}

$code    = get_post_meta( $ad->ID, '_vl_ad_code', true );
$desktop = get_post_meta( $ad->ID, '_vl_ad_desktop', true );
$mobile  = get_post_meta( $ad->ID, '_vl_ad_mobile', true );

if ( ! $code ) {
	return;
}

$classes = array( 'vl-ad-card' );
if ( '1' !== (string) $desktop ) {
	$classes[] = 'vl-ad-card--hide-desktop';
}
if ( '1' !== (string) $mobile ) {
	$classes[] = 'vl-ad-card--hide-mobile';
}
?>
<aside class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" aria-label="<?php esc_attr_e( 'Advertisement', 'dynamic-fashion' ); ?>">
	<span class="vl-ad-card__label"><?php esc_html_e( 'Sponsored', 'dynamic-fashion' ); ?></span>
	<div class="vl-ad-card__media">
		<?php
		if ( function_exists( 'vl_sanitize_ad_html' ) ) {
			echo vl_sanitize_ad_html( $code ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo wp_kses_post( $code );
		}
		?>
	</div>
</aside>
