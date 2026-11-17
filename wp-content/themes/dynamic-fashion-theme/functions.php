<?php
/**
 * Dynamic Fashion Theme functions.
 *
 * @package Dynamic_Fashion_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DFT_VERSION', '1.8.8' );
define( 'DFT_DIR', get_template_directory() );
define( 'DFT_URI', get_template_directory_uri() );

/**
 * Theme setup.
 */
function dft_setup() {
	load_theme_textdomain( 'dynamic-fashion', DFT_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'dynamic-fashion' ),
			'footer'  => __( 'Footer Menu', 'dynamic-fashion' ),
		)
	);

	add_image_size( 'dft-product-card', 640, 800, true );
	add_image_size( 'dft-product-hero', 960, 1200, true );
}
add_action( 'after_setup_theme', 'dft_setup' );

/**
 * Enqueue assets.
 */
function dft_enqueue_assets() {
	wp_enqueue_style(
		'dft-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'dft-main',
		DFT_URI . '/assets/css/main.css',
		array( 'dft-fonts' ),
		DFT_VERSION
	);

	wp_enqueue_script(
		'dft-main',
		DFT_URI . '/assets/js/main.js',
		array(),
		DFT_VERSION,
		true
	);

	wp_localize_script(
		'dft-main',
		'dftData',
		array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'cartNonce'   => wp_create_nonce( 'vl_cart' ),
			'cartUrl'     => function_exists( 'vl_get_cart_url' ) ? vl_get_cart_url() : home_url( '/cart/' ),
			'checkoutUrl' => function_exists( 'vl_get_checkout_url' ) ? vl_get_checkout_url() : home_url( '/checkout/' ),
			'i18n'        => array(
				'adding'     => __( 'Adding…', 'dynamic-fashion' ),
				'added'      => __( 'Added to cart', 'dynamic-fashion' ),
				'addToCart'  => __( 'Add to cart', 'dynamic-fashion' ),
				'placing'    => __( 'Placing order…', 'dynamic-fashion' ),
				'placeOrder' => __( 'Place COD order', 'dynamic-fashion' ),
				'viewCart'   => __( 'View cart', 'dynamic-fashion' ),
				'error'      => __( 'Something went wrong. Try again.', 'dynamic-fashion' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'dft_enqueue_assets' );

/**
 * Fallback primary menu — all categories (extras under More).
 */
function dft_fallback_menu() {
	echo '<ul class="vl-nav__list">';
	echo '<li class="vl-menu-home"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'dynamic-fashion' ) . '</a></li>';

	$shop = get_post_type_archive_link( 'fashion_product' );
	if ( $shop ) {
		echo '<li><a href="' . esc_url( $shop ) . '">' . esc_html__( 'Shop', 'dynamic-fashion' ) . '</a></li>';
	}

	$cats = function_exists( 'vl_get_header_menu_categories' )
		? vl_get_header_menu_categories()
		: array( 'top' => array(), 'more' => array() );

	foreach ( $cats['top'] as $term ) {
		echo '<li><a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a></li>';
	}

	if ( ! empty( $cats['more'] ) ) {
		echo '<li class="vl-menu-more menu-item-has-children">';
		echo '<a href="#">' . esc_html__( 'More', 'dynamic-fashion' ) . '</a>';
		echo '<ul class="sub-menu">';
		foreach ( $cats['more'] as $term ) {
			echo '<li><a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a></li>';
		}
		echo '</ul></li>';
	}

	echo '</ul>';
}

/**
 * Footer fallback links.
 */
function dft_footer_fallback_menu() {
	echo '<ul class="vl-footer-nav">';
	echo '<li><a href="' . esc_url( home_url( '/about/' ) ) . '">' . esc_html__( 'About', 'dynamic-fashion' ) . '</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html__( 'Contact', 'dynamic-fashion' ) . '</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/privacy-policy/' ) ) . '">' . esc_html__( 'Privacy Policy', 'dynamic-fashion' ) . '</a></li>';
	echo '</ul>';
}

/**
 * Safe ad render wrapper if plugin inactive.
 *
 * @param string $slot Slot key.
 */
function dft_render_ad( $slot ) {
	if ( function_exists( 'vl_render_ad' ) ) {
		vl_render_ad( $slot );
	}
}

/**
 * Render current product card + optional parallel ad card in grid.
 *
 * @param int $count Products rendered so far (1-based after increment).
 * @param int $every Insert ad card after every N products.
 */
function dft_render_product_and_maybe_ad( $count, $every = 4 ) {
	if ( function_exists( 'vl_get_homepage_settings' ) ) {
		$settings = vl_get_homepage_settings();
		if ( ! empty( $settings['ad_card_every'] ) ) {
			$every = max( 2, absint( $settings['ad_card_every'] ) );
		}
	}
	get_template_part( 'template-parts/products/card' );
	if ( function_exists( 'vl_maybe_render_product_ad_cards' ) ) {
		vl_maybe_render_product_ad_cards( $every, $count );
	}
}

/**
 * Body classes.
 *
 * @param array $classes Classes.
 * @return array
 */
function dft_body_classes( $classes ) {
	$classes[] = 'dft-theme';
	if ( is_front_page() ) {
		$classes[] = 'dft-home';
	}
	return $classes;
}
add_filter( 'body_class', 'dft_body_classes' );

/**
 * Insert ad between article paragraphs.
 *
 * @param string $content Content.
 * @return string
 */
function dft_article_mid_ad( $content ) {
	if ( ! is_singular( 'fashion_article' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	if ( ! function_exists( 'vl_get_ads_for_slot' ) || empty( vl_get_ads_for_slot( 'between_paragraphs' ) ) ) {
		return $content;
	}

	$parts = explode( '</p>', $content );
	if ( count( $parts ) < 3 ) {
		return $content;
	}

	ob_start();
	dft_render_ad( 'between_paragraphs' );
	$ad_html = ob_get_clean();

	$insert_at = (int) floor( count( $parts ) / 2 );
	$output    = '';
	foreach ( $parts as $index => $part ) {
		$output .= $part;
		if ( $index < count( $parts ) - 1 ) {
			$output .= '</p>';
		}
		if ( $index === $insert_at ) {
			$output .= $ad_html;
		}
	}

	return $output;
}
add_filter( 'the_content', 'dft_article_mid_ad' );
