<?php
/**
 * Footer template.
 *
 * @package Dynamic_Fashion_Theme
 */

$settings  = function_exists( 'vl_get_homepage_settings' ) ? vl_get_homepage_settings() : array();
$about     = ! empty( $settings['footer_about'] ) ? $settings['footer_about'] : __( 'Premium fashion discovery for curated clothing and trending styles. Order with Cash on Delivery.', 'dynamic-fashion' );
$support   = ! empty( $settings['footer_support'] ) ? $settings['footer_support'] : __( 'Need help with an order? Visit Contact and we will assist you.', 'dynamic-fashion' );
$phone     = ! empty( $settings['contact_phone'] ) ? $settings['contact_phone'] : '';
$email     = ! empty( $settings['contact_email'] ) ? $settings['contact_email'] : '';
$whatsapp  = ! empty( $settings['contact_whatsapp'] ) ? preg_replace( '/\D+/', '', $settings['contact_whatsapp'] ) : '';
$address   = ! empty( $settings['contact_address'] ) ? $settings['contact_address'] : '';
$copy      = ! empty( $settings['footer_copyright'] ) ? $settings['footer_copyright'] : ( '© ' . gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' ) );
?>
</main>

<?php dft_render_ad( 'before_footer' ); ?>

<footer class="site-footer">
	<div class="vl-container">
		<div class="vl-footer-grid">
			<div>
				<h3><?php bloginfo( 'name' ); ?></h3>
				<p><?php echo esc_html( $about ); ?></p>
			</div>
			<div>
				<h3><?php esc_html_e( 'Quick links', 'dynamic-fashion' ); ?></h3>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'menu_class'     => 'vl-footer-nav',
						'container'      => false,
						'fallback_cb'    => 'dft_footer_fallback_menu',
						'depth'          => 1,
					)
				);
				?>
			</div>
			<div>
				<h3><?php esc_html_e( 'Contact', 'dynamic-fashion' ); ?></h3>
				<p><?php echo esc_html( $support ); ?></p>
				<ul class="vl-footer-nav">
					<?php if ( $phone ) : ?>
						<li><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li>
					<?php endif; ?>
					<?php if ( $whatsapp ) : ?>
						<li><a href="<?php echo esc_url( 'https://wa.me/' . $whatsapp ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WhatsApp', 'dynamic-fashion' ); ?></a></li>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<li><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
					<?php endif; ?>
					<?php if ( $address ) : ?>
						<li><?php echo esc_html( $address ); ?></li>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact page', 'dynamic-fashion' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'dynamic-fashion' ); ?></a></li>
				</ul>
			</div>
		</div>

		<?php dft_render_ad( 'footer' ); ?>

		<div class="vl-footer-bottom">
			<span><?php echo esc_html( $copy ); ?></span>
			<span><?php bloginfo( 'description' ); ?></span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
