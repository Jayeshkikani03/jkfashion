<?php
/**
 * Site settings — split into clear admin pages.
 *
 * @package VL_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default site settings.
 *
 * @return array
 */
function vl_default_homepage_settings() {
	return array(
		'hero_title'           => 'Discover refined fashion edits',
		'hero_text'            => 'Curated looks across fashion, luxury collections, trending styles, and seasonal offers.',
		'hero_cta_label'       => 'Explore collections',
		'hero_cta_url'         => home_url( '/collection/fashion/' ),
		'hero_secondary'       => 'Our story',
		'hero_secondary_url'   => home_url( '/about/' ),
		'newsletter_title'     => 'Style notes in your inbox',
		'newsletter_text'      => 'Get new arrivals and styling ideas. No spam — unsubscribe anytime.',
		'meta_description'     => 'Premium fashion discovery with curated collections and seasonal edits.',
		'footer_about'         => 'Premium fashion discovery for curated clothing, festive edits, and trending styles.',
		'footer_support'       => 'Questions? Visit the Contact page and we will help.',
		'footer_copyright'     => '',
		'topbar_text'          => 'Premium fashion discovery',
		'topbar_link_label'    => 'Shop Sale',
		'topbar_link_url'      => home_url( '/collection/sale-and-offers/' ),
		'show_topbar'          => '1',
		'show_catstrip'        => '0',
		'contact_phone'        => '',
		'contact_email'        => '',
		'contact_whatsapp'     => '',
		'contact_address'      => '',
		'cod_note'             => 'Cash on Delivery available. Pay when your order arrives.',
		'products_per_page'    => '12',
		'ad_card_every'        => '4',
		'currency_symbol'      => '₹',
		'head_scripts'         => '',
		'body_scripts'         => '',
	);
}

/**
 * Get site settings.
 *
 * @return array
 */
function vl_get_homepage_settings() {
	$saved = get_option( 'vl_homepage_settings', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return wp_parse_args( $saved, vl_default_homepage_settings() );
}

/**
 * Settings tabs / pages map.
 *
 * @return array
 */
function vl_settings_pages() {
	return array(
		'vl-settings'          => array(
			'title' => __( 'Site Identity', 'vl-core' ),
			'menu'  => __( 'Site Identity', 'vl-core' ),
			'render'=> 'vl_render_settings_identity',
		),
		'vl-settings-homepage' => array(
			'title' => __( 'Homepage', 'vl-core' ),
			'menu'  => __( 'Homepage', 'vl-core' ),
			'render'=> 'vl_render_settings_homepage',
		),
		'vl-settings-footer'   => array(
			'title' => __( 'Footer & Contact', 'vl-core' ),
			'menu'  => __( 'Footer & Contact', 'vl-core' ),
			'render'=> 'vl_render_settings_footer',
		),
		'vl-settings-tracking' => array(
			'title' => __( 'Analytics & tracking', 'vl-core' ),
			'menu'  => __( 'Analytics & tracking', 'vl-core' ),
			'render'=> 'vl_render_settings_tracking',
		),
		'vl-settings-shop'     => array(
			'title' => __( 'Shop Options', 'vl-core' ),
			'menu'  => __( 'Shop Options', 'vl-core' ),
			'render'=> 'vl_render_settings_shop',
		),
		'vl-settings-users'    => array(
			'title' => __( 'Shop Users', 'vl-core' ),
			'menu'  => __( 'Shop Users', 'vl-core' ),
			'render'=> 'vl_render_settings_users',
		),
		'vl-settings-menus'    => array(
			'title' => __( 'Menus', 'vl-core' ),
			'menu'  => __( 'Menus', 'vl-core' ),
			'render'=> 'vl_render_settings_menus',
		),
	);
}

/**
 * Register settings menu + subpages.
 */
function vl_register_settings_page() {
	add_menu_page(
		__( 'Site Settings', 'vl-core' ),
		__( 'Site Settings', 'vl-core' ),
		'manage_options',
		'vl-settings',
		'vl_render_settings_identity',
		'dashicons-admin-generic',
		58
	);

	// First submenu renames the auto parent item.
	add_submenu_page(
		'vl-settings',
		__( 'Site Identity', 'vl-core' ),
		__( 'Site Identity', 'vl-core' ),
		'manage_options',
		'vl-settings',
		'vl_render_settings_identity'
	);

	foreach ( vl_settings_pages() as $slug => $page ) {
		if ( 'vl-settings' === $slug ) {
			continue;
		}
		add_submenu_page(
			'vl-settings',
			$page['title'],
			$page['menu'],
			'manage_options',
			$slug,
			$page['render']
		);
	}
}
add_action( 'admin_menu', 'vl_register_settings_page' );

/**
 * Register settings.
 */
function vl_register_settings() {
	register_setting(
		'vl_settings_group',
		'vl_homepage_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'vl_sanitize_homepage_settings',
			'default'           => vl_default_homepage_settings(),
		)
	);
}
add_action( 'admin_init', 'vl_register_settings' );

/**
 * Sanitize settings — merge with existing so separate pages don't wipe other fields.
 *
 * @param array $input Raw input.
 * @return array
 */
function vl_sanitize_homepage_settings( $input ) {
	$defaults = vl_default_homepage_settings();
	$current  = vl_get_homepage_settings();
	$output   = $current;
	$input    = is_array( $input ) ? $input : array();

	foreach ( $defaults as $key => $default ) {
		if ( ! array_key_exists( $key, $input ) ) {
			continue;
		}

		if ( false !== strpos( $key, 'url' ) ) {
			$output[ $key ] = esc_url_raw( $input[ $key ] );
		} elseif ( in_array( $key, array( 'head_scripts', 'body_scripts' ), true ) ) {
			$output[ $key ] = vl_sanitize_tracking_code( wp_unslash( $input[ $key ] ) );
		} elseif ( in_array( $key, array( 'footer_about', 'footer_support', 'footer_copyright', 'hero_text', 'newsletter_text', 'meta_description', 'cod_note', 'contact_address' ), true ) ) {
			$output[ $key ] = sanitize_textarea_field( $input[ $key ] );
		} elseif ( in_array( $key, array( 'show_topbar', 'show_catstrip' ), true ) ) {
			$output[ $key ] = ( '1' === (string) $input[ $key ] ) ? '1' : '0';
		} elseif ( in_array( $key, array( 'products_per_page', 'ad_card_every' ), true ) ) {
			$output[ $key ] = (string) max( 1, absint( $input[ $key ] ) );
		} else {
			$output[ $key ] = sanitize_text_field( $input[ $key ] );
		}
	}

	if ( isset( $_POST['vl_site_title'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_option( 'blogname', sanitize_text_field( wp_unslash( $_POST['vl_site_title'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
	if ( isset( $_POST['vl_site_tagline'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_option( 'blogdescription', sanitize_text_field( wp_unslash( $_POST['vl_site_tagline'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	// Unchecked checkboxes need explicit handling when page posts them via hidden fields.
	if ( isset( $_POST['vl_flags_page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$output['show_topbar']   = isset( $input['show_topbar'] ) ? '1' : '0';
		$output['show_catstrip'] = isset( $input['show_catstrip'] ) ? '1' : '0';
	}

	return wp_parse_args( $output, $defaults );
}

/**
 * Store tracking codes for admins.
 *
 * @param string $code Raw code.
 * @return string
 */
function vl_sanitize_tracking_code( $code ) {
	$code = (string) $code;
	if ( '' === trim( $code ) ) {
		return '';
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return '';
	}
	$code = str_replace( "\0", '', $code );
	$code = preg_replace( '/<\?(?:php|=)?[\s\S]*?\?>/i', '', $code );
	return trim( $code );
}

/**
 * Print head tracking tags.
 */
function vl_output_head_scripts() {
	$settings = vl_get_homepage_settings();
	if ( empty( $settings['head_scripts'] ) ) {
		return;
	}
	echo "\n<!-- VL tracking head -->\n";
	echo $settings['head_scripts'] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'vl_output_head_scripts', 99 );

/**
 * Print body tracking tags.
 */
function vl_output_body_scripts() {
	$settings = vl_get_homepage_settings();
	if ( empty( $settings['body_scripts'] ) ) {
		return;
	}
	echo "\n<!-- VL tracking body -->\n";
	echo $settings['body_scripts'] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_footer', 'vl_output_body_scripts', 99 );

/**
 * Settings nav tabs.
 *
 * @param string $active Active slug.
 */
function vl_settings_tabs( $active ) {
	$pages = vl_settings_pages();
	$pages['vl-import-catalog'] = array(
		'title' => __( 'Import Catalog', 'vl-core' ),
		'menu'  => __( 'Import Catalog', 'vl-core' ),
	);
	echo '<h2 class="nav-tab-wrapper vl-settings-tabs">';
	foreach ( $pages as $slug => $page ) {
		printf(
			'<a class="nav-tab %1$s" href="%2$s">%3$s</a>',
			$active === $slug ? 'nav-tab-active' : '',
			esc_url( admin_url( 'admin.php?page=' . $slug ) ),
			esc_html( $page['menu'] )
		);
	}
	echo '</h2>';
}

/**
 * Shared wrap start.
 *
 * @param string $title Title.
 * @param string $slug  Page slug.
 */
function vl_settings_wrap_start( $title, $slug ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return false;
	}
	echo '<div class="wrap vl-settings-wrap">';
	echo '<h1>' . esc_html( $title ) . '</h1>';
	vl_settings_tabs( $slug );
	if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Saved.', 'vl-core' ) . '</p></div>';
	}
	return true;
}

/**
 * Identity page.
 */
function vl_render_settings_identity() {
	if ( ! vl_settings_wrap_start( __( 'Site Identity', 'vl-core' ), 'vl-settings' ) ) {
		return;
	}
	$settings = vl_get_homepage_settings();
	?>
	<p class="description"><?php esc_html_e( 'Site name, logo, tagline, SEO text, and top bar — used across the whole website.', 'vl-core' ); ?></p>
	<form method="post" action="options.php">
		<?php settings_fields( 'vl_settings_group' ); ?>
		<input type="hidden" name="vl_flags_page" value="1">
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="vl_site_title"><?php esc_html_e( 'Site name', 'vl-core' ); ?></label></th>
				<td><input class="regular-text" type="text" id="vl_site_title" name="vl_site_title" value="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Site logo', 'vl-core' ); ?></th>
				<td>
					<?php if ( has_custom_logo() ) : ?>
						<div style="margin-bottom:8px;"><?php the_custom_logo(); ?></div>
					<?php else : ?>
						<p class="description"><?php esc_html_e( 'No logo uploaded — only the site name is shown in the header.', 'vl-core' ); ?></p>
					<?php endif; ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[control]=custom_logo' ) ); ?>"><?php esc_html_e( 'Upload / change site logo', 'vl-core' ); ?></a>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="vl_site_tagline"><?php esc_html_e( 'Tagline', 'vl-core' ); ?></label></th>
				<td><input class="large-text" type="text" id="vl_site_tagline" name="vl_site_tagline" value="<?php echo esc_attr( get_bloginfo( 'description' ) ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="meta_description"><?php esc_html_e( 'Default meta description (SEO)', 'vl-core' ); ?></label></th>
				<td><textarea class="large-text" rows="3" id="meta_description" name="vl_homepage_settings[meta_description]"><?php echo esc_textarea( $settings['meta_description'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Top bar', 'vl-core' ); ?></th>
				<td>
					<label><input type="checkbox" name="vl_homepage_settings[show_topbar]" value="1" <?php checked( $settings['show_topbar'], '1' ); ?>> <?php esc_html_e( 'Show top bar', 'vl-core' ); ?></label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="topbar_text"><?php esc_html_e( 'Top bar text', 'vl-core' ); ?></label></th>
				<td><input class="large-text" type="text" id="topbar_text" name="vl_homepage_settings[topbar_text]" value="<?php echo esc_attr( $settings['topbar_text'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="topbar_link_label"><?php esc_html_e( 'Top bar link label', 'vl-core' ); ?></label></th>
				<td><input class="regular-text" type="text" id="topbar_link_label" name="vl_homepage_settings[topbar_link_label]" value="<?php echo esc_attr( $settings['topbar_link_label'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="topbar_link_url"><?php esc_html_e( 'Top bar link URL', 'vl-core' ); ?></label></th>
				<td><input class="large-text" type="url" id="topbar_link_url" name="vl_homepage_settings[topbar_link_url]" value="<?php echo esc_url( $settings['topbar_link_url'] ); ?>"></td>
			</tr>
		</table>
		<?php submit_button( __( 'Save Site Identity', 'vl-core' ) ); ?>
	</form>
	</div>
	<?php
}

/**
 * Homepage page.
 */
function vl_render_settings_homepage() {
	if ( ! vl_settings_wrap_start( __( 'Homepage', 'vl-core' ), 'vl-settings-homepage' ) ) {
		return;
	}
	$settings = vl_get_homepage_settings();
	?>
	<p class="description"><?php esc_html_e( 'Hero banner and newsletter section on the homepage.', 'vl-core' ); ?></p>
	<form method="post" action="options.php">
		<?php settings_fields( 'vl_settings_group' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="hero_title"><?php esc_html_e( 'Hero title', 'vl-core' ); ?></label></th>
				<td><input class="large-text" type="text" id="hero_title" name="vl_homepage_settings[hero_title]" value="<?php echo esc_attr( $settings['hero_title'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="hero_text"><?php esc_html_e( 'Hero text', 'vl-core' ); ?></label></th>
				<td><textarea class="large-text" rows="3" id="hero_text" name="vl_homepage_settings[hero_text]"><?php echo esc_textarea( $settings['hero_text'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="hero_cta_label"><?php esc_html_e( 'Primary button label', 'vl-core' ); ?></label></th>
				<td><input class="regular-text" type="text" id="hero_cta_label" name="vl_homepage_settings[hero_cta_label]" value="<?php echo esc_attr( $settings['hero_cta_label'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="hero_cta_url"><?php esc_html_e( 'Primary button URL', 'vl-core' ); ?></label></th>
				<td><input class="large-text" type="url" id="hero_cta_url" name="vl_homepage_settings[hero_cta_url]" value="<?php echo esc_url( $settings['hero_cta_url'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="hero_secondary"><?php esc_html_e( 'Secondary button label', 'vl-core' ); ?></label></th>
				<td><input class="regular-text" type="text" id="hero_secondary" name="vl_homepage_settings[hero_secondary]" value="<?php echo esc_attr( $settings['hero_secondary'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="hero_secondary_url"><?php esc_html_e( 'Secondary button URL', 'vl-core' ); ?></label></th>
				<td><input class="large-text" type="url" id="hero_secondary_url" name="vl_homepage_settings[hero_secondary_url]" value="<?php echo esc_url( $settings['hero_secondary_url'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="newsletter_title"><?php esc_html_e( 'Newsletter title', 'vl-core' ); ?></label></th>
				<td><input class="large-text" type="text" id="newsletter_title" name="vl_homepage_settings[newsletter_title]" value="<?php echo esc_attr( $settings['newsletter_title'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="newsletter_text"><?php esc_html_e( 'Newsletter text', 'vl-core' ); ?></label></th>
				<td><textarea class="large-text" rows="2" id="newsletter_text" name="vl_homepage_settings[newsletter_text]"><?php echo esc_textarea( $settings['newsletter_text'] ); ?></textarea></td>
			</tr>
		</table>
		<?php submit_button( __( 'Save Homepage', 'vl-core' ) ); ?>
	</form>
	</div>
	<?php
}

/**
 * Footer & contact page.
 */
function vl_render_settings_footer() {
	if ( ! vl_settings_wrap_start( __( 'Footer & Contact', 'vl-core' ), 'vl-settings-footer' ) ) {
		return;
	}
	$settings = vl_get_homepage_settings();
	?>
	<p class="description"><?php esc_html_e( 'Footer text and contact details. Leave phone / WhatsApp / email / address empty to hide them on the website.', 'vl-core' ); ?></p>
	<form method="post" action="options.php">
		<?php settings_fields( 'vl_settings_group' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="footer_about"><?php esc_html_e( 'Footer about text', 'vl-core' ); ?></label></th>
				<td><textarea class="large-text" rows="3" id="footer_about" name="vl_homepage_settings[footer_about]"><?php echo esc_textarea( $settings['footer_about'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="footer_support"><?php esc_html_e( 'Footer help text', 'vl-core' ); ?></label></th>
				<td><textarea class="large-text" rows="3" id="footer_support" name="vl_homepage_settings[footer_support]"><?php echo esc_textarea( $settings['footer_support'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="footer_copyright"><?php esc_html_e( 'Copyright line (optional)', 'vl-core' ); ?></label></th>
				<td>
					<input class="large-text" type="text" id="footer_copyright" name="vl_homepage_settings[footer_copyright]" value="<?php echo esc_attr( $settings['footer_copyright'] ); ?>" placeholder="<?php echo esc_attr( '© ' . gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="contact_phone"><?php esc_html_e( 'Phone', 'vl-core' ); ?></label></th>
				<td>
					<input class="regular-text" type="text" id="contact_phone" name="vl_homepage_settings[contact_phone]" value="<?php echo esc_attr( $settings['contact_phone'] ); ?>">
					<p class="description"><?php esc_html_e( 'Clear this field to remove the phone link from the site.', 'vl-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="contact_whatsapp"><?php esc_html_e( 'WhatsApp number', 'vl-core' ); ?></label></th>
				<td>
					<input class="regular-text" type="text" id="contact_whatsapp" name="vl_homepage_settings[contact_whatsapp]" value="<?php echo esc_attr( $settings['contact_whatsapp'] ); ?>" placeholder="919876543210">
					<p class="description"><?php esc_html_e( 'Country code + number, no + or spaces. Leave empty to hide WhatsApp.', 'vl-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="contact_email"><?php esc_html_e( 'Email', 'vl-core' ); ?></label></th>
				<td>
					<input class="regular-text" type="email" id="contact_email" name="vl_homepage_settings[contact_email]" value="<?php echo esc_attr( $settings['contact_email'] ); ?>">
					<p class="description"><?php esc_html_e( 'Leave empty to hide email on the site.', 'vl-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="contact_address"><?php esc_html_e( 'Address', 'vl-core' ); ?></label></th>
				<td><textarea class="large-text" rows="2" id="contact_address" name="vl_homepage_settings[contact_address]"><?php echo esc_textarea( $settings['contact_address'] ); ?></textarea></td>
			</tr>
		</table>
		<?php submit_button( __( 'Save Footer & Contact', 'vl-core' ) ); ?>
	</form>
	</div>
	<?php
}

/**
 * Tracking page.
 */
function vl_render_settings_tracking() {
	if ( ! vl_settings_wrap_start( __( 'Analytics & tracking', 'vl-core' ), 'vl-settings-tracking' ) ) {
		return;
	}
	$settings = vl_get_homepage_settings();
	?>
	<p class="description"><?php esc_html_e( 'Sitewide scripts only. Google Auto Ads / AdSense account code goes in Head tags. Manual banner units go under Advertisements with a slot tag.', 'vl-core' ); ?></p>
	<form method="post" action="options.php">
		<?php settings_fields( 'vl_settings_group' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="head_scripts"><?php esc_html_e( 'Head tags (Auto Ads / AdSense / GA4)', 'vl-core' ); ?></label></th>
				<td>
					<textarea class="large-text code" rows="10" id="head_scripts" name="vl_homepage_settings[head_scripts]" placeholder="&lt;script async src=&quot;https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-XXXXXXXX&quot; crossorigin=&quot;anonymous&quot;&gt;&lt;/script&gt;"><?php echo esc_textarea( $settings['head_scripts'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Paste Google Auto Ads / AdSense client script here (adsbygoogle.js?client=ca-pub-…). Also GA4, GTM head, Search Console.', 'vl-core' ); ?></p>
					<ol class="description" style="margin:8px 0 0 1.2rem;">
						<li><?php esc_html_e( 'AdSense → Ads → Auto ads → copy site script', 'vl-core' ); ?></li>
						<li><?php esc_html_e( 'Paste into Head tags → Save', 'vl-core' ); ?></li>
						<li><?php esc_html_e( 'Fixed banners (header / between products / footer) use Advertisements + slot tags', 'vl-core' ); ?></li>
					</ol>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="body_scripts"><?php esc_html_e( 'Body / footer tags', 'vl-core' ); ?></label></th>
				<td>
					<textarea class="large-text code" rows="8" id="body_scripts" name="vl_homepage_settings[body_scripts]"><?php echo esc_textarea( $settings['body_scripts'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Meta Pixel noscript, GTM body snippet, other footer trackers. Not for Auto Ads.', 'vl-core' ); ?></p>
				</td>
			</tr>
		</table>
		<?php submit_button( __( 'Save analytics & tracking', 'vl-core' ) ); ?>
	</form>

	<div class="vl-admin-box" style="margin-top:18px;max-width:820px;padding:12px 14px;border:1px solid #dcdcde;border-radius:8px;background:#fff;">
		<strong><?php esc_html_e( 'Where each ad type goes', 'vl-core' ); ?></strong>
		<ul style="margin:8px 0 0 1.2rem;">
			<li><strong><?php esc_html_e( 'Auto ads / AdSense account script', 'vl-core' ); ?></strong> → <?php esc_html_e( 'Site Settings → Analytics & tracking → Head tags', 'vl-core' ); ?></li>
			<li><strong><?php esc_html_e( 'Manual banner / display unit HTML', 'vl-core' ); ?></strong> → <?php esc_html_e( 'Advertisements → choose slot (header_top, between_products, before_footer…)', 'vl-core' ); ?></li>
		</ul>
	</div>
	<p style="margin-top:12px;"><a class="button button-primary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=vl_ad' ) ); ?>"><?php esc_html_e( 'Manage Advertisements (slot tags)', 'vl-core' ); ?></a></p>
	</div>
	<?php
}

/**
 * Shop options page.
 */
function vl_render_settings_shop() {
	if ( ! vl_settings_wrap_start( __( 'Shop Options', 'vl-core' ), 'vl-settings-shop' ) ) {
		return;
	}
	$settings = vl_get_homepage_settings();
	?>
	<p class="description"><?php esc_html_e( 'COD note, currency, product list size, and ad-card spacing.', 'vl-core' ); ?></p>
	<form method="post" action="options.php">
		<?php settings_fields( 'vl_settings_group' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="cod_note"><?php esc_html_e( 'COD note (product page)', 'vl-core' ); ?></label></th>
				<td><textarea class="large-text" rows="2" id="cod_note" name="vl_homepage_settings[cod_note]"><?php echo esc_textarea( $settings['cod_note'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="currency_symbol"><?php esc_html_e( 'Currency symbol', 'vl-core' ); ?></label></th>
				<td><input class="small-text" type="text" id="currency_symbol" name="vl_homepage_settings[currency_symbol]" value="<?php echo esc_attr( $settings['currency_symbol'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="products_per_page"><?php esc_html_e( 'Products per page', 'vl-core' ); ?></label></th>
				<td><input class="small-text" type="number" min="4" max="48" id="products_per_page" name="vl_homepage_settings[products_per_page]" value="<?php echo esc_attr( $settings['products_per_page'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="ad_card_every"><?php esc_html_e( 'Show ad card after every N products', 'vl-core' ); ?></label></th>
				<td>
					<input class="small-text" type="number" min="2" max="12" id="ad_card_every" name="vl_homepage_settings[ad_card_every]" value="<?php echo esc_attr( $settings['ad_card_every'] ); ?>">
					<p class="description"><?php esc_html_e( 'Uses Advertisements with slot tag: between_products', 'vl-core' ); ?></p>
				</td>
			</tr>
		</table>
		<?php submit_button( __( 'Save Shop Options', 'vl-core' ) ); ?>
	</form>
	</div>
	<?php
}

/**
 * Users page.
 */
function vl_render_settings_users() {
	if ( ! vl_settings_wrap_start( __( 'Shop Users', 'vl-core' ), 'vl-settings-users' ) ) {
		return;
	}

	if ( isset( $_GET['user'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = sanitize_key( wp_unslash( $_GET['user'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'created' === $status ) {
			$created = get_transient( 'vl_new_user_' . get_current_user_id() );
			delete_transient( 'vl_new_user_' . get_current_user_id() );
			echo '<div class="notice notice-success"><p><strong>' . esc_html__( 'User created.', 'vl-core' ) . '</strong> ';
			if ( is_array( $created ) ) {
				echo esc_html(
					sprintf(
						__( 'Login: %1$s | Password: %2$s | Role: %3$s', 'vl-core' ),
						$created['login'],
						$created['pass'],
						function_exists( 'vl_role_label' ) ? vl_role_label( $created['role'] ) : $created['role']
					)
				);
			}
			echo '</p></div>';
		} elseif ( 'exists' === $status ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Username or email already exists.', 'vl-core' ) . '</p></div>';
		} elseif ( 'missing' === $status ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Username and email are required.', 'vl-core' ) . '</p></div>';
		} elseif ( 'error' === $status ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Could not create user.', 'vl-core' ) . '</p></div>';
		}
	}
	?>
	<p class="description"><?php esc_html_e( 'Create separate logins for staff and editors.', 'vl-core' ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="vl-create-user-form">
		<input type="hidden" name="action" value="vl_create_shop_user">
		<?php wp_nonce_field( 'vl_create_shop_user', 'vl_create_shop_user_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="vl_user_name"><?php esc_html_e( 'Full name', 'vl-core' ); ?></label></th>
				<td><input class="regular-text" type="text" id="vl_user_name" name="vl_user_name"></td>
			</tr>
			<tr>
				<th scope="row"><label for="vl_user_login"><?php esc_html_e( 'Username *', 'vl-core' ); ?></label></th>
				<td><input class="regular-text" type="text" id="vl_user_login" name="vl_user_login" required autocomplete="off"></td>
			</tr>
			<tr>
				<th scope="row"><label for="vl_user_email"><?php esc_html_e( 'Email *', 'vl-core' ); ?></label></th>
				<td><input class="regular-text" type="email" id="vl_user_email" name="vl_user_email" required autocomplete="off"></td>
			</tr>
			<tr>
				<th scope="row"><label for="vl_user_pass"><?php esc_html_e( 'Password', 'vl-core' ); ?></label></th>
				<td><input class="regular-text" type="text" id="vl_user_pass" name="vl_user_pass" autocomplete="new-password" placeholder="<?php esc_attr_e( 'Leave blank to auto-generate', 'vl-core' ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="vl_user_role"><?php esc_html_e( 'Role', 'vl-core' ); ?></label></th>
				<td>
					<select id="vl_user_role" name="vl_user_role">
						<option value="vl_shop_staff"><?php esc_html_e( 'Shop Staff — Products + Orders', 'vl-core' ); ?></option>
						<option value="vl_shop_editor"><?php esc_html_e( 'Shop Editor — Products + Orders + Ads + Pages', 'vl-core' ); ?></option>
					</select>
				</td>
			</tr>
		</table>
		<?php submit_button( __( 'Create user', 'vl-core' ) ); ?>
	</form>
	<p><a class="button" href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>"><?php esc_html_e( 'View all users', 'vl-core' ); ?></a></p>
	</div>
	<?php
}

/**
 * Menus page.
 */
function vl_render_settings_menus() {
	if ( ! vl_settings_wrap_start( __( 'Menus', 'vl-core' ), 'vl-settings-menus' ) ) {
		return;
	}
	if ( isset( $_GET['menus'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Menus rebuilt.', 'vl-core' ) . '</p></div>';
	}
	?>
	<p><?php esc_html_e( 'Header menu = Home + all product categories + pages marked “Show in Header”.', 'vl-core' ); ?></p>
	<p><?php esc_html_e( 'Footer menu = pages marked “Show in Footer” (About / Contact / Privacy by default).', 'vl-core' ); ?></p>
	<p><?php esc_html_e( 'On any Page edit screen, use the box “Show this page where?” to add it to header or footer.', 'vl-core' ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="vl_resync_menus">
		<?php wp_nonce_field( 'vl_resync_menus', 'vl_resync_menus_nonce' ); ?>
		<?php submit_button( __( 'Rebuild menus now', 'vl-core' ) ); ?>
	</form>
	<p>
		<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>"><?php esc_html_e( 'Manage Pages', 'vl-core' ); ?></a>
		<a class="button" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=product_category&post_type=fashion_product' ) ); ?>"><?php esc_html_e( 'Manage Categories', 'vl-core' ); ?></a>
	</p>
	</div>
	<?php
}

/**
 * Alias kept for older links.
 */
function vl_render_settings_page() {
	vl_render_settings_identity();
}

/**
 * Apply products_per_page setting on category archives.
 *
 * @param WP_Query $query Query.
 */
function vl_shop_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_tax( 'product_category' ) || $query->is_post_type_archive( 'fashion_product' ) ) {
		$settings = vl_get_homepage_settings();
		$query->set( 'posts_per_page', max( 4, absint( $settings['products_per_page'] ) ) );
	}
}
add_action( 'pre_get_posts', 'vl_shop_pre_get_posts' );
