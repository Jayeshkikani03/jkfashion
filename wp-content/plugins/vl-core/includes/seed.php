<?php
/**
 * Seed categories, sample products, articles, menus, and ads.
 *
 * @package VL_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default fashion guide posts (used by auto-setup when Blogs / Guides is empty).
 *
 * @return array
 */
function vl_default_guide_posts() {
	return array(
		array(
			'title'   => 'How to build a festive capsule wardrobe',
			'topic'   => 'Festive style',
			'content' => "<p>A festive capsule does not need dozens of pieces. Start with one statement ethnic set, one versatile blazer, a refined saree or dress option, and two pairs of footwear that work across events.</p>\n\n<p>Keep accessories intentional: one bold jewelry set and one minimal everyday option. This keeps packing simple and styling fast for Diwali, weddings, and family gatherings.</p>\n\n<p>At JK Fashion, browse <strong>Festival Fashion</strong> and <strong>Luxury Collection</strong> for pieces that mix easily without looking overdone.</p>",
		),
		array(
			'title'   => 'Styling slides beyond loungewear',
			'topic'   => 'Footwear',
			'content' => "<p>Slides can move beyond home wear when the silhouette is clean and the color is intentional.</p>\n\n<p>Pair matte black slides with tailored trousers and a knit top, or choose ivory slides with linen separates for daytime polish. Avoid overly distressed pairings if you want a more elevated result.</p>\n\n<p>Explore our <strong>Best Slides</strong> edits for easy everyday footwear that still looks finished.</p>",
		),
		array(
			'title'   => 'Luxury looks that still feel wearable',
			'topic'   => 'Luxury',
			'content' => "<p>Luxury does not have to feel formal. Soft champagne tones, structured neutrals, and careful embroidery keep outfits special while remaining wearable.</p>\n\n<p>Focus on fabric quality and fit first, then add one decorative detail so the look stays refined — perfect for receptions, dinners, and festive evenings.</p>\n\n<p>Shop <strong>Luxury Collection</strong> for elevated pieces you can actually wear again.</p>",
		),
		array(
			'title'   => 'New season layering ideas for everyday polish',
			'topic'   => 'Trends',
			'content' => "<p>Layering is the fastest way to refresh what you already own. Start with a clean base tee or kurta, add a light cardigan or structured jacket, then finish with one accent — jewelry, a belt, or sharper footwear.</p>\n\n<p>Keep colors in one family (cream, ivory, navy, soft brown) so mixes feel intentional instead of busy.</p>\n\n<p>Check <strong>New Arrivals</strong> and <strong>Trending Fashion</strong> for pieces that layer well for work and weekend.</p>",
		),
		array(
			'title'   => 'Smart COD shopping tips for fashion finds',
			'topic'   => 'Shopping tips',
			'content' => "<p>Cash on Delivery is simple — still choose thoughtfully. Check size notes, fabric description, and photos carefully before placing an order.</p>\n\n<p>Keep your phone reachable so delivery confirmation is smooth. If you are ordering for an event, leave a few days buffer for shipping.</p>\n\n<p>Orders of ₹99+ also unlock a next-look coupon on your thank-you page — a small perk for continuing your style edit.</p>",
		),
		array(
			'title'   => 'Women’s wardrobe staples that always work',
			'topic'   => 'Women\'s fashion',
			'content' => "<p>A strong everyday edit usually includes: one easy kurti or top set, one polished dress or co-ord, comfortable footwear, and a light outer layer.</p>\n\n<p>Neutral bases make festive jewelry and seasonal colors easier to swap in. Build from staples, then add trend pieces sparingly.</p>\n\n<p>Browse <strong>Women’s Fashion</strong> for wearable everyday and occasion-ready looks.</p>",
		),
	);
}

/**
 * Ensure default Blogs / Guides exist (idempotent).
 *
 * @param bool $force Recreate missing defaults even if some guides exist.
 * @return int Number of guides created.
 */
function vl_ensure_default_guides( $force = false ) {
	$counts = wp_count_posts( 'fashion_article' );
	$publish = isset( $counts->publish ) ? (int) $counts->publish : 0;

	if ( ! $force && $publish > 0 ) {
		update_option( 'vl_guides_seeded', 1 );
		return 0;
	}

	if ( ! $force && get_option( 'vl_guides_seeded' ) && $publish > 0 ) {
		return 0;
	}

	$created = 0;
	$topics  = array();

	foreach ( vl_default_guide_posts() as $article ) {
		$exists = get_page_by_title( $article['title'], OBJECT, 'fashion_article' );
		if ( $exists ) {
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_title'   => $article['title'],
				'post_content' => $article['content'],
				'post_status'  => 'publish',
				'post_type'    => 'fashion_article',
			),
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		$topic_name = ! empty( $article['topic'] ) ? $article['topic'] : 'Style notes';
		if ( ! isset( $topics[ $topic_name ] ) ) {
			$term = term_exists( $topic_name, 'guide_topic' );
			if ( ! $term ) {
				$term = wp_insert_term( $topic_name, 'guide_topic' );
			}
			if ( ! is_wp_error( $term ) ) {
				$topics[ $topic_name ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
			}
		}
		if ( ! empty( $topics[ $topic_name ] ) ) {
			wp_set_object_terms( $post_id, array( $topics[ $topic_name ] ), 'guide_topic' );
		}

		$created++;
	}

	update_option( 'vl_guides_seeded', 1 );
	return $created;
}

/**
 * Backfill default guides on admin/front if none exist yet.
 */
function vl_maybe_seed_default_guides() {
	if ( get_option( 'vl_guides_seeded' ) ) {
		$counts  = wp_count_posts( 'fashion_article' );
		$publish = isset( $counts->publish ) ? (int) $counts->publish : 0;
		if ( $publish > 0 ) {
			return;
		}
	}
	vl_ensure_default_guides( false );
}
add_action( 'init', 'vl_maybe_seed_default_guides', 45 );

/**
 * Handle seed form submission.
 */
function vl_handle_seed_content() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized', 'vl-core' ) );
	}

	check_admin_referer( 'vl_seed_content', 'vl_seed_nonce' );

	$result = vl_seed_content();
	$msg    = $result ? 'seeded=1' : 'seeded=0';

	wp_safe_redirect( add_query_arg( $msg, admin_url( 'admin.php?page=vl-settings' ) ) );
	exit;
}
add_action( 'admin_post_vl_seed_content', 'vl_handle_seed_content' );

/**
 * Admin notice after seeding.
 */
function vl_seed_admin_notice() {
	if ( ! isset( $_GET['page'] ) || 'vl-settings' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	if ( isset( $_GET['seeded'] ) && '1' === $_GET['seeded'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Starter content seeded successfully.', 'vl-core' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'vl_seed_admin_notice' );

/**
 * Seed site content.
 *
 * @return bool
 */
function vl_seed_content() {
	if ( get_option( 'vl_content_seeded' ) ) {
		return false;
	}

	$categories = array(
		'new-arrivals'           => 'New Arrivals',
		'fashion'                => 'Fashion',
		'luxury-collection'      => 'Luxury Collection',
		'sale-and-offers'        => 'Sale and Offers',
		'trending-fashion'       => 'Trending Fashion',
		'best-slides'            => 'Best Slides',
		'womens-fashion'         => 'Women\'s Fashion',
		'mens-fashion'           => 'Men\'s Fashion',
		'festival-fashion'       => 'Festival Fashion',
		'jewelry-and-accessories'=> 'Jewelry and Accessories',
	);

	$term_ids = array();
	foreach ( $categories as $slug => $name ) {
		$existing = term_exists( $slug, 'product_category' );
		if ( $existing ) {
			$term_ids[ $slug ] = (int) ( is_array( $existing ) ? $existing['term_id'] : $existing );
			continue;
		}
		$created = wp_insert_term( $name, 'product_category', array( 'slug' => $slug ) );
		if ( ! is_wp_error( $created ) ) {
			$term_ids[ $slug ] = (int) $created['term_id'];
		}
	}

	$products = array(
		array(
			'title'    => 'Ivory Festival Embroidered Kurta Set',
			'content'  => "An original starter look for festive evenings.\n\nCrafted with a soft ivory base and champagne embroidery accents, this kurta set pairs clean lines with occasion-ready detailing. Style with gold jewelry and soft flats for gatherings, or keep it minimal for elevated daytime wear.",
			'price'    => 2499,
			'sale'     => 0,
			'cats'     => array( 'festival-fashion', 'womens-fashion', 'new-arrivals' ),
			'flags'    => array( '_vl_is_new' => '1', '_vl_is_featured' => '1' ),
		),
		array(
			'title'    => 'Midnight Structured Blazer',
			'content'  => "A tailored midnight blazer designed for polished everyday styling.\n\nLayer over a knit top and trousers for work, or dress it down with denim for evening plans. The silhouette stays sharp without feeling stiff.",
			'price'    => 3299,
			'sale'     => 2799,
			'cats'     => array( 'mens-fashion', 'fashion', 'sale-and-offers' ),
			'flags'    => array( '_vl_is_on_sale' => '1', '_vl_is_trending' => '1' ),
		),
		array(
			'title'    => 'Champagne Silk Blend Saree Edit',
			'content'  => "A luminous champagne silk-blend saree edit created for modern celebrations.\n\nSoft drape, refined border work, and an easy blouse pairing make this a versatile luxury pick for weddings and receptions.",
			'price'    => 5499,
			'sale'     => 0,
			'cats'     => array( 'luxury-collection', 'womens-fashion' ),
			'flags'    => array( '_vl_is_luxury' => '1', '_vl_is_featured' => '1' ),
		),
		array(
			'title'    => 'Coastal Linen Shirt Dress',
			'content'  => "A breezy linen shirt dress for warm-weather days.\n\nBelted at the waist or worn loose, it transitions from brunch to travel with minimal effort. Pair with slides and a woven bag.",
			'price'    => 1899,
			'sale'     => 0,
			'cats'     => array( 'trending-fashion', 'womens-fashion', 'new-arrivals' ),
			'flags'    => array( '_vl_is_trending' => '1', '_vl_is_new' => '1' ),
		),
		array(
			'title'    => 'Matte Black Everyday Slides',
			'content'  => "Minimal matte black slides for easy casual outfits.\n\nA clean strap profile and versatile color make these a staple with loungewear, denim, or lightweight ethnic separates.",
			'price'    => 899,
			'sale'     => 749,
			'cats'     => array( 'best-slides', 'sale-and-offers' ),
			'flags'    => array( '_vl_is_on_sale' => '1', '_vl_is_trending' => '1' ),
		),
		array(
			'title'    => 'Heritage Pearl Drop Earrings',
			'content'  => "Soft pearl drops with a contemporary silhouette.\n\nDesigned to finish both festive and evening looks without overpowering the outfit.",
			'price'    => 1299,
			'sale'     => 0,
			'cats'     => array( 'jewelry-and-accessories', 'festival-fashion' ),
			'flags'    => array( '_vl_is_featured' => '1' ),
		),
		array(
			'title'    => 'Olive Utility Cargo Trousers',
			'content'  => "Relaxed utility trousers with a modern olive wash.\n\nPair with a fitted tee and sneakers for weekend styling, or elevate with a crisp shirt.",
			'price'    => 1599,
			'sale'     => 0,
			'cats'     => array( 'mens-fashion', 'trending-fashion' ),
			'flags'    => array( '_vl_is_trending' => '1' ),
		),
		array(
			'title'    => 'Rose Garden Printed Co-ord Set',
			'content'  => "A coordinated printed set with soft rose motifs.\n\nWear together for a finished look, or style the pieces separately through the week.",
			'price'    => 2199,
			'sale'     => 1899,
			'cats'     => array( 'fashion', 'womens-fashion', 'sale-and-offers', 'new-arrivals' ),
			'flags'    => array( '_vl_is_on_sale' => '1', '_vl_is_new' => '1' ),
		),
		array(
			'title'    => 'Sandstone Textured Knitted Polo',
			'content'  => "A sandstone knit polo with a clean collar and soft hand-feel.\n\nAn easy staple for smart-casual days and travel wardrobes.",
			'price'    => 1499,
			'sale'     => 0,
			'cats'     => array( 'mens-fashion', 'fashion' ),
			'flags'    => array( '_vl_is_featured' => '1' ),
		),
		array(
			'title'    => 'Ivory Cross-Strap Slides',
			'content'  => "Light ivory slides with a soft cross-strap design.\n\nIdeal for resort days, indoor-outdoor transitions, and relaxed summer outfits.",
			'price'    => 999,
			'sale'     => 0,
			'cats'     => array( 'best-slides', 'new-arrivals' ),
			'flags'    => array( '_vl_is_new' => '1' ),
		),
	);

	foreach ( $products as $product ) {
		$post_id = wp_insert_post(
			array(
				'post_title'   => $product['title'],
				'post_content' => $product['content'],
				'post_status'  => 'publish',
				'post_type'    => 'fashion_product',
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		update_post_meta( $post_id, '_vl_price', $product['price'] );
		update_post_meta( $post_id, '_vl_sale_price', $product['sale'] );
		update_post_meta( $post_id, '_vl_external_url', '' );

		foreach ( array( '_vl_is_featured', '_vl_is_new', '_vl_is_trending', '_vl_is_luxury', '_vl_is_on_sale' ) as $flag ) {
			update_post_meta( $post_id, $flag, isset( $product['flags'][ $flag ] ) ? $product['flags'][ $flag ] : '0' );
		}

		$assign = array();
		foreach ( $product['cats'] as $slug ) {
			if ( isset( $term_ids[ $slug ] ) ) {
				$assign[] = $term_ids[ $slug ];
			}
		}
		if ( $assign ) {
			wp_set_object_terms( $post_id, $assign, 'product_category' );
		}
	}

	vl_ensure_default_guides( true );

	// Pages.
	$about_id = wp_insert_post(
		array(
			'post_title'   => 'About Us',
			'post_name'    => 'about-us',
			'post_content' => "Vastralaxmi Fashion is a premium discovery storefront for curated clothing, festive edits, slides, and styling guides.\n\nWe focus on clearer category browsing, polished product presentation, and thoughtful advertisement placement without cluttering the shopping journey.",
			'post_status'  => 'publish',
			'post_type'    => 'page',
		)
	);

	$contact_id = wp_insert_post(
		array(
			'post_title'   => 'Contact Us',
			'post_name'    => 'contact-us',
			'post_content' => "Have a question about collections, partnerships, or styling guidance?\n\nEmail: hello@vastralaxmi.example\n\nWe typically respond within one business day.",
			'post_status'  => 'publish',
			'post_type'    => 'page',
		)
	);

	// Menus.
	$primary_id = wp_create_nav_menu( 'Primary' );
	$footer_id  = wp_create_nav_menu( 'Footer' );

	if ( ! is_wp_error( $primary_id ) ) {
		$order = 1;
		wp_update_nav_menu_item(
			$primary_id,
			0,
			array(
				'menu-item-title'  => 'Home',
				'menu-item-url'    => home_url( '/' ),
				'menu-item-status' => 'publish',
				'menu-item-type'   => 'custom',
				'menu-item-position' => $order++,
			)
		);

		foreach ( $categories as $slug => $name ) {
			if ( ! isset( $term_ids[ $slug ] ) ) {
				continue;
			}
			wp_update_nav_menu_item(
				$primary_id,
				0,
				array(
					'menu-item-title'     => $name,
					'menu-item-object'    => 'product_category',
					'menu-item-object-id' => $term_ids[ $slug ],
					'menu-item-type'      => 'taxonomy',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $order++,
				)
			);
		}

		wp_update_nav_menu_item(
			$primary_id,
			0,
			array(
				'menu-item-title'     => 'Fashion Guides',
				'menu-item-url'       => get_post_type_archive_link( 'fashion_article' ),
				'menu-item-status'    => 'publish',
				'menu-item-type'      => 'custom',
				'menu-item-position'  => $order++,
			)
		);

		if ( $about_id && ! is_wp_error( $about_id ) ) {
			wp_update_nav_menu_item(
				$primary_id,
				0,
				array(
					'menu-item-title'     => 'About Us',
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $about_id,
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $order++,
				)
			);
		}

		if ( $contact_id && ! is_wp_error( $contact_id ) ) {
			wp_update_nav_menu_item(
				$primary_id,
				0,
				array(
					'menu-item-title'     => 'Contact Us',
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $contact_id,
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $order++,
				)
			);
		}

		$locations            = get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = (int) $primary_id;
		$locations['footer']  = ! is_wp_error( $footer_id ) ? (int) $footer_id : (int) $primary_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	if ( ! is_wp_error( $footer_id ) ) {
		foreach ( array( $about_id, $contact_id ) as $page_id ) {
			if ( $page_id && ! is_wp_error( $page_id ) ) {
				wp_update_nav_menu_item(
					$footer_id,
					0,
					array(
						'menu-item-title'     => get_the_title( $page_id ),
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $page_id,
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					)
				);
			}
		}
	}

	update_option( 'blogname', 'Vastralaxmi Fashion' );
	update_option( 'blogdescription', 'Premium fashion discovery' );
	update_option( 'show_on_front', 'posts' );
	update_option( 'permalink_structure', '/%postname%/' );
	flush_rewrite_rules();

	update_option( 'vl_content_seeded', 1 );
	return true;
}
