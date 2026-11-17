<?php
/**
 * Plugin Name: VL Core
 * Description: Vastralaxmi Fashion core — products, articles, categories, ads, and homepage settings.
 * Version: 1.0.0
 * Author: Vastralaxmi
 * Text Domain: vl-core
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VL_CORE_VERSION', '1.9.5' );
define( 'VL_CORE_FILE', __FILE__ );
define( 'VL_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'VL_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once VL_CORE_PATH . 'includes/helpers.php';
require_once VL_CORE_PATH . 'includes/roles.php';
require_once VL_CORE_PATH . 'includes/post-types.php';
require_once VL_CORE_PATH . 'includes/product-meta.php';
require_once VL_CORE_PATH . 'includes/cart.php';
require_once VL_CORE_PATH . 'includes/orders.php';
require_once VL_CORE_PATH . 'includes/ads.php';
require_once VL_CORE_PATH . 'includes/pages.php';
require_once VL_CORE_PATH . 'includes/settings.php';
require_once VL_CORE_PATH . 'includes/seed.php';
require_once VL_CORE_PATH . 'includes/import-catalog.php';
require_once VL_CORE_PATH . 'includes/bootstrap.php';
require_once VL_CORE_PATH . 'includes/admin-ui.php';
require_once VL_CORE_PATH . 'includes/seo.php';

/**
 * Flush rewrite rules on activation + queue owner / pending auto-setup.
 */
function vl_core_activate() {
	require_once VL_CORE_PATH . 'includes/roles.php';
	require_once VL_CORE_PATH . 'includes/bootstrap.php';
	vl_register_shop_roles();
	vl_register_post_types();
	vl_register_taxonomies();
	vl_bootstrap_on_activate();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'vl_core_activate' );

/**
 * Flush rewrite rules on deactivation.
 */
function vl_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'vl_core_deactivate' );

/**
 * Boot plugin modules.
 */
function vl_core_init() {
	vl_register_post_types();
	vl_register_taxonomies();
}
add_action( 'init', 'vl_core_init' );
