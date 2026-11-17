<?php
/**
 * Header template.
 *
 * @package Dynamic_Fashion_Theme
 */

$settings = function_exists( 'vl_get_homepage_settings' ) ? vl_get_homepage_settings() : array();
$topbar   = isset( $settings['topbar_text'] ) ? trim( (string) $settings['topbar_text'] ) : '';
$show_top = '1' === (string) ( isset( $settings['show_topbar'] ) ? $settings['show_topbar'] : '0' );
$top_lbl  = isset( $settings['topbar_link_label'] ) ? trim( (string) $settings['topbar_link_label'] ) : '';
$top_url  = ! empty( $settings['topbar_link_url'] ) ? $settings['topbar_link_url'] : '';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'dynamic-fashion' ); ?></a>

<?php if ( $show_top && '' !== $topbar ) : ?>
<div class="vl-topbar">
	<div class="vl-container vl-topbar__inner">
		<span><?php echo esc_html( $topbar ); ?></span>
		<?php if ( $top_lbl && $top_url ) : ?>
			<a href="<?php echo esc_url( $top_url ); ?>"><?php echo esc_html( $top_lbl ); ?></a>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>

<?php dft_render_ad( 'header_top' ); ?>

<div class="vl-header-sentinel" aria-hidden="true"></div>
<header class="site-header" id="site-header">
	<div class="vl-container vl-header-bar">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-title-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span class="site-title"><?php bloginfo( 'name' ); ?></span>
				</a>
			<?php endif; ?>
		</div>

		<nav id="site-navigation-desktop" class="vl-nav vl-nav--desktop" aria-label="<?php esc_attr_e( 'Primary', 'dynamic-fashion' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu_class'     => 'vl-nav__list',
					'container'      => false,
					'fallback_cb'    => 'dft_fallback_menu',
					'depth'          => 2,
				)
			);
			?>
		</nav>

		<div class="vl-header-tools">
			<?php
			$cart_url   = function_exists( 'vl_get_cart_url' ) ? vl_get_cart_url() : home_url( '/cart/' );
			$cart_count = function_exists( 'vl_cart_count' ) ? vl_cart_count() : 0;
			?>
			<a class="vl-cart-link" href="<?php echo esc_url( $cart_url ); ?>" data-vl-cart-link aria-label="<?php esc_attr_e( 'Cart', 'dynamic-fashion' ); ?>">
				<svg class="vl-cart-link__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
					<path d="M7 4h-2l-1 2h2l2.4 9.2a2 2 0 0 0 2 1.5h7.2a2 2 0 0 0 2-1.5L21 8H8.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
					<circle cx="10" cy="20" r="1.4" fill="currentColor"/>
					<circle cx="18" cy="20" r="1.4" fill="currentColor"/>
				</svg>
				<span class="vl-cart-link__count" data-vl-cart-count<?php echo $cart_count ? '' : ' hidden'; ?>><?php echo esc_html( (string) $cart_count ); ?></span>
			</a>
			<button class="vl-nav-toggle" type="button" aria-expanded="false" aria-controls="site-navigation">
				<span></span><span></span><span></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'dynamic-fashion' ); ?></span>
			</button>
		</div>
	</div>
</header>
<div class="vl-header-spacer" aria-hidden="true"></div>

<!-- Mobile drawer outside header so position:fixed works. -->
<div class="vl-nav-overlay" hidden></div>
<nav id="site-navigation" class="vl-nav vl-nav--mobile" aria-label="<?php esc_attr_e( 'Mobile', 'dynamic-fashion' ); ?>">
	<div class="vl-nav__mobile-head">
		<strong><?php esc_html_e( 'Menu', 'dynamic-fashion' ); ?></strong>
		<button type="button" class="vl-nav-close" aria-label="<?php esc_attr_e( 'Close menu', 'dynamic-fashion' ); ?>">×</button>
	</div>
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'menu_class'     => 'vl-nav__list',
			'container'      => false,
			'fallback_cb'    => 'dft_fallback_menu',
			'depth'          => 2,
		)
	);
	?>
</nav>

<?php dft_render_ad( 'below_nav' ); ?>

<main id="primary" class="site-main">
