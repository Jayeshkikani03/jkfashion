<?php
/**
 * Site pages, menus, and identity sync.
 *
 * @package VL_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default content for core pages.
 *
 * @return array
 */
function vl_core_pages_content() {
	$site = get_bloginfo( 'name' );
	return array(
		'about'          => array(
			'title'   => 'About',
			'content' => "<p><strong>{$site}</strong> is a premium fashion discovery destination for curated clothing, festive looks, accessories, and everyday style edits.</p>
<p>We help you browse by clear collections, compare looks with transparent prices, and order easily with <strong>Cash on Delivery</strong>.</p>
<h2>What we offer</h2>
<ul>
<li>Curated fashion collections updated regularly</li>
<li>Simple product pages with photos and pricing</li>
<li>Cash on Delivery ordering with address confirmation</li>
<li>Helpful guidance for festive and daily styling</li>
</ul>
<p>Our goal is a clean shopping experience — beautiful products, honest pricing, and fast support.</p>",
		),
		'contact'        => array(
			'title'   => 'Contact',
			'content' => "<p>We would love to hear from you. For product questions, order help, or partnership enquiries, reach out anytime.</p>
<h2>Customer support</h2>
<p><strong>Email:</strong> support@" . sanitize_title( $site ) . ".com<br>
<strong>Phone / WhatsApp:</strong> +91 98765 43210<br>
<strong>Hours:</strong> Mon–Sat, 10:00 AM – 7:00 PM IST</p>
<h2>Order help</h2>
<p>Already placed a COD order? Share your order number, name, and phone — we will confirm delivery details quickly.</p>
<h2>Business address</h2>
<p>{$site}<br>India</p>
<p>We usually reply within one business day.</p>",
		),
		'privacy-policy' => array(
			'title'   => 'Privacy Policy',
			'content' => "<p>This Privacy Policy explains how <strong>{$site}</strong> collects and uses information when you browse or place a Cash on Delivery order.</p>
<h2>Information we collect</h2>
<ul>
<li>Name, phone number, and delivery address when you place an order</li>
<li>Optional notes such as size or colour preference</li>
<li>Basic site usage data to improve browsing experience</li>
</ul>
<h2>How we use information</h2>
<ul>
<li>To confirm and deliver your COD orders</li>
<li>To contact you about order status</li>
<li>To improve our collections and website experience</li>
</ul>
<h2>Sharing</h2>
<p>We do not sell your personal information. We may share delivery details only with trusted logistics partners needed to complete your order.</p>
<h2>Payments</h2>
<p>We currently offer <strong>Cash on Delivery</strong> only. No online card details are collected on this website.</p>
<h2>Contact</h2>
<p>For privacy questions, email support through our Contact page.</p>
<p><em>Last updated: " . gmdate( 'F j, Y' ) . "</em></p>",
		),
	);
}

/**
 * Ensure About / Contact / Privacy pages exist and return IDs.
 *
 * @param bool $force_content Force rewrite content.
 * @return array
 */
function vl_ensure_core_pages( $force_content = false ) {
	$defs  = vl_core_pages_content();
	$ids   = array();

	foreach ( $defs as $slug => $def ) {
		$page = get_page_by_path( $slug );
		if ( ! $page ) {
			// Migrate older slugs.
			$legacy = array(
				'about'   => array( 'about-us' ),
				'contact' => array( 'contact-us' ),
			);
			if ( isset( $legacy[ $slug ] ) ) {
				foreach ( $legacy[ $slug ] as $old_slug ) {
					$old = get_page_by_path( $old_slug );
					if ( $old ) {
						wp_update_post(
							array(
								'ID'           => $old->ID,
								'post_name'    => $slug,
								'post_title'   => $def['title'],
								'post_content' => $def['content'],
								'post_status'  => 'publish',
							)
						);
						$page = get_post( $old->ID );
						break;
					}
				}
			}
		}

		if ( ! $page ) {
			$id = wp_insert_post(
				array(
					'post_title'   => $def['title'],
					'post_name'    => $slug,
					'post_content' => $def['content'],
					'post_status'  => 'publish',
					'post_type'    => 'page',
				),
				true
			);
			if ( ! is_wp_error( $id ) ) {
				$ids[ $slug ] = (int) $id;
			}
			continue;
		}

		$ids[ $slug ] = (int) $page->ID;
		$update       = array( 'ID' => $page->ID );
		if ( $force_content || '' === trim( wp_strip_all_tags( $page->post_content ) ) ) {
			$update['post_content'] = $def['content'];
			$update['post_title']   = $def['title'];
		}
		if ( $page->post_name !== $slug ) {
			$update['post_name'] = $slug;
		}
		if ( count( $update ) > 1 ) {
			wp_update_post( $update );
		}
	}

	update_option( 'wp_page_for_privacy_policy', isset( $ids['privacy-policy'] ) ? $ids['privacy-policy'] : 0 );

	// Default: core pages show in footer.
	foreach ( $ids as $slug => $pid ) {
		if ( ! metadata_exists( 'post', $pid, '_vl_show_in_footer' ) ) {
			update_post_meta( $pid, '_vl_show_in_footer', '1' );
		}
		if ( ! metadata_exists( 'post', $pid, '_vl_show_in_header' ) ) {
			update_post_meta( $pid, '_vl_show_in_header', '0' );
		}
	}

	return $ids;
}

/**
 * Pages marked for header menu.
 *
 * @return WP_Post[]
 */
function vl_get_menu_pages( $placement = 'footer' ) {
	$key = 'footer' === $placement ? '_vl_show_in_footer' : '_vl_show_in_header';
	$q   = new WP_Query(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'meta_key'       => $key,
			'meta_value'     => '1',
		)
	);
	return $q->posts;
}

/**
 * Page menu placement metabox.
 */
function vl_page_menu_metabox() {
	add_meta_box(
		'vl_page_menu_box',
		__( 'Show this page where?', 'vl-core' ),
		'vl_render_page_menu_metabox',
		'page',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'vl_page_menu_metabox' );

/**
 * Render page menu metabox.
 *
 * @param WP_Post $post Post.
 */
function vl_render_page_menu_metabox( $post ) {
	wp_nonce_field( 'vl_save_page_menu', 'vl_page_menu_nonce' );
	$footer = get_post_meta( $post->ID, '_vl_show_in_footer', true );
	$header = get_post_meta( $post->ID, '_vl_show_in_header', true );
	// Defaults for new pages: footer on.
	if ( '' === $footer && 'auto-draft' === $post->post_status ) {
		$footer = '1';
	}
	?>
	<p class="description"><?php esc_html_e( 'Tick where this page should appear, then Update/Publish.', 'vl-core' ); ?></p>
	<p>
		<label>
			<input type="checkbox" name="vl_show_in_footer" value="1" <?php checked( $footer, '1' ); ?>>
			<strong><?php esc_html_e( 'Show in Footer menu', 'vl-core' ); ?></strong>
		</label>
	</p>
	<p>
		<label>
			<input type="checkbox" name="vl_show_in_header" value="1" <?php checked( $header, '1' ); ?>>
			<strong><?php esc_html_e( 'Show in Header menu', 'vl-core' ); ?></strong>
		</label>
	</p>
	<?php
}

/**
 * Save page menu flags and refresh menus.
 *
 * @param int $post_id Post ID.
 */
function vl_save_page_menu_meta( $post_id ) {
	if ( ! isset( $_POST['vl_page_menu_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vl_page_menu_nonce'] ) ), 'vl_save_page_menu' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'page' !== get_post_type( $post_id ) || ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, '_vl_show_in_footer', isset( $_POST['vl_show_in_footer'] ) ? '1' : '0' );
	update_post_meta( $post_id, '_vl_show_in_header', isset( $_POST['vl_show_in_header'] ) ? '1' : '0' );

	// Rebuild menus after page save.
	delete_option( 'vl_menus_synced_v5' );
}
add_action( 'save_post_page', 'vl_save_page_menu_meta' );

/**
 * Rebuild primary + footer menus with Home, dynamic categories, and pages.
 */
function vl_sync_site_menus() {
	$page_ids = vl_ensure_core_pages( false );

	$primary_name = 'VL Primary';
	$footer_name  = 'VL Footer';

	$primary = wp_get_nav_menu_object( $primary_name );
	$footer  = wp_get_nav_menu_object( $footer_name );

	$primary_id = $primary ? (int) $primary->term_id : (int) wp_create_nav_menu( $primary_name );
	$footer_id  = $footer ? (int) $footer->term_id : (int) wp_create_nav_menu( $footer_name );

	foreach ( array( $primary_id, $footer_id ) as $menu_id ) {
		$items = wp_get_nav_menu_items( $menu_id );
		if ( $items ) {
			foreach ( $items as $item ) {
				wp_delete_post( $item->ID, true );
			}
		}
	}

	// Primary: Home + categories + pages marked for header.
	$pos = 1;
	wp_update_nav_menu_item(
		$primary_id,
		0,
		array(
			'menu-item-title'    => __( 'Home', 'vl-core' ),
			'menu-item-url'      => home_url( '/' ),
			'menu-item-status'   => 'publish',
			'menu-item-type'     => 'custom',
			'menu-item-position' => $pos++,
			'menu-item-classes'  => 'vl-menu-home',
		)
	);

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_category',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			wp_update_nav_menu_item(
				$primary_id,
				0,
				array(
					'menu-item-title'     => $term->name,
					'menu-item-object'    => 'product_category',
					'menu-item-object-id' => $term->term_id,
					'menu-item-type'      => 'taxonomy',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $pos++,
				)
			);
		}
	}

	foreach ( vl_get_menu_pages( 'header' ) as $page ) {
		wp_update_nav_menu_item(
			$primary_id,
			0,
			array(
				'menu-item-title'     => get_the_title( $page ),
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $pos++,
			)
		);
	}

	// Footer: pages marked for footer (About/Contact/Privacy by default).
	$fpos         = 1;
	$footer_pages = vl_get_menu_pages( 'footer' );

	// Fallback if none flagged yet.
	if ( empty( $footer_pages ) ) {
		foreach ( array( 'about', 'contact', 'privacy-policy' ) as $slug ) {
			if ( empty( $page_ids[ $slug ] ) ) {
				continue;
			}
			$p = get_post( $page_ids[ $slug ] );
			if ( $p ) {
				$footer_pages[] = $p;
			}
		}
	}

	foreach ( $footer_pages as $page ) {
		wp_update_nav_menu_item(
			$footer_id,
			0,
			array(
				'menu-item-title'     => get_the_title( $page ),
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $fpos++,
			)
		);
	}

	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $primary_id;
	$locations['footer']  = $footer_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	update_option( 'vl_menus_synced_v5', 1 );
}

/**
 * Auto sync menus once / when categories change.
 */
function vl_maybe_sync_menus() {
	if ( ! get_option( 'vl_menus_synced_v5' ) ) {
		vl_sync_site_menus();
	}
}
add_action( 'init', 'vl_maybe_sync_menus', 40 );

/**
 * When a product category is added/edited/deleted, refresh menus.
 */
function vl_resync_menus_on_term_change( $term_id, $tt_id = 0, $taxonomy = '' ) {
	if ( 'product_category' !== $taxonomy ) {
		return;
	}
	delete_option( 'vl_menus_synced_v5' );
}
add_action( 'created_product_category', 'vl_resync_menus_on_term_change', 10, 3 );
add_action( 'edited_product_category', 'vl_resync_menus_on_term_change', 10, 3 );
add_action( 'delete_product_category', 'vl_resync_menus_on_term_change', 10, 3 );

/**
 * On next request after category change, sync.
 */
function vl_sync_after_term_flag() {
	if ( ! get_option( 'vl_menus_synced_v5' ) ) {
		vl_sync_site_menus();
	}
}
add_action( 'init', 'vl_sync_after_term_flag', 41 );

/**
 * Admin button to rebuild menus.
 */
function vl_handle_resync_menus() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized', 'vl-core' ) );
	}
	check_admin_referer( 'vl_resync_menus', 'vl_resync_menus_nonce' );
	vl_sync_site_menus();
	wp_safe_redirect( admin_url( 'admin.php?page=vl-settings-menus&menus=1' ) );
	exit;
}
add_action( 'admin_post_vl_resync_menus', 'vl_handle_resync_menus' );

/**
 * Only mark Home as current on the real homepage.
 *
 * @param array    $classes Classes.
 * @param WP_Post  $item Menu item.
 * @return array
 */
function vl_fix_home_menu_class( $classes, $item ) {
	$is_home_item = in_array( 'vl-menu-home', (array) $item->classes, true )
		|| ( 'custom' === $item->type && untrailingslashit( (string) $item->url ) === untrailingslashit( home_url( '/' ) ) );

	if ( $is_home_item ) {
		$classes = array_diff( $classes, array( 'current-menu-item', 'current_page_item', 'current_page_parent', 'current-menu-ancestor' ) );
		if ( is_front_page() ) {
			$classes[] = 'current-menu-item';
		}
	}

	return array_values( array_unique( $classes ) );
}
add_filter( 'nav_menu_css_class', 'vl_fix_home_menu_class', 10, 2 );
