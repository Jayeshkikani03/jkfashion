<?php
/**
 * Advertisement manager.
 *
 * @package VL_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available ad slots.
 *
 * @return array
 */
function vl_get_ad_slots() {
	return array(
		'header_top'         => __( 'Header top banner', 'vl-core' ),
		'below_nav'          => __( 'Below navigation', 'vl-core' ),
		'below_hero'         => __( 'Below hero banner', 'vl-core' ),
		'before_category'    => __( 'Before category content', 'vl-core' ),
		'between_products'   => __( 'Between product cards', 'vl-core' ),
		'between_paragraphs' => __( 'Between article paragraphs', 'vl-core' ),
		'sidebar'            => __( 'Sidebar advertisement', 'vl-core' ),
		'before_related'     => __( 'Before related products', 'vl-core' ),
		'before_footer'      => __( 'Before footer', 'vl-core' ),
		'footer'             => __( 'Footer advertisement', 'vl-core' ),
	);
}

/**
 * Register ad meta boxes.
 */
function vl_add_ad_meta_boxes() {
	add_meta_box(
		'vl_ad_settings',
		__( 'Advertisement Settings', 'vl-core' ),
		'vl_render_ad_metabox',
		'vl_ad',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'vl_add_ad_meta_boxes' );

/**
 * Render ad metabox.
 *
 * @param WP_Post $post Post.
 */
function vl_render_ad_metabox( $post ) {
	wp_nonce_field( 'vl_save_ad_meta', 'vl_ad_meta_nonce' );

	$code       = get_post_meta( $post->ID, '_vl_ad_code', true );
	$enabled    = get_post_meta( $post->ID, '_vl_ad_enabled', true );
	$slot       = get_post_meta( $post->ID, '_vl_ad_slot', true );
	$priority   = get_post_meta( $post->ID, '_vl_ad_priority', true );
	$desktop    = get_post_meta( $post->ID, '_vl_ad_desktop', true );
	$mobile     = get_post_meta( $post->ID, '_vl_ad_mobile', true );
	$targets    = get_post_meta( $post->ID, '_vl_ad_targets', true );

	$enabled  = '' === $enabled ? '1' : $enabled;
	$desktop  = '' === $desktop ? '1' : $desktop;
	$mobile   = '' === $mobile ? '1' : $mobile;
	$priority = '' === $priority ? '10' : $priority;
	$targets  = is_array( $targets ) ? $targets : array( 'all' );
	$slots    = vl_get_ad_slots();

	$page_targets = array(
		'all'      => __( 'All pages', 'vl-core' ),
		'home'     => __( 'Homepage', 'vl-core' ),
		'category' => __( 'Category / collection pages', 'vl-core' ),
		'product'  => __( 'Product detail pages', 'vl-core' ),
		'article'  => __( 'Fashion guide pages', 'vl-core' ),
		'page'     => __( 'Static pages', 'vl-core' ),
	);
	?>
	<div class="vl-admin-box">
		<p class="description"><?php esc_html_e( 'How to add an ad: 1) Give a title  2) Choose Slot tag  3) Paste your AdSense/HTML code  4) Keep Enabled checked  5) Publish', 'vl-core' ); ?></p>

		<p>
			<label><input type="checkbox" name="vl_ad_enabled" value="1" <?php checked( $enabled, '1' ); ?>> <strong><?php esc_html_e( 'Enabled (show on website)', 'vl-core' ); ?></strong></label>
		</p>

		<p>
			<label for="vl_ad_slot"><strong><?php esc_html_e( 'Ad Slot Tag (where it appears)', 'vl-core' ); ?></strong></label><br>
			<select class="widefat" id="vl_ad_slot" name="vl_ad_slot">
				<?php foreach ( $slots as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $slot, $key ); ?>><?php echo esc_html( $label . ' — tag: ' . $key ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<div class="vl-slot-help">
			<strong><?php esc_html_e( 'Common slot tags:', 'vl-core' ); ?></strong>
			<ul style="margin:8px 0 0 1.1rem;">
				<li><code>header_top</code> — <?php esc_html_e( 'very top of every page', 'vl-core' ); ?></li>
				<li><code>below_hero</code> — <?php esc_html_e( 'homepage under banner', 'vl-core' ); ?></li>
				<li><code>before_category</code> — <?php esc_html_e( 'above product list', 'vl-core' ); ?></li>
				<li><code>between_products</code> — <?php esc_html_e( 'ad card inside product grid (same size as product)', 'vl-core' ); ?></li>
				<li><code>sidebar</code> — <?php esc_html_e( 'category sidebar', 'vl-core' ); ?></li>
				<li><code>before_footer</code> / <code>footer</code> — <?php esc_html_e( 'bottom of page', 'vl-core' ); ?></li>
			</ul>
		</div>

		<p>
			<label for="vl_ad_code"><strong><?php esc_html_e( 'Paste Ad HTML / AdSense code here', 'vl-core' ); ?></strong></label><br>
			<textarea class="widefat" rows="8" id="vl_ad_code" name="vl_ad_code" placeholder="&lt;script&gt;...&lt;/script&gt; or banner HTML"><?php echo esc_textarea( $code ); ?></textarea>
		</p>

		<div class="vl-admin-grid">
			<p>
				<label for="vl_ad_priority"><strong><?php esc_html_e( 'Order', 'vl-core' ); ?></strong></label><br>
				<input type="number" class="small-text" id="vl_ad_priority" name="vl_ad_priority" value="<?php echo esc_attr( $priority ); ?>">
				<span class="description"><?php esc_html_e( 'Lower = first', 'vl-core' ); ?></span>
			</p>
			<p>
				<label><input type="checkbox" name="vl_ad_desktop" value="1" <?php checked( $desktop, '1' ); ?>> <?php esc_html_e( 'Desktop', 'vl-core' ); ?></label><br>
				<label><input type="checkbox" name="vl_ad_mobile" value="1" <?php checked( $mobile, '1' ); ?>> <?php esc_html_e( 'Mobile', 'vl-core' ); ?></label>
			</p>
		</div>

		<p><strong><?php esc_html_e( 'Show on pages', 'vl-core' ); ?></strong></p>
		<?php foreach ( $page_targets as $key => $label ) : ?>
			<label class="vl-check">
				<input type="checkbox" name="vl_ad_targets[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $targets, true ) ); ?>>
				<?php echo esc_html( $label ); ?>
			</label>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Save ad meta.
 *
 * @param int $post_id Post ID.
 */
function vl_save_ad_meta( $post_id ) {
	if ( ! isset( $_POST['vl_ad_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vl_ad_meta_nonce'] ) ), 'vl_save_ad_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'vl_ad' !== get_post_type( $post_id ) ) {
		return;
	}

	$slots = array_keys( vl_get_ad_slots() );
	$slot  = isset( $_POST['vl_ad_slot'] ) ? sanitize_key( wp_unslash( $_POST['vl_ad_slot'] ) ) : 'header_top';
	if ( ! in_array( $slot, $slots, true ) ) {
		$slot = 'header_top';
	}

	$code = isset( $_POST['vl_ad_code'] ) ? wp_unslash( $_POST['vl_ad_code'] ) : '';
	$code = vl_sanitize_ad_html( $code );

	$targets = array();
	if ( isset( $_POST['vl_ad_targets'] ) && is_array( $_POST['vl_ad_targets'] ) ) {
		foreach ( wp_unslash( $_POST['vl_ad_targets'] ) as $target ) {
			$targets[] = sanitize_key( $target );
		}
	}
	if ( empty( $targets ) ) {
		$targets = array( 'all' );
	}

	update_post_meta( $post_id, '_vl_ad_code', $code );
	update_post_meta( $post_id, '_vl_ad_enabled', isset( $_POST['vl_ad_enabled'] ) ? '1' : '0' );
	update_post_meta( $post_id, '_vl_ad_slot', $slot );
	update_post_meta( $post_id, '_vl_ad_priority', isset( $_POST['vl_ad_priority'] ) ? absint( $_POST['vl_ad_priority'] ) : 10 );
	update_post_meta( $post_id, '_vl_ad_desktop', isset( $_POST['vl_ad_desktop'] ) ? '1' : '0' );
	update_post_meta( $post_id, '_vl_ad_mobile', isset( $_POST['vl_ad_mobile'] ) ? '1' : '0' );
	update_post_meta( $post_id, '_vl_ad_targets', $targets );
}
add_action( 'save_post', 'vl_save_ad_meta' );

/**
 * Detect current page target key.
 *
 * @return string
 */
function vl_get_current_ad_target() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_tax( 'product_category' ) || is_post_type_archive( 'fashion_product' ) ) {
		return 'category';
	}
	if ( is_singular( 'fashion_product' ) ) {
		return 'product';
	}
	if ( is_singular( 'fashion_article' ) || is_post_type_archive( 'fashion_article' ) ) {
		return 'article';
	}
	if ( is_page() ) {
		return 'page';
	}
	return 'all';
}

/**
 * Query enabled ads for a slot.
 *
 * @param string $slot Slot key.
 * @return WP_Post[]
 */
function vl_get_ads_for_slot( $slot ) {
	$query = new WP_Query(
		array(
			'post_type'      => 'vl_ad',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'meta_key'       => '_vl_ad_priority',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'   => '_vl_ad_enabled',
					'value' => '1',
				),
				array(
					'key'   => '_vl_ad_slot',
					'value' => $slot,
				),
			),
		)
	);

	$target = vl_get_current_ad_target();
	$ads    = array();

	foreach ( $query->posts as $ad ) {
		$targets = get_post_meta( $ad->ID, '_vl_ad_targets', true );
		if ( ! is_array( $targets ) ) {
			$targets = array( 'all' );
		}

		if ( in_array( 'all', $targets, true ) || in_array( $target, $targets, true ) ) {
			$ads[] = $ad;
		}
	}

	return $ads;
}

/**
 * Render advertisements for a slot.
 *
 * @param string $slot Slot key.
 * @param array  $args Optional args.
 */
function vl_render_ad( $slot, $args = array() ) {
	$defaults = array(
		'class' => '',
	);
	$args     = wp_parse_args( $args, $defaults );
	$ads      = vl_get_ads_for_slot( $slot );

	if ( empty( $ads ) ) {
		return;
	}

	$classes = array( 'vl-ad', 'vl-ad--' . sanitize_html_class( $slot ) );
	if ( $args['class'] ) {
		$classes[] = $args['class'];
	}

	echo '<aside class="' . esc_attr( implode( ' ', $classes ) ) . '" aria-label="' . esc_attr__( 'Advertisement', 'vl-core' ) . '">';
	echo '<span class="vl-ad__label">' . esc_html__( 'Advertisement', 'vl-core' ) . '</span>';

	foreach ( $ads as $ad ) {
		$desktop = get_post_meta( $ad->ID, '_vl_ad_desktop', true );
		$mobile  = get_post_meta( $ad->ID, '_vl_ad_mobile', true );
		$code    = get_post_meta( $ad->ID, '_vl_ad_code', true );

		if ( ! $code ) {
			continue;
		}

		$device_classes = array( 'vl-ad__unit' );
		if ( '1' !== (string) $desktop ) {
			$device_classes[] = 'vl-ad__unit--hide-desktop';
		}
		if ( '1' !== (string) $mobile ) {
			$device_classes[] = 'vl-ad__unit--hide-mobile';
		}

		echo '<div class="' . esc_attr( implode( ' ', $device_classes ) ) . '">';
		echo vl_sanitize_ad_html( $code ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized via capability-aware helper.
		echo '</div>';
	}

	echo '</aside>';
}

/**
 * Render product-grid ad cards (parallel to product cards).
 *
 * @param int $every Insert after every N products (default 4).
 * @param int $count Current product count in loop.
 * @return void
 */
function vl_maybe_render_product_ad_cards( $every = 4, $count = 0 ) {
	if ( $count < 1 || 0 !== ( $count % max( 1, (int) $every ) ) ) {
		return;
	}

	static $index = 0;
	$ads = vl_get_ads_for_slot( 'between_products' );
	if ( empty( $ads ) ) {
		return;
	}

	$ad = $ads[ $index % count( $ads ) ];
	$index++;

	set_query_var( 'vl_ad_card', $ad );
	$theme_card = locate_template( 'template-parts/products/ad-card.php' );
	if ( $theme_card ) {
		load_template( $theme_card, false );
	} else {
		vl_render_inline_ad_card( $ad );
	}
	set_query_var( 'vl_ad_card', null );
}

/**
 * Fallback inline ad card if theme file missing.
 *
 * @param WP_Post $ad Ad post.
 */
function vl_render_inline_ad_card( $ad ) {
	$code = get_post_meta( $ad->ID, '_vl_ad_code', true );
	if ( ! $code ) {
		return;
	}
	echo '<aside class="vl-ad-card" aria-label="' . esc_attr__( 'Advertisement', 'vl-core' ) . '">';
	echo '<span class="vl-ad-card__label">' . esc_html__( 'Sponsored', 'vl-core' ) . '</span>';
	echo '<div class="vl-ad-card__media">' . vl_sanitize_ad_html( $code ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</aside>';
}

/**
 * Banner-style HTML for seeded slot ads.
 *
 * @param string $label  Short label.
 * @param string $title  Title.
 * @param string $text   Supporting text.
 * @param string $url    Link URL.
 * @param string $cta    Button text.
 * @param string $bg     CSS background.
 * @param string $color  Text color.
 * @return string
 */
function vl_default_banner_html( $label, $title, $text, $url, $cta, $bg, $color = '#1a2230' ) {
	return '<a href="' . esc_url( $url ) . '" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;width:100%;padding:1rem 1.25rem;text-decoration:none;background:' . esc_attr( $bg ) . ';color:' . esc_attr( $color ) . ';border-radius:14px;">'
		. '<span style="display:block;min-width:0;">'
		. '<span style="display:block;font-size:0.7rem;letter-spacing:0.12em;text-transform:uppercase;font-weight:700;opacity:0.75;">' . esc_html( $label ) . '</span>'
		. '<strong style="display:block;margin-top:0.35rem;font-size:1.15rem;line-height:1.25;font-family:Georgia,serif;">' . esc_html( $title ) . '</strong>'
		. '<span style="display:block;margin-top:0.25rem;font-size:0.88rem;opacity:0.85;">' . esc_html( $text ) . '</span>'
		. '</span>'
		. '<span style="display:inline-block;padding:0.55rem 0.95rem;border-radius:999px;background:#1a2230;color:#fff;font-size:0.82rem;font-weight:700;white-space:nowrap;">' . esc_html( $cta ) . '</span>'
		. '</a>';
}

/**
 * Default ads for all main slots (auto-seeded).
 *
 * @return array
 */
function vl_default_slot_ads() {
	$sale = home_url( '/collection/sale-and-offers/' );
	$new  = home_url( '/collection/new-arrivals/' );
	$home = home_url( '/' );

	$cards = array(
		array(
			'title'    => 'Default: Header top',
			'slot'     => 'header_top',
			'priority' => 10,
			'html'     => vl_default_banner_html( 'Sponsored', 'Festival looks, ready to ship', 'Browse curated edits with COD available', $sale, 'Shop now', 'linear-gradient(90deg,#f7f1e8,#efe0cb)' ),
		),
		array(
			'title'    => 'Default: Below hero',
			'slot'     => 'below_hero',
			'priority' => 10,
			'html'     => vl_default_banner_html( 'Sponsored', 'New season drops', 'Fresh arrivals updated regularly', $new, 'See new', 'linear-gradient(90deg,#1a2230,#2c3648)', '#fff' ),
		),
		array(
			'title'    => 'Default: Before category',
			'slot'     => 'before_category',
			'priority' => 10,
			'html'     => vl_default_banner_html( 'Sponsored', 'Explore this collection', 'Find your next look in curated categories', $home, 'Browse', 'linear-gradient(90deg,#f3efe6,#e7d7c0)' ),
		),
		array(
			'title'    => 'Default: Sidebar',
			'slot'     => 'sidebar',
			'priority' => 10,
			'html'     => '<a href="' . esc_url( $sale ) . '" style="display:block;padding:1.25rem;text-align:center;text-decoration:none;background:linear-gradient(165deg,#1a2230,#b24a1b);color:#fff;border-radius:14px;">'
				. '<span style="display:block;font-size:0.7rem;letter-spacing:0.12em;text-transform:uppercase;opacity:0.8;font-weight:700;">Sponsored</span>'
				. '<strong style="display:block;margin-top:0.6rem;font-size:1.2rem;font-family:Georgia,serif;">Sale picks</strong>'
				. '<span style="display:block;margin-top:0.35rem;font-size:0.88rem;opacity:0.9;">Better prices, same polish</span>'
				. '</a>',
		),
		array(
			'title'    => 'Default: Before footer',
			'slot'     => 'before_footer',
			'priority' => 10,
			'html'     => vl_default_banner_html( 'Sponsored', 'Still browsing?', 'Jump back into Sale & Offers', $sale, 'Shop sale', 'linear-gradient(90deg,#efe4d4,#f7f1e8)' ),
		),
		array(
			'title'    => 'Default: Footer',
			'slot'     => 'footer',
			'priority' => 10,
			'html'     => vl_default_banner_html( 'Sponsored', 'JK Fashion', 'Premium fashion discovery', $home, 'Home', 'linear-gradient(90deg,#1a2230,#2c3648)', '#fff' ),
		),
		array(
			'title'    => 'Grid ad: Sale edit',
			'slot'     => 'between_products',
			'priority' => 5,
			'html'     => '<a href="' . esc_url( $sale ) . '" style="display:flex;flex-direction:column;justify-content:flex-end;height:100%;min-height:260px;padding:1.25rem;text-decoration:none;background:linear-gradient(165deg,#1a2230 0%,#2c3648 55%,#b24a1b 140%);color:#fff;border-radius:inherit;">'
				. '<span style="font-size:0.72rem;letter-spacing:0.12em;text-transform:uppercase;opacity:0.85;font-weight:700;">Sponsored</span>'
				. '<strong style="display:block;margin-top:0.85rem;font-size:1.35rem;line-height:1.2;font-family:Georgia,serif;">Sale &amp; Offers</strong>'
				. '<span style="display:block;margin-top:0.45rem;font-size:0.9rem;opacity:0.9;">Curated looks at better prices</span>'
				. '<span style="display:inline-block;margin-top:1.1rem;padding:0.55rem 0.9rem;border-radius:999px;background:#fff;color:#1a2230;font-size:0.82rem;font-weight:700;">Shop sale</span>'
				. '</a>',
		),
		array(
			'title'    => 'Grid ad: New arrivals',
			'slot'     => 'between_products',
			'priority' => 6,
			'html'     => '<a href="' . esc_url( $new ) . '" style="display:flex;flex-direction:column;justify-content:flex-end;height:100%;min-height:260px;padding:1.25rem;text-decoration:none;background:linear-gradient(165deg,#f7f1e8 0%,#e8d7c0 60%,#d36a2f 130%);color:#1a2230;border-radius:inherit;">'
				. '<span style="font-size:0.72rem;letter-spacing:0.12em;text-transform:uppercase;opacity:0.7;font-weight:700;">Sponsored</span>'
				. '<strong style="display:block;margin-top:0.85rem;font-size:1.35rem;line-height:1.2;font-family:Georgia,serif;">New Arrivals</strong>'
				. '<span style="display:block;margin-top:0.45rem;font-size:0.9rem;opacity:0.85;">Fresh drops for the season</span>'
				. '<span style="display:inline-block;margin-top:1.1rem;padding:0.55rem 0.9rem;border-radius:999px;background:#1a2230;color:#fff;font-size:0.82rem;font-weight:700;">Explore new</span>'
				. '</a>',
		),
	);

	return $cards;
}

/**
 * Backward-compatible alias.
 *
 * @return array
 */
function vl_default_product_ad_cards() {
	$out = array();
	foreach ( vl_default_slot_ads() as $card ) {
		if ( 'between_products' === $card['slot'] ) {
			$out[] = $card;
		}
	}
	return $out;
}

/**
 * Ensure default ads exist for all main slots (auto seed).
 */
function vl_ensure_product_ad_card() {
	if ( get_option( 'vl_default_ads_seeded_v3' ) ) {
		// Still fill any missing slot that has zero enabled ads.
		$needed = false;
		foreach ( array( 'header_top', 'below_hero', 'before_category', 'between_products', 'sidebar', 'before_footer', 'footer' ) as $slot ) {
			if ( empty( vl_get_ads_for_slot( $slot ) ) ) {
				$needed = true;
				break;
			}
		}
		if ( ! $needed ) {
			return;
		}
	}

	$created = 0;
	foreach ( vl_default_slot_ads() as $card ) {
		$existing = vl_get_ads_for_slot( $card['slot'] );
		if ( ! empty( $existing ) ) {
			continue;
		}

		$ad_id = wp_insert_post(
			array(
				'post_title'  => $card['title'],
				'post_type'   => 'vl_ad',
				'post_status' => 'publish',
			),
			true
		);
		if ( is_wp_error( $ad_id ) ) {
			continue;
		}
		update_post_meta( $ad_id, '_vl_ad_code', $card['html'] );
		update_post_meta( $ad_id, '_vl_ad_enabled', '1' );
		update_post_meta( $ad_id, '_vl_ad_slot', $card['slot'] );
		update_post_meta( $ad_id, '_vl_ad_priority', (int) $card['priority'] );
		update_post_meta( $ad_id, '_vl_ad_desktop', '1' );
		update_post_meta( $ad_id, '_vl_ad_mobile', '1' );
		update_post_meta( $ad_id, '_vl_ad_targets', array( 'all' ) );
		$created++;
	}

	update_option( 'vl_default_ads_seeded_v3', 1 );
	update_option( 'vl_product_ad_card_seeded_v2', 1 );
	update_option( 'vl_product_ad_card_seeded', 1 );
}
add_action( 'init', 'vl_ensure_product_ad_card', 50 );
