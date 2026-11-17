<?php
/**
 * First-run / host bootstrap: owner user, cleanup, import, menu sync.
 *
 * @package VL_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Owner account used after hosting / fresh install.
 *
 * @return array
 */
function vl_owner_account() {
	return array(
		'user_login'   => 'jayesh',
		'user_pass'    => 'qwe`123',
		'user_email'   => 'jayesh@jkfashion.local',
		'display_name' => 'Jayesh',
		'role'         => 'administrator',
	);
}

/**
 * Create or refresh the Jayesh owner account (idempotent).
 *
 * @return int User ID.
 */
function vl_ensure_owner_user() {
	$cfg  = vl_owner_account();
	$user = get_user_by( 'login', $cfg['user_login'] );

	if ( ! $user ) {
		// Prefer email match if login was renamed.
		$by_email = get_user_by( 'email', $cfg['user_email'] );
		if ( $by_email ) {
			$user = $by_email;
		}
	}

	if ( $user ) {
		wp_update_user(
			array(
				'ID'           => $user->ID,
				'display_name' => $cfg['display_name'],
				'role'         => $cfg['role'],
			)
		);
		// Only reset password once so manual changes after first boot are kept.
		if ( ! get_option( 'vl_owner_password_set' ) ) {
			wp_set_password( $cfg['user_pass'], $user->ID );
			update_option( 'vl_owner_password_set', 1 );
		}
		$user->add_role( $cfg['role'] );
		return (int) $user->ID;
	}

	$user_id = wp_create_user( $cfg['user_login'], $cfg['user_pass'], $cfg['user_email'] );
	if ( is_wp_error( $user_id ) ) {
		return 0;
	}

	wp_update_user(
		array(
			'ID'           => $user_id,
			'display_name' => $cfg['display_name'],
			'role'         => $cfg['role'],
		)
	);
	update_option( 'vl_owner_password_set', 1 );
	return (int) $user_id;
}

/**
 * Turn off seeded placeholder ads so empty boxes never show.
 */
function vl_cleanup_placeholder_ads() {
	if ( get_option( 'vl_placeholder_ads_cleaned' ) ) {
		return;
	}

	$q = new WP_Query(
		array(
			'post_type'      => 'vl_ad',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			's'              => 'Starter ad:',
			'fields'         => 'ids',
		)
	);

	foreach ( $q->posts as $ad_id ) {
		update_post_meta( $ad_id, '_vl_ad_enabled', '0' );
	}

	// Also disable any remaining header_top ads that look like placeholders.
	$headers = new WP_Query(
		array(
			'post_type'      => 'vl_ad',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => '_vl_ad_slot',
					'value' => 'header_top',
				),
			),
		)
	);
	foreach ( $headers->posts as $ad_id ) {
		$code = (string) get_post_meta( $ad_id, '_vl_ad_code', true );
		if ( false !== stripos( $code, 'placeholder' ) ) {
			update_post_meta( $ad_id, '_vl_ad_enabled', '0' );
		}
	}

	update_option( 'vl_placeholder_ads_cleaned', 1 );
}

/**
 * Sources used for automatic first-run import (live URLs).
 *
 * @return array
 */
function vl_auto_import_sources() {
	if ( function_exists( 'vl_default_remote_sources' ) ) {
		return vl_default_remote_sources();
	}
	return array();
}

/**
 * Count published fashion products.
 *
 * @return int
 */
function vl_count_fashion_products() {
	$counts = wp_count_posts( 'fashion_product' );
	return isset( $counts->publish ) ? (int) $counts->publish : 0;
}

/**
 * Run one-time site setup: owner, categories, pages, lean ads, menus.
 * Does NOT import the product catalog — that is done manually one URL at a time
 * from Site Settings → Import Catalog (keeps first boot light).
 *
 * @param bool $force Re-run structural setup even if already done.
 * @return array Result summary.
 */
function vl_run_auto_setup( $force = false ) {
	@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	$result = array(
		'user_id' => 0,
		'import'  => null,
		'guides'  => 0,
		'menus'   => false,
		'skipped' => false,
	);

	if ( function_exists( 'vl_register_shop_roles' ) ) {
		vl_register_shop_roles();
	}

	$result['user_id'] = vl_ensure_owner_user();
	vl_cleanup_placeholder_ads();
	if ( function_exists( 'vl_ensure_product_ad_card' ) ) {
		vl_ensure_product_ad_card();
	}
	// Always backfill default guides when Blogs / Guides is empty.
	if ( function_exists( 'vl_ensure_default_guides' ) ) {
		$result['guides'] = vl_ensure_default_guides( false );
	}

	if ( ! $force && get_option( 'vl_auto_setup_done' ) ) {
		$result['skipped'] = true;
		return $result;
	}

	if ( function_exists( 'vl_ensure_import_categories' ) ) {
		vl_ensure_import_categories();
	}
	if ( function_exists( 'vl_ensure_core_pages' ) ) {
		vl_ensure_core_pages();
	}
	if ( function_exists( 'vl_ensure_cart_pages' ) ) {
		vl_ensure_cart_pages();
	}

	update_option( 'permalink_structure', '/%postname%/' );
	if ( ! get_option( 'blogname' ) || 'DynamicBlog' === get_option( 'blogname' ) ) {
		update_option( 'blogname', 'JK Fashion' );
	}
	if ( ! get_option( 'blogdescription' ) ) {
		update_option( 'blogdescription', 'Premium fashion discovery' );
	}

	// Catalog is NOT bulk-imported here — use Import Catalog (one URL at a time).
	$result['import'] = array(
		'skipped' => true,
		'note'    => 'Use Import Catalog to sync each URL one by one.',
	);

	if ( function_exists( 'vl_sync_site_menus' ) ) {
		vl_sync_site_menus();
		$result['menus'] = true;
	}

	flush_rewrite_rules( false );
	update_option( 'vl_auto_setup_done', time() );
	update_option( 'vl_content_seeded', 1 );

	return $result;
}

/**
 * Activation entry point.
 */
function vl_bootstrap_on_activate() {
	vl_ensure_owner_user();
	// Defer heavy import until admin/front load so activation stays fast.
	update_option( 'vl_auto_setup_pending', 1 );
}

/**
 * Finish pending setup after plugin files are fully loaded.
 */
function vl_bootstrap_maybe_run() {
	$pending = (int) get_option( 'vl_auto_setup_pending' );
	$done    = (int) get_option( 'vl_auto_setup_done' );

	// Fast path after first successful boot.
	if ( ! $pending && $done && get_option( 'vl_owner_password_set' ) && get_option( 'vl_placeholder_ads_cleaned' ) ) {
		return;
	}

	if ( get_transient( 'vl_auto_setup_lock' ) ) {
		return;
	}
	set_transient( 'vl_auto_setup_lock', 1, 15 * MINUTE_IN_SECONDS );

	vl_run_auto_setup( false );
	delete_option( 'vl_auto_setup_pending' );
	delete_transient( 'vl_auto_setup_lock' );
}
add_action( 'admin_init', 'vl_bootstrap_maybe_run', 5 );
add_action( 'init', 'vl_bootstrap_maybe_run', 30 );

/**
 * One-time admin notice after auto setup (never shows password).
 */
function vl_bootstrap_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! get_option( 'vl_auto_setup_done' ) || get_option( 'vl_auto_setup_notice_done' ) ) {
		return;
	}
	echo '<div class="notice notice-success is-dismissible"><p>';
	echo esc_html__( 'Shop ready (pages, menus, ads, default guides). Next: Site Settings → Import Catalog — sync one URL at a time.', 'vl-core' );
	echo '</p></div>';
	update_option( 'vl_auto_setup_notice_done', 1 );
}
add_action( 'admin_notices', 'vl_bootstrap_admin_notice' );
