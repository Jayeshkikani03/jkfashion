<?php
/**
 * Header template.
 *
 * @package Dynamic_Fashion_Theme
 */

$settings = function_exists( 'vl_get_homepage_settings' ) ? vl_get_homepage_settings() : array();
$topbar   = ! empty( $settings['topbar_text'] ) ? $settings['topbar_text'] : __( 'Premium fashion • Cash on Delivery available', 'dynamic-fashion' );
$show_top = ! isset( $settings['show_topbar'] ) || '1' === (string) $settings['show_topbar'];
$show_cat = ! isset( $settings['show_catstrip'] ) || '1' === (string) $settings['show_catstrip'];
$top_lbl  = ! empty( $settings['topbar_link_label'] ) ? $settings['topbar_link_label'] : __( 'Shop Sale', 'dynamic-fashion' );
$top_url  = ! empty( $settings['topbar_link_url'] ) ? $settings['topbar_link_url'] : home_url( '/collection/sale-and-offers/' );
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

<?php if ( $show_top ) : ?>
<div class="vl-topbar">
	<div class="vl-container vl-topbar__inner">
		<span><?php echo esc_html( $topbar ); ?></span>
		<a href="<?php echo esc_url( $top_url ); ?>"><?php echo esc_html( $top_lbl ); ?></a>
	</div>
</div>
<?php endif; ?>

<header class="site-header">
	<div class="vl-container vl-header-bar">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-logo-mark" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php bloginfo( 'name' ); ?>">
					<span class="site-logo-mark__icon">VL</span>
					<span>
						<span class="site-title"><?php bloginfo( 'name' ); ?></span>
						<span class="site-description"><?php bloginfo( 'description' ); ?></span>
					</span>
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
					'depth'          => 1,
				)
			);
			?>
		</nav>

		<button class="vl-nav-toggle" type="button" aria-expanded="false" aria-controls="site-navigation">
			<span></span><span></span><span></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'dynamic-fashion' ); ?></span>
		</button>
	</div>

	<?php if ( $show_cat ) : ?>
	<div class="vl-catstrip">
		<div class="vl-container">
			<ul class="vl-catstrip__list">
				<?php
				$strip = get_terms(
					array(
						'taxonomy'   => 'product_category',
						'hide_empty' => false,
						'orderby'    => 'name',
						'order'      => 'ASC',
					)
				);
				if ( ! is_wp_error( $strip ) && $strip ) {
					foreach ( $strip as $term ) {
						printf(
							'<li><a href="%1$s">%2$s</a></li>',
							esc_url( get_term_link( $term ) ),
							esc_html( $term->name )
						);
					}
				}
				?>
			</ul>
		</div>
	</div>
	<?php endif; ?>
</header>

<!-- Mobile drawer outside header so position:fixed works (header uses backdrop-filter). -->
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
			'depth'          => 1,
		)
	);
	?>
</nav>

<?php dft_render_ad( 'below_nav' ); ?>

<main id="primary" class="site-main">
