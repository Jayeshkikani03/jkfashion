<?php
/**
 * Shop roles for different users (Admin / Editor / Staff).
 *
 * @package VL_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register custom roles.
 */
function vl_register_shop_roles() {
	// Shop Editor — products, orders, ads, pages, media (no plugins/settings).
	$editor_caps = array(
		'read'                   => true,
		'upload_files'           => true,
		'edit_posts'             => true,
		'edit_others_posts'      => true,
		'publish_posts'          => true,
		'delete_posts'           => true,
		'delete_others_posts'    => true,
		'edit_pages'             => true,
		'edit_others_pages'      => true,
		'publish_pages'          => true,
		'delete_pages'           => true,
		'edit_published_posts'   => true,
		'delete_published_posts' => true,
		'edit_published_pages'   => true,
		'delete_published_pages' => true,
		'moderate_comments'      => true,
		'vl_manage_shop'         => true,
		'vl_manage_orders'       => true,
		'vl_manage_ads'          => true,
	);

	$existing_editor = get_role( 'vl_shop_editor' );
	if ( ! $existing_editor ) {
		add_role( 'vl_shop_editor', __( 'Shop Editor', 'vl-core' ), $editor_caps );
	} else {
		foreach ( $editor_caps as $cap => $grant ) {
			$existing_editor->add_cap( $cap, $grant );
		}
	}

	// Shop Staff — products + orders only (simpler for daily work).
	$staff_caps = array(
		'read'                   => true,
		'upload_files'           => true,
		'edit_posts'             => true,
		'edit_others_posts'      => true,
		'publish_posts'          => true,
		'delete_posts'           => true,
		'delete_others_posts'    => true,
		'edit_published_posts'   => true,
		'delete_published_posts' => true,
		'vl_manage_shop'         => true,
		'vl_manage_orders'       => true,
	);

	$existing_staff = get_role( 'vl_shop_staff' );
	if ( ! $existing_staff ) {
		add_role( 'vl_shop_staff', __( 'Shop Staff', 'vl-core' ), $staff_caps );
	} else {
		foreach ( $staff_caps as $cap => $grant ) {
			$existing_staff->add_cap( $cap, $grant );
		}
	}

	// Give admins shop caps too.
	$admin = get_role( 'administrator' );
	if ( $admin ) {
		$admin->add_cap( 'vl_manage_shop' );
		$admin->add_cap( 'vl_manage_orders' );
		$admin->add_cap( 'vl_manage_ads' );
	}
}
add_action( 'init', 'vl_register_shop_roles', 5 );

/**
 * Whether current user is a limited shop user (not full admin).
 *
 * @return bool
 */
function vl_is_limited_shop_user() {
	if ( current_user_can( 'manage_options' ) ) {
		return false;
	}
	$user = wp_get_current_user();
	$roles = (array) $user->roles;
	return in_array( 'vl_shop_editor', $roles, true ) || in_array( 'vl_shop_staff', $roles, true ) || in_array( 'editor', $roles, true );
}

/**
 * Hide advanced / unused menus for editors/staff (whitelist style).
 */
function vl_limit_menus_for_shop_users() {
	if ( ! vl_is_limited_shop_user() ) {
		return;
	}

	$user  = wp_get_current_user();
	$roles = (array) $user->roles;
	$staff = in_array( 'vl_shop_staff', $roles, true );

	$allowed = array(
		'index.php',
		'edit.php?post_type=fashion_product',
		'edit.php?post_type=vl_order',
		'upload.php',
		'profile.php',
		'users.php', // profile often under users for some roles
	);

	if ( ! $staff ) {
		$allowed[] = 'edit.php?post_type=vl_ad';
		$allowed[] = 'edit.php?post_type=page';
	}

	global $menu;
	if ( ! is_array( $menu ) ) {
		return;
	}

	foreach ( $menu as $i => $item ) {
		$slug  = isset( $item[2] ) ? (string) $item[2] : '';
		$title = isset( $item[0] ) ? wp_strip_all_tags( $item[0] ) : '';

		// Always kill WP learning / promo menus.
		if ( false !== stripos( $title, 'Learn WordPress' )
			|| false !== stripos( $title, 'Get Involved' )
			|| false !== stripos( $slug, 'wordpress.org' )
			|| false !== stripos( $slug, 'learn.wordpress' ) ) {
			unset( $menu[ $i ] );
			continue;
		}

		if ( '' === $slug || 'separator1' === $slug || 'separator2' === $slug || 'separator-last' === $slug || 0 === strpos( $slug, 'separator' ) ) {
			unset( $menu[ $i ] );
			continue;
		}

		if ( ! in_array( $slug, $allowed, true ) ) {
			unset( $menu[ $i ] );
		}
	}
}
add_action( 'admin_menu', 'vl_limit_menus_for_shop_users', 10000 );


/**
 * Create staff/editor user from Site Settings form.
 */
function vl_handle_create_shop_user() {
	if ( ! current_user_can( 'create_users' ) ) {
		wp_die( esc_html__( 'Unauthorized', 'vl-core' ) );
	}
	check_admin_referer( 'vl_create_shop_user', 'vl_create_shop_user_nonce' );

	$username = isset( $_POST['vl_user_login'] ) ? sanitize_user( wp_unslash( $_POST['vl_user_login'] ) ) : '';
	$email    = isset( $_POST['vl_user_email'] ) ? sanitize_email( wp_unslash( $_POST['vl_user_email'] ) ) : '';
	$name     = isset( $_POST['vl_user_name'] ) ? sanitize_text_field( wp_unslash( $_POST['vl_user_name'] ) ) : '';
	$role     = isset( $_POST['vl_user_role'] ) ? sanitize_key( wp_unslash( $_POST['vl_user_role'] ) ) : 'vl_shop_staff';
	$pass     = isset( $_POST['vl_user_pass'] ) ? (string) wp_unslash( $_POST['vl_user_pass'] ) : '';

	$allowed_roles = array( 'vl_shop_staff', 'vl_shop_editor', 'editor' );
	if ( ! in_array( $role, $allowed_roles, true ) ) {
		$role = 'vl_shop_staff';
	}

	if ( '' === $username || '' === $email ) {
		wp_safe_redirect( admin_url( 'admin.php?page=vl-settings-users&user=missing' ) );
		exit;
	}

	if ( username_exists( $username ) || email_exists( $email ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=vl-settings-users&user=exists' ) );
		exit;
	}

	if ( '' === $pass ) {
		$pass = wp_generate_password( 12, true );
	}

	$user_id = wp_insert_user(
		array(
			'user_login'   => $username,
			'user_email'   => $email,
			'user_pass'    => $pass,
			'display_name' => $name ? $name : $username,
			'first_name'   => $name,
			'role'         => $role,
		)
	);

	if ( is_wp_error( $user_id ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=vl-settings-users&user=error' ) );
		exit;
	}

	// Store once for admin notice (not emailed automatically).
	set_transient(
		'vl_new_user_' . get_current_user_id(),
		array(
			'login' => $username,
			'pass'  => $pass,
			'role'  => $role,
		),
		60
	);

	wp_safe_redirect( admin_url( 'admin.php?page=vl-settings-users&user=created' ) );
	exit;
}
add_action( 'admin_post_vl_create_shop_user', 'vl_handle_create_shop_user' );

/**
 * Role label helper.
 *
 * @param string $role Role key.
 * @return string
 */
function vl_role_label( $role ) {
	$map = array(
		'vl_shop_staff'  => __( 'Shop Staff (Products + Orders)', 'vl-core' ),
		'vl_shop_editor' => __( 'Shop Editor (Products + Orders + Ads + Pages)', 'vl-core' ),
		'editor'         => __( 'Editor (WordPress default)', 'vl-core' ),
	);
	return isset( $map[ $role ] ) ? $map[ $role ] : $role;
}
