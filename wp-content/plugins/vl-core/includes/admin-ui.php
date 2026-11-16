<?php
/**
 * Simplified admin UI for products and ads.
 *
 * @package VL_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter displayed titles for products.
 *
 * @param string $title Title.
 * @param int    $post_id Post ID.
 * @return string
 */
function vl_filter_product_title( $title, $post_id = 0 ) {
	if ( $post_id && 'fashion_product' === get_post_type( $post_id ) ) {
		return vl_clean_product_title( $title );
	}
	return $title;
}
add_filter( 'the_title', 'vl_filter_product_title', 10, 2 );

/**
 * Enqueue admin assets for product/ad screens.
 *
 * @param string $hook Hook.
 */
function vl_admin_enqueue( $hook ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen ) {
		return;
	}

	$need = in_array( $screen->post_type, array( 'fashion_product', 'vl_ad' ), true )
		|| 'dashboard' === $screen->id
		|| 'toplevel_page_vl-settings' === $screen->id;

	if ( ! $need ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'vl-admin', VL_CORE_URL . 'assets/admin.css', array(), VL_CORE_VERSION );
	wp_enqueue_script( 'vl-admin', VL_CORE_URL . 'assets/admin.js', array( 'jquery' ), VL_CORE_VERSION, true );
	wp_localize_script(
		'vl-admin',
		'vlAdmin',
		array(
			'title'         => __( 'Select product images', 'vl-core' ),
			'button'        => __( 'Use these images', 'vl-core' ),
			'featuredTitle' => __( 'Select product photo', 'vl-core' ),
			'featuredBtn'   => __( 'Use this photo', 'vl-core' ),
			'noImage'       => __( 'No image selected', 'vl-core' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'vl_admin_enqueue' );

/**
 * Hide unused admin menus for a clean shop panel.
 */
function vl_simplify_admin_menu() {
	remove_menu_page( 'edit.php' );                   // Posts
	remove_menu_page( 'edit-comments.php' );          // Comments
	remove_menu_page( 'edit.php?post_type=fashion_article' ); // Fashion Guides
	remove_submenu_page( 'index.php', 'update-core.php' );

	// Extra WP promo / learning menus.
	remove_menu_page( 'https://wordpress.org/support/forum/requests-and-feedback' );
	remove_menu_page( 'https://learn.wordpress.org/' );
	remove_menu_page( 'https://wordpress.org/support/' );
	remove_menu_page( 'https://wordpress.org/documentation/' );
	remove_menu_page( 'wporg' );
	remove_menu_page( 'learn-wordpress' );
	remove_menu_page( 'get-involved' );

	global $menu, $submenu;
	if ( is_array( $menu ) ) {
		foreach ( $menu as $i => $item ) {
			$title = isset( $item[0] ) ? wp_strip_all_tags( $item[0] ) : '';
			$slug  = isset( $item[2] ) ? (string) $item[2] : '';
			$kill  = array( 'Get Involved', 'Learn WordPress', 'WordPress.org', 'Documentation', 'Support', 'Feedback' );
			if ( in_array( $title, $kill, true ) || false !== stripos( $slug, 'learn.wordpress' ) || false !== stripos( $slug, 'wordpress.org' ) ) {
				unset( $menu[ $i ] );
			}
		}
	}

	if ( isset( $submenu['index.php'] ) ) {
		foreach ( $submenu['index.php'] as $i => $item ) {
			if ( isset( $item[2] ) && in_array( $item[2], array( 'update-core.php', 'update-core.php?force-check=1' ), true ) ) {
				unset( $submenu['index.php'][ $i ] );
			}
		}
	}
}
add_action( 'admin_menu', 'vl_simplify_admin_menu', 9999 );

/**
 * Reorder left menu: Dashboard, Products, Ads, Media, Pages, Settings.
 */
function vl_admin_menu_order( $menu_order ) {
	return array(
		'index.php',
		'edit.php?post_type=fashion_product',
		'edit.php?post_type=vl_order',
		'edit.php?post_type=vl_ad',
		'upload.php',
		'edit.php?post_type=page',
		'vl-settings', // Site Settings.
		'themes.php',
		'plugins.php',
		'users.php',
		'tools.php',
		'options-general.php',
	);
}
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', 'vl_admin_menu_order' );

/**
 * Logo / site name in admin bar → Dashboard (not frontend).
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar.
 */
function vl_admin_bar_logo_to_dashboard( $wp_admin_bar ) {
	$node = $wp_admin_bar->get_node( 'site-name' );
	if ( $node ) {
		$wp_admin_bar->add_node(
			array(
				'id'    => 'site-name',
				'title' => $node->title,
				'href'  => admin_url(),
				'meta'  => array(
					'title' => __( 'Go to Dashboard', 'vl-core' ),
				),
			)
		);
	}

	// Remove "Visit Site" confusion under site name; add clear Dashboard + Visit.
	$wp_admin_bar->remove_node( 'view-site' );
	$wp_admin_bar->add_node(
		array(
			'parent' => 'site-name',
			'id'     => 'vl-dashboard',
			'title'  => __( 'Dashboard', 'vl-core' ),
			'href'   => admin_url(),
		)
	);
	$wp_admin_bar->add_node(
		array(
			'parent' => 'site-name',
			'id'     => 'vl-view-site',
			'title'  => __( 'View Website', 'vl-core' ),
			'href'   => home_url( '/' ),
		)
	);

	// Clean + New menu: Product first, then Ad, Page, Media.
	$wp_admin_bar->remove_node( 'new-post' );
	$wp_admin_bar->remove_node( 'new-content' );

	$wp_admin_bar->add_node(
		array(
			'id'    => 'new-content',
			'title' => '<span class="ab-icon" aria-hidden="true"></span><span class="ab-label">' . esc_html__( 'Add', 'vl-core' ) . '</span>',
			'href'  => admin_url( 'post-new.php?post_type=fashion_product' ),
			'meta'  => array(
				'title' => __( 'Add new', 'vl-core' ),
			),
		)
	);
	$wp_admin_bar->add_node(
		array(
			'parent' => 'new-content',
			'id'     => 'vl-new-product',
			'title'  => __( 'Product', 'vl-core' ),
			'href'   => admin_url( 'post-new.php?post_type=fashion_product' ),
		)
	);
	$user  = wp_get_current_user();
	$staff = in_array( 'vl_shop_staff', (array) $user->roles, true );

	if ( ! $staff && current_user_can( 'publish_posts' ) ) {
		$wp_admin_bar->add_node(
			array(
				'parent' => 'new-content',
				'id'     => 'vl-new-ad',
				'title'  => __( 'Advertisement', 'vl-core' ),
				'href'   => admin_url( 'post-new.php?post_type=vl_ad' ),
			)
		);
	}
	if ( ! $staff && current_user_can( 'edit_pages' ) ) {
		$wp_admin_bar->add_node(
			array(
				'parent' => 'new-content',
				'id'     => 'vl-new-page',
				'title'  => __( 'Page', 'vl-core' ),
				'href'   => admin_url( 'post-new.php?post_type=page' ),
			)
		);
	}
	$wp_admin_bar->add_node(
		array(
			'parent' => 'new-content',
			'id'     => 'vl-new-media',
			'title'  => __( 'Media', 'vl-core' ),
			'href'   => admin_url( 'media-new.php' ),
		)
	);
}
add_action( 'admin_bar_menu', 'vl_admin_bar_logo_to_dashboard', 100 );

/**
 * Remove WordPress logo from admin bar completely.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar.
 */
function vl_admin_bar_remove_wp_logo( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'wp-logo' );
	$wp_admin_bar->remove_node( 'wp-logo-external' );
	$wp_admin_bar->remove_node( 'wp-logo-about' );
	$wp_admin_bar->remove_node( 'about' );
	$wp_admin_bar->remove_node( 'wporg' );
	$wp_admin_bar->remove_node( 'documentation' );
	$wp_admin_bar->remove_node( 'learn' );
	$wp_admin_bar->remove_node( 'support-forums' );
	$wp_admin_bar->remove_node( 'feedback' );
	$wp_admin_bar->remove_node( 'comments' );
}
add_action( 'admin_bar_menu', 'vl_admin_bar_remove_wp_logo', 999 );

/**
 * Hide WP logo via CSS as backup.
 */
function vl_admin_hide_wp_logo_css() {
	echo '<style>
		#wpadminbar #wp-admin-bar-wp-logo { display: none !important; }
		#wpadminbar .ab-top-menu > li.menupop.hover > .ab-sub-wrapper { margin-top: 0; }
		#adminmenu .wp-menu-separator { display: none; }
	</style>';
}
add_action( 'admin_head', 'vl_admin_hide_wp_logo_css' );

/**
 * Block unused update / about screens → send to dashboard.
 */
function vl_block_unused_admin_pages() {
	global $pagenow;
	$blocked = array( 'update-core.php', 'about.php', 'credits.php', 'freedoms.php', 'privacy.php', 'contribute.php' );
	if ( in_array( $pagenow, $blocked, true ) && current_user_can( 'edit_posts' ) ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}
add_action( 'admin_init', 'vl_block_unused_admin_pages' );

/**
 * Simplify product editor meta boxes.
 */
function vl_simplify_product_screen() {
	remove_meta_box( 'slugdiv', 'fashion_product', 'normal' );
	remove_meta_box( 'authordiv', 'fashion_product', 'normal' );
	remove_meta_box( 'revisionsdiv', 'fashion_product', 'normal' );
	remove_meta_box( 'commentsdiv', 'fashion_product', 'normal' );
	remove_meta_box( 'commentstatusdiv', 'fashion_product', 'normal' );
	remove_meta_box( 'postexcerpt', 'fashion_product', 'normal' );
	remove_meta_box( 'postcustom', 'fashion_product', 'normal' );
	remove_meta_box( 'trackbacksdiv', 'fashion_product', 'normal' );

	// Featured image stays, but we also have picker in main box.
	remove_meta_box( 'postimagediv', 'fashion_product', 'side' );
	add_meta_box(
		'postimagediv',
		__( 'Main Product Photo', 'vl-core' ),
		'post_thumbnail_meta_box',
		'fashion_product',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes_fashion_product', 'vl_simplify_product_screen', 20 );

/**
 * Product editor help banner.
 */
function vl_product_edit_help() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'fashion_product' !== $screen->post_type ) {
		return;
	}
	if ( ! in_array( $screen->base, array( 'post' ), true ) ) {
		return;
	}
	?>
	<div class="vl-product-steps notice notice-info">
		<p><strong><?php esc_html_e( 'Add product in 5 steps:', 'vl-core' ); ?></strong></p>
		<ol>
			<li><?php esc_html_e( 'Type product name at the top', 'vl-core' ); ?></li>
			<li><?php esc_html_e( 'Click “Upload product photo” (or use Main Product Photo on the right)', 'vl-core' ); ?></li>
			<li><?php esc_html_e( 'Enter Price (₹)', 'vl-core' ); ?></li>
			<li><?php esc_html_e( 'Tick a Product Category on the right', 'vl-core' ); ?></li>
			<li><?php esc_html_e( 'Click Publish / Update', 'vl-core' ); ?></li>
		</ol>
	</div>
	<?php
}
add_action( 'edit_form_after_title', 'vl_product_edit_help' );

/**
 * Product list columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function vl_product_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['vl_thumb'] = __( 'Image', 'vl-core' );
			$new['vl_price'] = __( 'Price', 'vl-core' );
		}
	}
	unset( $new['comments'] );
	return $new;
}
add_filter( 'manage_fashion_product_posts_columns', 'vl_product_columns' );

/**
 * Render product columns.
 *
 * @param string $column Column.
 * @param int    $post_id Post ID.
 */
function vl_product_column_content( $column, $post_id ) {
	if ( 'vl_thumb' === $column ) {
		if ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail( $post_id, array( 56, 56 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo '&mdash;';
		}
	}
	if ( 'vl_price' === $column ) {
		echo wp_kses_post( vl_get_product_price_html( $post_id ) );
	}
}
add_action( 'manage_fashion_product_posts_custom_column', 'vl_product_column_content', 10, 2 );

/**
 * Ad list columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function vl_ad_columns( $columns ) {
	$columns['vl_slot']    = __( 'Slot tag', 'vl-core' );
	$columns['vl_enabled'] = __( 'Status', 'vl-core' );
	return $columns;
}
add_filter( 'manage_vl_ad_posts_columns', 'vl_ad_columns' );

/**
 * Ad column content.
 *
 * @param string $column Column.
 * @param int    $post_id Post ID.
 */
function vl_ad_column_content( $column, $post_id ) {
	if ( 'vl_slot' === $column ) {
		$slots = vl_get_ad_slots();
		$slot  = get_post_meta( $post_id, '_vl_ad_slot', true );
		echo esc_html( isset( $slots[ $slot ] ) ? $slots[ $slot ] . ' (' . $slot . ')' : $slot );
	}
	if ( 'vl_enabled' === $column ) {
		$enabled = get_post_meta( $post_id, '_vl_ad_enabled', true );
		echo '1' === (string) $enabled ? esc_html__( 'Enabled', 'vl-core' ) : esc_html__( 'Disabled', 'vl-core' );
	}
}
add_action( 'manage_vl_ad_posts_custom_column', 'vl_ad_column_content', 10, 2 );

/**
 * Dashboard help widget.
 */
function vl_admin_help_widget() {
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );

	$site = get_bloginfo( 'name' );
	wp_add_dashboard_widget(
		'vl_help_widget',
		sprintf(
			/* translators: %s: site name */
			__( '%s — Editor Dashboard', 'vl-core' ),
			$site
		),
		'vl_render_help_widget'
	);

	wp_add_dashboard_widget(
		'vl_stats_widget',
		__( 'Shop overview', 'vl-core' ),
		'vl_render_stats_widget'
	);
}
add_action( 'wp_dashboard_setup', 'vl_admin_help_widget', 20 );

/**
 * Count helper.
 *
 * @param string $post_type Post type.
 * @return int
 */
function vl_count_posts_safe( $post_type ) {
	$counts = wp_count_posts( $post_type );
	return isset( $counts->publish ) ? (int) $counts->publish : 0;
}

/**
 * Render stats widget.
 */
function vl_render_stats_widget() {
	$products = vl_count_posts_safe( 'fashion_product' );
	$orders   = vl_count_posts_safe( 'vl_order' );
	$ads      = vl_count_posts_safe( 'vl_ad' );
	$cats     = (int) wp_count_terms(
		array(
			'taxonomy'   => 'product_category',
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $cats ) ) {
		$cats = 0;
	}
	?>
	<div class="vl-dash-stats">
		<a class="vl-dash-stat" href="<?php echo esc_url( admin_url( 'edit.php?post_type=fashion_product' ) ); ?>">
			<strong><?php echo esc_html( number_format_i18n( $products ) ); ?></strong>
			<span><?php esc_html_e( 'Products', 'vl-core' ); ?></span>
		</a>
		<a class="vl-dash-stat" href="<?php echo esc_url( admin_url( 'edit.php?post_type=vl_order' ) ); ?>">
			<strong><?php echo esc_html( number_format_i18n( $orders ) ); ?></strong>
			<span><?php esc_html_e( 'COD Orders', 'vl-core' ); ?></span>
		</a>
		<a class="vl-dash-stat" href="<?php echo esc_url( admin_url( 'edit.php?post_type=vl_ad' ) ); ?>">
			<strong><?php echo esc_html( number_format_i18n( $ads ) ); ?></strong>
			<span><?php esc_html_e( 'Ads', 'vl-core' ); ?></span>
		</a>
		<a class="vl-dash-stat" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=product_category&post_type=fashion_product' ) ); ?>">
			<strong><?php echo esc_html( number_format_i18n( $cats ) ); ?></strong>
			<span><?php esc_html_e( 'Categories', 'vl-core' ); ?></span>
		</a>
	</div>
	<?php
}

/**
 * Render help widget.
 */
function vl_render_help_widget() {
	$user  = wp_get_current_user();
	$site  = get_bloginfo( 'name' );
	$is_staff = in_array( 'vl_shop_staff', (array) $user->roles, true );
	?>
	<div class="vl-dash-welcome">
		<p class="vl-dash-welcome__hi">
			<?php
			printf(
				/* translators: 1: user display name, 2: site name */
				esc_html__( 'Welcome %1$s — this is the %2$s shop panel.', 'vl-core' ),
				esc_html( $user->display_name ? $user->display_name : $user->user_login ),
				esc_html( $site )
			);
			?>
		</p>
		<p><?php esc_html_e( 'Use the left menu only. Everything you need is listed below.', 'vl-core' ); ?></p>
	</div>

	<div class="vl-dash-cards">
		<div class="vl-dash-card">
			<strong>1. <?php esc_html_e( 'Add a product', 'vl-core' ); ?></strong>
			<p><?php esc_html_e( 'Products → Add Product → upload photo → enter price → choose category → Publish.', 'vl-core' ); ?></p>
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=fashion_product' ) ); ?>"><?php esc_html_e( 'Add Product', 'vl-core' ); ?></a>
		</div>
		<div class="vl-dash-card">
			<strong>2. <?php esc_html_e( 'Check COD orders', 'vl-core' ); ?></strong>
			<p><?php esc_html_e( 'When a customer places an order on the website, it appears here with name, phone and address.', 'vl-core' ); ?></p>
			<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=vl_order' ) ); ?>"><?php esc_html_e( 'View Orders', 'vl-core' ); ?></a>
		</div>
		<?php if ( ! $is_staff ) : ?>
		<div class="vl-dash-card">
			<strong>3. <?php esc_html_e( 'Add an ad', 'vl-core' ); ?></strong>
			<p><?php esc_html_e( 'Advertisements → Add New → choose slot tag → paste HTML → Publish.', 'vl-core' ); ?></p>
			<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=vl_ad' ) ); ?>"><?php esc_html_e( 'Add Ad', 'vl-core' ); ?></a>
		</div>
		<?php endif; ?>
		<div class="vl-dash-card">
			<strong><?php echo $is_staff ? '3.' : '4.'; ?> <?php esc_html_e( 'View website', 'vl-core' ); ?></strong>
			<p><?php esc_html_e( 'Open the live shop to check products and mobile view.', 'vl-core' ); ?></p>
			<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open Site', 'vl-core' ); ?></a>
		</div>
	</div>

	<?php if ( current_user_can( 'create_users' ) ) : ?>
		<p class="vl-dash-admin-note">
			<?php esc_html_e( 'Admin tip: create Shop Staff / Shop Editor users from Site Settings → Create shop user.', 'vl-core' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=vl-settings' ) ); ?>"><?php esc_html_e( 'Open Site Settings', 'vl-core' ); ?></a>
		</p>
	<?php endif; ?>
	<?php
}

/**
 * One-click clean all product titles.
 */
function vl_handle_clean_titles() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized', 'vl-core' ) );
	}
	check_admin_referer( 'vl_clean_titles', 'vl_clean_titles_nonce' );

	$q = new WP_Query(
		array(
			'post_type'      => 'fashion_product',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_status'    => 'any',
		)
	);

	$count = 0;
	foreach ( $q->posts as $id ) {
		$old = get_the_title( $id );
		$new = vl_clean_product_title( $old );
		if ( $new && $new !== $old ) {
			wp_update_post(
				array(
					'ID'         => $id,
					'post_title' => $new,
				)
			);
			$count++;
		}
	}

	wp_safe_redirect( admin_url( 'edit.php?post_type=fashion_product&cleaned=' . $count ) );
	exit;
}
add_action( 'admin_post_vl_clean_titles', 'vl_handle_clean_titles' );

/**
 * Notice + clean button on products list.
 */
function vl_products_admin_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-fashion_product' !== $screen->id ) {
		return;
	}
	if ( isset( $_GET['cleaned'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="notice notice-success"><p>' . esc_html( sprintf( __( 'Cleaned %s product names.', 'vl-core' ), absint( $_GET['cleaned'] ) ) ) . '</p></div>'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	?>
	<div class="notice notice-info" style="padding:12px;">
		<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=fashion_product' ) ); ?>"><?php esc_html_e( 'Add New Product', 'vl-core' ); ?></a>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;margin-left:8px;">
			<input type="hidden" name="action" value="vl_clean_titles">
			<?php wp_nonce_field( 'vl_clean_titles', 'vl_clean_titles_nonce' ); ?>
			<button class="button"><?php esc_html_e( 'Clean product names', 'vl-core' ); ?></button>
		</form>
	</div>
	<?php
}
add_action( 'admin_notices', 'vl_products_admin_notice' );

/**
 * Clean admin footer (no WordPress promo text).
 *
 * @return string
 */
function vl_admin_footer_text() {
	return esc_html( get_bloginfo( 'name' ) );
}
add_filter( 'admin_footer_text', 'vl_admin_footer_text' );

/**
 * Hide WP version in admin footer.
 *
 * @return string
 */
function vl_admin_footer_version() {
	return '';
}
add_filter( 'update_footer', 'vl_admin_footer_version', 11 );
