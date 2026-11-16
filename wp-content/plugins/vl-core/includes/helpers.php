<?php
/**
 * Shared helpers.
 *
 * @package VL_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clean product title: remove emojis and excess symbols.
 *
 * @param string $title Title.
 * @return string
 */
function vl_clean_product_title( $title ) {
	$title = wp_strip_all_tags( (string) $title );
	$title = preg_replace( '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}\x{200D}]/u', '', $title );
	$title = preg_replace( '/[✨🔥🧸🤎🌿💖👗🛍️⭐️✅❌•·]+/u', '', $title );
	$title = preg_replace( '/\s{2,}/', ' ', $title );
	$title = trim( $title, " \t\n\r\0\x0B-–—|" );
	return $title;
}

/**
 * Format a product price in INR.
 *
 * @param mixed $price Price value.
 * @return string
 */
function vl_format_price( $price ) {
	if ( '' === $price || null === $price ) {
		return '';
	}

	$number = floatval( $price );
	if ( $number <= 0 ) {
		return '';
	}

	$symbol = '₹';
	if ( function_exists( 'vl_get_homepage_settings' ) ) {
		$settings = vl_get_homepage_settings();
		if ( ! empty( $settings['currency_symbol'] ) ) {
			$symbol = $settings['currency_symbol'];
		}
	}
	return $symbol . number_format_i18n( $number, 2 );
}

/**
 * Get product meta with defaults.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key without underscore prefix expectation.
 * @param mixed  $default Default value.
 * @return mixed
 */
function vl_get_product_meta( $post_id, $key, $default = '' ) {
	$value = get_post_meta( $post_id, $key, true );
	return ( '' === $value || null === $value ) ? $default : $value;
}

/**
 * Whether a product is marked with a flag.
 *
 * @param int    $post_id Post ID.
 * @param string $flag    Flag meta key.
 * @return bool
 */
function vl_product_has_flag( $post_id, $flag ) {
	return '1' === (string) vl_get_product_meta( $post_id, $flag, '0' );
}

/**
 * Query products by category slug or flags.
 *
 * @param array $args Query args.
 * @return WP_Query
 */
function vl_query_products( $args = array() ) {
	$defaults = array(
		'post_type'      => 'fashion_product',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	$args = wp_parse_args( $args, $defaults );

	if ( ! empty( $args['category_slug'] ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_category',
				'field'    => 'slug',
				'terms'    => $args['category_slug'],
			),
		);
		unset( $args['category_slug'] );
	}

	if ( ! empty( $args['meta_flag'] ) ) {
		$args['meta_query'] = array(
			array(
				'key'   => $args['meta_flag'],
				'value' => '1',
			),
		);
		unset( $args['meta_flag'] );
	}

	return new WP_Query( $args );
}

/**
 * Allowed HTML for advertisement code.
 *
 * @return array
 */
function vl_allowed_ad_html() {
	$allowed = wp_kses_allowed_html( 'post' );

	$allowed['script'] = array(
		'async'          => true,
		'charset'        => true,
		'crossorigin'    => true,
		'defer'          => true,
		'src'            => true,
		'type'           => true,
		'data-*'         => true,
		'id'             => true,
		'class'          => true,
	);

	$allowed['ins'] = array(
		'class'          => true,
		'style'          => true,
		'data-*'         => true,
		'id'             => true,
	);

	$allowed['iframe'] = array(
		'src'             => true,
		'width'           => true,
		'height'          => true,
		'style'           => true,
		'frameborder'     => true,
		'allow'           => true,
		'allowfullscreen' => true,
		'loading'         => true,
		'referrerpolicy'  => true,
		'title'           => true,
		'class'           => true,
		'id'              => true,
	);

	return $allowed;
}

/**
 * Sanitize advertisement HTML for users who can manage options.
 *
 * @param string $html Raw HTML.
 * @return string
 */
function vl_sanitize_ad_html( $html ) {
	$html = (string) $html;

	if ( current_user_can( 'manage_options' ) ) {
		return $html;
	}

	return wp_kses( $html, vl_allowed_ad_html() );
}
