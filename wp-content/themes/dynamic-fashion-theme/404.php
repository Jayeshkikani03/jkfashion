<?php
/**
 * 404 template.
 *
 * @package Dynamic_Fashion_Theme
 */

get_header();
?>

<div class="vl-container" style="padding:3rem 0;">
	<div class="vl-empty">
		<h1><?php esc_html_e( 'Page not found', 'dynamic-fashion' ); ?></h1>
		<p><?php esc_html_e( 'The page you requested could not be found. Browse collections from the homepage instead.', 'dynamic-fashion' ); ?></p>
		<p><a class="vl-btn vl-btn--dark" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'dynamic-fashion' ); ?></a></p>
	</div>
</div>

<?php
get_footer();
