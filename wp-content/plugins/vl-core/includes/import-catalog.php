<?php
/**
 * Import catalog from JSON URL or local files.
 *
 * @package VL_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Category slug map from source category names.
 *
 * @return array
 */
function vl_import_category_map() {
	return array(
		'fashion_26'             => 'fashion',
		'Best_sellers'           => 'new-arrivals',
		'Luxury_Cloth'           => 'luxury-collection',
		'Best_cloth'             => 'luxury-collection',
		'cloth_were'             => 'sale-and-offers',
		'Best_Slides'            => 'best-slides',
		'Casual_Slides'          => 'best-slides',
		'Shoes'                  => 'best-slides',
		'Shoes_Slides'           => 'best-slides',
		'Slides_Choice'          => 'best-slides',
		'Slides_Collection'      => 'best-slides',
		'Slides_One'             => 'best-slides',
		'Slippers_Collection'    => 'best-slides',
		'Trending_Fashion'       => 'trending-fashion',
		'Trend_Fashion'          => 'trending-fashion',
		'Trend_Collection'       => 'trending-fashion',
		'Trend_Choice'           => 'trending-fashion',
		'Trending_Collection'    => 'trending-fashion',
		'Trending_Hub'           => 'trending-fashion',
		'Casual_Style'           => 'trending-fashion',
		'Festival_Fashion'       => 'festival-fashion',
		'Navrati_special_offer'  => 'festival-fashion',
		'Best_Jewelry'           => 'jewelry-and-accessories',
		'Jewelry_Accessories'    => 'jewelry-and-accessories',
		'Jewelry_Collection'     => 'jewelry-and-accessories',
		'Clothing_Fashion'       => 'fashion',
		'Fashion_Collection'     => 'fashion',
		'Fashion_Style'          => 'fashion',
		'Fashion_World'          => 'fashion',
		'dress'                  => 'womens-fashion',
		'Dress_Collection'       => 'womens-fashion',
		'Best_Wear'              => 'womens-fashion',
		'Fashion_Boutique_hub'   => 'womens-fashion',
		'Best_Collection'        => 'luxury-collection',
		'Best_One'               => 'luxury-collection',
		'Best_Choice'            => 'trending-fashion',
		'New_Arrival'            => 'new-arrivals',
		'New_Collection'         => 'new-arrivals',
		'Kids_Choice'            => 'fashion',
		'Best_Toys'              => 'fashion',
		// social.vastralaxmi.com
		'STYLE_ORBIT'            => 'fashion',
		'ZENORA_VOGUE'           => 'luxury-collection',
		'ZAVIRA_VOGUE'           => 'luxury-collection',
		'VELORA_LUXE'            => 'luxury-collection',
		'STYLE_CREST'            => 'fashion',
		'AUREL_VOGUE'            => 'luxury-collection',
		'trendy_corner'          => 'trending-fashion',
		'daily_chic'             => 'womens-fashion',
		'seasonal_charm'         => 'new-arrivals',
		'daily_fashion'          => 'fashion',
		'happy_picks'            => 'sale-and-offers',
		'NEW_STYLE'              => 'new-arrivals',
		'Summer_Shirt_Haul'      => 'mens-fashion',
		'diwali_charm'           => 'festival-fashion',
		'diwali_mahotsav'        => 'festival-fashion',
		// view.vastralaxmi.com
		'festive_wala'           => 'festival-fashion',
		'Festival_1'             => 'festival-fashion',
		'Nightbird_trending'     => 'trending-fashion',
		'Nightbird'              => 'trending-fashion',
		'Trend_tour'           => 'trending-fashion',
		'Trending_fashion'       => 'trending-fashion',
		'Trending_1'             => 'trending-fashion',
		'Trending_2'             => 'trending-fashion',
		'Trending_3'             => 'trending-fashion',
		'Vastra_Guru'            => 'fashion',
		'Insta_Fashion'          => 'womens-fashion',
		'Vastraveli'             => 'fashion',
		'vastraveli'             => 'fashion',
		'Fashion_Mantra'         => 'fashion',
		'summer_2026'            => 'new-arrivals',
	);
}

/**
 * Ensure unified categories exist.
 *
 * @return array slug => term_id
 */
function vl_ensure_import_categories() {
	$categories = array(
		'new-arrivals'            => 'New Arrivals',
		'fashion'                 => 'Fashion',
		'luxury-collection'       => 'Luxury Collection',
		'sale-and-offers'         => 'Sale & Offers',
		'trending-fashion'        => 'Trending Fashion',
		'best-slides'             => 'Best Slides',
		'womens-fashion'          => "Women's Fashion",
		'mens-fashion'            => "Men's Fashion",
		'festival-fashion'        => 'Festival Fashion',
		'jewelry-and-accessories' => 'Jewelry & Accessories',
	);

	$ids = array();
	foreach ( $categories as $slug => $name ) {
		$existing = term_exists( $slug, 'product_category' );
		if ( $existing ) {
			$ids[ $slug ] = (int) ( is_array( $existing ) ? $existing['term_id'] : $existing );
			continue;
		}
		$created = wp_insert_term( $name, 'product_category', array( 'slug' => $slug ) );
		if ( ! is_wp_error( $created ) ) {
			$ids[ $slug ] = (int) $created['term_id'];
		}
	}
	return $ids;
}

/**
 * Saved JSON sources (user-added URLs).
 *
 * @return array
 */
function vl_get_json_sources() {
	$sources = get_option( 'vl_json_sources', array() );
	return is_array( $sources ) ? $sources : array();
}

/**
 * Built-in live catalog URLs (primary sync sources).
 *
 * @return array
 */
function vl_default_remote_sources() {
	return array(
		array(
			'id'     => 'url-next',
			'label'  => 'next.vastralaxmi.com',
			'type'   => 'url',
			'url'    => 'https://next.vastralaxmi.com/products/products.json',
			'file'   => '',
			'origin' => 'https://next.vastralaxmi.com',
		),
		array(
			'id'     => 'url-sale',
			'label'  => 'sale.vastralaxmi.com',
			'type'   => 'url',
			'url'    => 'https://sale.vastralaxmi.com/products/products.json',
			'file'   => '',
			'origin' => 'https://sale.vastralaxmi.com',
		),
		array(
			'id'     => 'url-trending',
			'label'  => 'trending.vastralaxmi.com',
			'type'   => 'url',
			'url'    => 'https://trending.vastralaxmi.com/products/products.json',
			'file'   => '',
			'origin' => 'https://trending.vastralaxmi.com',
		),
		array(
			'id'     => 'url-info',
			'label'  => 'info.vastralaxmi.com',
			'type'   => 'url',
			'url'    => 'https://www.info.vastralaxmi.com/products/products.json',
			'file'   => '',
			'origin' => 'https://www.info.vastralaxmi.com',
		),
		array(
			'id'     => 'url-social',
			'label'  => 'social.vastralaxmi.com',
			'type'   => 'url',
			'url'    => 'https://social.vastralaxmi.com/products/products.json',
			'file'   => '',
			'origin' => 'https://social.vastralaxmi.com',
		),
		array(
			'id'     => 'url-view',
			'label'  => 'view.vastralaxmi.com',
			'type'   => 'url',
			'url'    => 'https://view.vastralaxmi.com/products/products.json',
			'file'   => '',
			'origin' => 'https://view.vastralaxmi.com',
		),
	);
}

/**
 * Optional local file sources (backup only).
 *
 * @return array
 */
function vl_default_local_sources() {
	return array(
		array(
			'id'     => 'local-next',
			'label'  => 'Local: next-products.json',
			'type'   => 'file',
			'url'    => '',
			'file'   => 'next-products.json',
			'origin' => 'https://next.vastralaxmi.com',
		),
		array(
			'id'     => 'local-sale',
			'label'  => 'Local: sale-products.json',
			'type'   => 'file',
			'url'    => '',
			'file'   => 'sale-products.json',
			'origin' => 'https://sale.vastralaxmi.com',
		),
		array(
			'id'     => 'local-trending',
			'label'  => 'Local: trending-products.json',
			'type'   => 'file',
			'url'    => '',
			'file'   => 'trending-products.json',
			'origin' => 'https://trending.vastralaxmi.com',
		),
		array(
			'id'     => 'local-info',
			'label'  => 'Local: info-products.json',
			'type'   => 'file',
			'url'    => '',
			'file'   => 'info-products.json',
			'origin' => 'https://www.info.vastralaxmi.com',
		),
		array(
			'id'     => 'local-social',
			'label'  => 'Local: social-products.json',
			'type'   => 'file',
			'url'    => '',
			'file'   => 'social-products.json',
			'origin' => 'https://social.vastralaxmi.com',
		),
		array(
			'id'     => 'local-view',
			'label'  => 'Local: view-products.json',
			'type'   => 'file',
			'url'    => '',
			'file'   => 'view-products.json',
			'origin' => 'https://view.vastralaxmi.com',
		),
	);
}

/**
 * Sideload a remote image into media library.
 *
 * @param string $url     Image URL.
 * @param int    $post_id Parent post.
 * @param string $desc    Description.
 * @return int|\WP_Error Attachment ID.
 */
function vl_sideload_image( $url, $post_id, $desc = '' ) {
	if ( ! $url ) {
		return new WP_Error( 'no_url', 'Empty image URL' );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = download_url( $url, 60 );
	if ( is_wp_error( $tmp ) ) {
		return $tmp;
	}

	$path = wp_parse_url( $url, PHP_URL_PATH );
	$name = $path ? basename( $path ) : 'product-image.jpg';
	$name = sanitize_file_name( $name );
	if ( ! preg_match( '/\.(jpe?g|png|gif|webp)$/i', $name ) ) {
		$name .= '.jpg';
	}

	$file_array = array(
		'name'     => $name,
		'tmp_name' => $tmp,
	);

	$id = media_handle_sideload( $file_array, $post_id, $desc );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
	return $id;
}

/**
 * Canonical site key shared by URL + local variants of the same catalog.
 * e.g. url-next / local-next → next
 *
 * @param array $source Source config.
 * @return string
 */
function vl_source_site_key( $source ) {
	$id = isset( $source['id'] ) ? (string) $source['id'] : '';
	$id = preg_replace( '/^(url|local)-/', '', $id );
	if ( $id ) {
		return sanitize_key( $id );
	}
	if ( ! empty( $source['origin'] ) ) {
		$host = wp_parse_url( $source['origin'], PHP_URL_HOST );
		if ( $host ) {
			$host = preg_replace( '/^www\./', '', $host );
			$host = preg_replace( '/\.vastralaxmi\.com$/', '', $host );
			return sanitize_key( $host );
		}
	}
	$label = ! empty( $source['label'] ) ? $source['label'] : 'source';
	return sanitize_key( $label );
}

/**
 * Stable source key for duplicate prevention.
 *
 * @param string $source_name Source id/site.
 * @param int    $remote_id   Remote product id.
 * @param string $title       Title fallback.
 * @return string
 */
function vl_make_source_key( $source_name, $remote_id, $title = '' ) {
	$source_name = sanitize_key( $source_name );
	$remote_id   = absint( $remote_id );
	if ( $remote_id > 0 ) {
		return $source_name . ':' . $remote_id;
	}
	return $source_name . ':t:' . md5( strtolower( trim( (string) $title ) ) );
}

/**
 * Find existing imported product by source key (and legacy key formats).
 *
 * @param string $source_key  Unique source key.
 * @param string $source_name Source site key.
 * @param int    $remote_id   Remote id.
 * @param string $title       Title.
 * @return int
 */
function vl_find_imported_product( $source_key, $source_name = '', $remote_id = 0, $title = '' ) {
	$keys = array( $source_key );
	$site = sanitize_key( $source_name );
	$rid  = absint( $remote_id );

	if ( $site && $rid > 0 ) {
		// Legacy keys from earlier local/url-prefixed imports.
		$keys[] = $site . ':' . $rid . ':' . md5( $title );
		$keys[] = 'url-' . $site . ':' . $rid;
		$keys[] = 'local-' . $site . ':' . $rid;
		if ( $title ) {
			$keys[] = 'url-' . $site . ':' . $rid . ':' . md5( $title );
			$keys[] = 'local-' . $site . ':' . $rid . ':' . md5( $title );
		}
	}

	foreach ( array_unique( array_filter( $keys ) ) as $key ) {
		$q = new WP_Query(
			array(
				'post_type'      => 'fashion_product',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_vl_source_key',
				'meta_value'     => $key,
			)
		);
		if ( $q->posts ) {
			return (int) $q->posts[0];
		}
	}

	// Also match by site + remote product id meta (including legacy url-/local- prefixes).
	if ( $site && $rid > 0 ) {
		$site_variants = array_unique( array( $site, 'url-' . $site, 'local-' . $site ) );
		$q             = new WP_Query(
			array(
				'post_type'      => 'fashion_product',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'     => '_vl_source_site',
						'value'   => $site_variants,
						'compare' => 'IN',
					),
					array(
						'key'   => '_vl_source_product_id',
						'value' => (string) $rid,
					),
				),
			)
		);
		if ( $q->posts ) {
			return (int) $q->posts[0];
		}
	}

	return 0;
}

/**
 * Fetch JSON array from URL or local file.
 *
 * @param array $source Source config.
 * @return array|\WP_Error
 */
function vl_fetch_source_json( $source ) {
	$type = isset( $source['type'] ) ? $source['type'] : 'url';

	if ( 'file' === $type ) {
		$path = ABSPATH . 'docs/imports/' . sanitize_file_name( $source['file'] );
		if ( ! file_exists( $path ) ) {
			return new WP_Error( 'missing_file', 'Missing file: ' . $source['file'] );
		}
		$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	} else {
		$url = esc_url_raw( $source['url'] );
		if ( ! $url ) {
			return new WP_Error( 'bad_url', 'Empty JSON URL' );
		}
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 60,
				'headers' => array( 'Accept' => 'application/json' ),
			)
		);
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$code = wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			return new WP_Error( 'http_error', 'HTTP ' . $code . ' for URL' );
		}
		$raw = wp_remote_retrieve_body( $response );
	}

	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'bad_json', 'Invalid JSON (must be an array of products)' );
	}

	// Support { "products": [ ... ] } wrappers.
	if ( isset( $data['products'] ) && is_array( $data['products'] ) ) {
		$data = $data['products'];
	}

	return $data;
}

/**
 * Guess image origin from JSON URL.
 *
 * @param string $json_url JSON URL.
 * @return string
 */
function vl_guess_origin_from_url( $json_url ) {
	$parts = wp_parse_url( $json_url );
	if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
		return '';
	}
	$origin = $parts['scheme'] . '://' . $parts['host'];
	if ( ! empty( $parts['port'] ) ) {
		$origin .= ':' . $parts['port'];
	}
	return $origin;
}

/**
 * Import products from one or more sources.
 *
 * Default mode: sync new only — existing products are left unchanged (no duplicates).
 *
 * @param array $sources          Source configs.
 * @param bool  $with_images      Download images for new / missing products.
 * @param bool  $update_existing  If true, overwrite existing rows (default false).
 * @param int   $image_limit      Max images to download this run (0 = no limit).
 * @return array Stats.
 */
function vl_import_from_sources( $sources, $with_images = true, $update_existing = false, $image_limit = 0 ) {
	$stats = array(
		'created'         => 0,
		'updated'         => 0,
		'skipped'         => 0,
		'existing'        => 0,
		'images'          => 0,
		'images_pending'  => false,
		'errors'          => array(),
		'total'           => 0,
		'sources'         => 0,
	);

	$term_ids     = vl_ensure_import_categories();
	$map          = vl_import_category_map();
	$image_limit  = absint( $image_limit );
	$images_left  = $image_limit > 0 ? $image_limit : PHP_INT_MAX;

	foreach ( $sources as $source ) {
		$stats['sources']++;
		$label  = ! empty( $source['label'] ) ? $source['label'] : ( ! empty( $source['url'] ) ? $source['url'] : $source['id'] );
		$data   = vl_fetch_source_json( $source );
		$origin = ! empty( $source['origin'] ) ? untrailingslashit( $source['origin'] ) : '';

		if ( is_wp_error( $data ) ) {
			$stats['errors'][] = $label . ': ' . $data->get_error_message();
			continue;
		}

		if ( ! $origin && ! empty( $source['url'] ) ) {
			$origin = vl_guess_origin_from_url( $source['url'] );
		}

		$source_name = vl_source_site_key( $source );

		foreach ( $data as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$stats['total']++;

			$id    = isset( $item['id'] ) ? (int) $item['id'] : 0;
			$title = isset( $item['title'] ) ? sanitize_text_field( wp_strip_all_tags( $item['title'] ) ) : '';
			if ( function_exists( 'vl_clean_product_title' ) ) {
				$title = vl_clean_product_title( $title );
			}
			if ( ! $title ) {
				$stats['skipped']++;
				continue;
			}

			$source_key = vl_make_source_key( $source_name, $id, $title );
			$existing   = vl_find_imported_product( $source_key, $source_name, $id, $title );

			// Sync mode: keep existing product data as-is (no overwrite / no duplicates).
			if ( $existing && ! $update_existing ) {
				update_post_meta( $existing, '_vl_source_key', $source_key );
				update_post_meta( $existing, '_vl_source_site', $source_name );
				if ( $id > 0 ) {
					update_post_meta( $existing, '_vl_source_product_id', (string) $id );
				}
				$stats['existing']++;
				$stats['skipped']++;

				// Still fill missing featured images when that option is on.
				if ( $with_images && ! has_post_thumbnail( $existing ) ) {
					if ( $images_left <= 0 ) {
						$stats['images_pending'] = true;
						break 2;
					}
					$filled = vl_import_attach_product_image( $existing, $item, $origin, $title );
					if ( is_wp_error( $filled ) ) {
						$stats['errors'][] = $title . ': ' . $filled->get_error_message();
					} elseif ( $filled ) {
						$stats['images']++;
						$images_left--;
					}
				}
				continue;
			}

			$content  = isset( $item['description'] ) ? wp_kses_post( wpautop( $item['description'] ) ) : '';
			$price    = isset( $item['price'] ) ? floatval( $item['price'] ) : 0;
			$cat_raw  = isset( $item['category'] ) ? sanitize_text_field( $item['category'] ) : '';
			$cat_slug = isset( $map[ $cat_raw ] ) ? $map[ $cat_raw ] : 'fashion';

			$postarr = array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'fashion_product',
			);

			if ( $existing ) {
				$postarr['ID'] = $existing;
				$post_id       = wp_update_post( $postarr, true );
				if ( is_wp_error( $post_id ) ) {
					$stats['errors'][] = $post_id->get_error_message();
					$stats['skipped']++;
					continue;
				}
				$stats['updated']++;
			} else {
				$post_id = wp_insert_post( $postarr, true );
				if ( is_wp_error( $post_id ) ) {
					$stats['errors'][] = $post_id->get_error_message();
					$stats['skipped']++;
					continue;
				}
				$stats['created']++;
			}

			update_post_meta( $post_id, '_vl_price', $price );
			update_post_meta( $post_id, '_vl_sale_price', '' );
			update_post_meta( $post_id, '_vl_source_key', $source_key );
			update_post_meta( $post_id, '_vl_source_site', $source_name );
			update_post_meta( $post_id, '_vl_source_category', $cat_raw );
			if ( $id > 0 ) {
				update_post_meta( $post_id, '_vl_source_product_id', (string) $id );
			}

			update_post_meta( $post_id, '_vl_is_featured', in_array( $cat_slug, array( 'luxury-collection', 'new-arrivals' ), true ) ? '1' : '0' );
			update_post_meta( $post_id, '_vl_is_new', 'new-arrivals' === $cat_slug ? '1' : '0' );
			update_post_meta( $post_id, '_vl_is_trending', 'trending-fashion' === $cat_slug ? '1' : '0' );
			update_post_meta( $post_id, '_vl_is_luxury', 'luxury-collection' === $cat_slug ? '1' : '0' );
			update_post_meta( $post_id, '_vl_is_on_sale', 'sale-and-offers' === $cat_slug ? '1' : '0' );

			if ( isset( $term_ids[ $cat_slug ] ) ) {
				wp_set_object_terms( $post_id, array( $term_ids[ $cat_slug ] ), 'product_category' );
			}

			if ( $with_images && ! has_post_thumbnail( $post_id ) ) {
				if ( $images_left <= 0 ) {
					$stats['images_pending'] = true;
					break 2;
				}
				$filled = vl_import_attach_product_image( $post_id, $item, $origin, $title );
				if ( is_wp_error( $filled ) ) {
					$stats['errors'][] = $title . ': ' . $filled->get_error_message();
				} elseif ( $filled ) {
					$stats['images']++;
					$images_left--;
				}
			}
		}
	}

	update_option( 'vl_catalog_imported', time() );
	flush_rewrite_rules( false );
	return $stats;
}

/**
 * Resolve image URL from a product JSON row + origin.
 *
 * @param array  $item   Product row.
 * @param string $origin Image base URL.
 * @return string
 */
function vl_import_item_image_url( $item, $origin = '' ) {
	$img_rel = '';
	if ( ! empty( $item['image'] ) ) {
		$img_rel = $item['image'];
	} elseif ( ! empty( $item['img'] ) ) {
		$img_rel = $item['img'];
	} else {
		for ( $i = 1; $i <= 10; $i++ ) {
			$key = 'img' . $i;
			if ( ! empty( $item[ $key ] ) ) {
				$img_rel = $item[ $key ];
				break;
			}
		}
	}
	if ( ! $img_rel ) {
		return '';
	}
	$img_rel = (string) $img_rel;
	if ( 0 === strpos( $img_rel, 'http://' ) || 0 === strpos( $img_rel, 'https://' ) ) {
		return esc_url_raw( $img_rel );
	}
	if ( ! $origin ) {
		return '';
	}
	return esc_url_raw( rtrim( $origin, '/' ) . '/' . ltrim( $img_rel, '/' ) );
}

/**
 * Download and set featured image for a product when missing.
 *
 * @param int    $post_id Product ID.
 * @param array  $item    JSON row.
 * @param string $origin  Image base URL.
 * @param string $title   Product title.
 * @return bool|\WP_Error True if attached, false if no image URL, WP_Error on failure.
 */
function vl_import_attach_product_image( $post_id, $item, $origin, $title ) {
	$post_id = absint( $post_id );
	if ( ! $post_id || has_post_thumbnail( $post_id ) ) {
		return false;
	}
	$img_url = vl_import_item_image_url( $item, $origin );
	if ( ! $img_url ) {
		return false;
	}
	$att_id = vl_sideload_image( $img_url, $post_id, $title );
	if ( is_wp_error( $att_id ) ) {
		return $att_id;
	}
	set_post_thumbnail( $post_id, $att_id );
	update_post_meta( $post_id, '_vl_gallery_ids', (string) $att_id );
	return true;
}

/**
 * Backward-compatible catalog import (live URLs first).
 *
 * @param bool $with_images Download images.
 * @return array
 */
function vl_import_scraped_catalog( $with_images = true ) {
	return vl_import_from_sources( vl_default_remote_sources(), $with_images, false );
}

/**
 * Admin page + action for import.
 */
function vl_register_import_page() {
	add_submenu_page(
		'vl-settings',
		__( 'Import Catalog', 'vl-core' ),
		__( 'Import Catalog', 'vl-core' ),
		'manage_options',
		'vl-import-catalog',
		'vl_render_import_page'
	);
}
add_action( 'admin_menu', 'vl_register_import_page' );

/**
 * Render import page.
 */
function vl_render_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$saved   = vl_get_json_sources();
	$remote  = vl_default_remote_sources();
	$local   = vl_default_local_sources();
	$stats   = get_option( 'vl_last_import_stats', array() );
	$status  = isset( $_GET['vl_msg'] ) ? sanitize_key( wp_unslash( $_GET['vl_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$step    = isset( $_GET['vl_step'] ) ? absint( $_GET['vl_step'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$of      = isset( $_GET['vl_of'] ) ? absint( $_GET['vl_of'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$queue   = get_transient( 'vl_import_queue_' . get_current_user_id() );
	?>
	<div class="wrap vl-import-wrap">
		<h1><?php esc_html_e( 'Import Catalog', 'vl-core' ); ?></h1>
		<?php
		if ( function_exists( 'vl_settings_tabs' ) ) {
			vl_settings_tabs( 'vl-import-catalog' );
		}
		?>
		<p><?php esc_html_e( 'Sync one catalog URL at a time (lighter load). Existing products are skipped — only new items are added.', 'vl-core' ); ?></p>

		<?php if ( 'saved' === $status ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'JSON source saved. Select it and click Sync.', 'vl-core' ); ?></p></div>
		<?php elseif ( 'deleted' === $status ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'JSON source removed.', 'vl-core' ); ?></p></div>
		<?php elseif ( 'progress' === $status ) : ?>
			<div class="notice notice-info">
				<p>
					<?php
					printf(
						/* translators: 1: current step, 2: total, 3: source label, 4: images this batch, 5: images total */
						esc_html__( 'Working… source %1$s of %2$s (%3$s) — Images this batch: %4$s | Images total so far: %5$s. Keep this tab open.', 'vl-core' ),
						esc_html( (string) max( 1, $step ) ),
						esc_html( (string) max( 1, $of ) ),
						esc_html( isset( $stats['last_source'] ) ? $stats['last_source'] : '' ),
						esc_html( isset( $stats['batch_images'] ) ? $stats['batch_images'] : 0 ),
						esc_html( isset( $stats['images'] ) ? $stats['images'] : 0 )
					);
					?>
				</p>
			</div>
			<?php if ( is_array( $queue ) && ! empty( $queue['remaining'] ) ) : ?>
				<meta http-equiv="refresh" content="1;url=<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vl_import_catalog_next' ), 'vl_import_catalog_next' ) ); ?>">
				<p><a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vl_import_catalog_next' ), 'vl_import_catalog_next' ) ); ?>"><?php esc_html_e( 'Continue next URL now', 'vl-core' ); ?></a></p>
			<?php endif; ?>
		<?php elseif ( 'imported' === $status ) : ?>
			<div class="notice notice-success">
				<p>
					<?php
					printf(
						/* translators: 1: created, 2: already present, 3: images, 4: total, 5: sources */
						esc_html__( 'All selected URLs synced — New: %1$s | Already present: %2$s | Images: %3$s | Rows: %4$s | Sources: %5$s', 'vl-core' ),
						esc_html( isset( $stats['created'] ) ? $stats['created'] : 0 ),
						esc_html( isset( $stats['existing'] ) ? $stats['existing'] : ( isset( $stats['skipped'] ) ? $stats['skipped'] : 0 ) ),
						esc_html( isset( $stats['images'] ) ? $stats['images'] : 0 ),
						esc_html( isset( $stats['total'] ) ? $stats['total'] : 0 ),
						esc_html( isset( $stats['sources'] ) ? $stats['sources'] : 0 )
					);
					?>
				</p>
				<?php if ( ! empty( $stats['errors'] ) ) : ?>
					<details><summary><?php esc_html_e( 'Show errors / notes', 'vl-core' ); ?></summary>
						<ul>
							<?php foreach ( array_slice( $stats['errors'], 0, 20 ) as $err ) : ?>
								<li><?php echo esc_html( $err ); ?></li>
							<?php endforeach; ?>
						</ul>
					</details>
				<?php endif; ?>
			</div>
		<?php elseif ( 'none' === $status ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Select at least one URL source to sync.', 'vl-core' ); ?></p></div>
		<?php elseif ( 'bad' === $status ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Please enter a valid JSON URL and a name.', 'vl-core' ); ?></p></div>
		<?php endif; ?>

		<div class="vl-import-grid">
			<div class="vl-import-panel">
				<h2><?php esc_html_e( '1. Sync from live URLs', 'vl-core' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Select URLs, then Sync. Each URL is imported one-by-one so the site stays light. Already-imported products stay unchanged.', 'vl-core' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="vl_import_catalog">
					<?php wp_nonce_field( 'vl_import_catalog', 'vl_import_nonce' ); ?>

					<h3><?php esc_html_e( 'Built-in catalog URLs', 'vl-core' ); ?></h3>
					<ul class="vl-source-list">
						<?php foreach ( $remote as $i => $src ) : ?>
							<li>
								<label>
									<input type="checkbox" name="vl_sources[]" value="<?php echo esc_attr( $src['id'] ); ?>" <?php checked( 0 === $i ); ?>>
									<strong><?php echo esc_html( $src['label'] ); ?></strong>
									<code><?php echo esc_html( $src['url'] ); ?></code>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>

					<?php if ( $saved ) : ?>
						<h3><?php esc_html_e( 'Your custom URLs', 'vl-core' ); ?></h3>
						<ul class="vl-source-list">
							<?php foreach ( $saved as $src ) : ?>
								<li>
									<label>
										<input type="checkbox" name="vl_sources[]" value="<?php echo esc_attr( $src['id'] ); ?>">
										<strong><?php echo esc_html( $src['label'] ); ?></strong>
										<code><?php echo esc_html( $src['url'] ); ?></code>
									</label>
									<a class="button-link-delete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vl_delete_json_source&id=' . rawurlencode( $src['id'] ) ), 'vl_delete_json_source_' . $src['id'] ) ); ?>"><?php esc_html_e( 'Remove', 'vl-core' ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<details class="vl-local-sources">
						<summary><?php esc_html_e( 'Optional: local backup files (docs/imports)', 'vl-core' ); ?></summary>
						<ul class="vl-source-list">
							<?php foreach ( $local as $src ) : ?>
								<li>
									<label>
										<input type="checkbox" name="vl_sources[]" value="<?php echo esc_attr( $src['id'] ); ?>">
										<strong><?php echo esc_html( $src['label'] ); ?></strong>
									</label>
								</li>
							<?php endforeach; ?>
						</ul>
					</details>

					<p>
						<label><input type="checkbox" name="vl_with_images" value="1" id="vl_with_images"> <?php esc_html_e( 'Download missing product images', 'vl-core' ); ?></label>
					</p>
					<p class="description"><?php esc_html_e( 'Images download in small batches (about 20 at a time) so the page does not hang. Keep the tab open until it finishes.', 'vl-core' ); ?></p>
					<p class="submit" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
						<button type="submit" name="vl_sync_mode" value="data" class="button button-primary"><?php esc_html_e( 'Sync selected (data only)', 'vl-core' ); ?></button>
						<button type="submit" name="vl_sync_mode" value="images" class="button button-secondary" style="background:#1a2230;border-color:#1a2230;color:#fff;"><?php esc_html_e( 'Download missing images now', 'vl-core' ); ?></button>
					</p>
				</form>
			</div>

			<div class="vl-import-panel">
				<h2><?php esc_html_e( '2. Add another JSON URL', 'vl-core' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Save any extra live URL, then select it on the left and sync.', 'vl-core' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="vl_save_json_source">
					<?php wp_nonce_field( 'vl_save_json_source', 'vl_save_json_nonce' ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="vl_json_label"><?php esc_html_e( 'Source name *', 'vl-core' ); ?></label></th>
							<td><input class="regular-text" type="text" id="vl_json_label" name="vl_json_label" required placeholder="Women Fashion JSON"></td>
						</tr>
						<tr>
							<th scope="row"><label for="vl_json_url"><?php esc_html_e( 'JSON URL *', 'vl-core' ); ?></label></th>
							<td><input class="large-text" type="url" id="vl_json_url" name="vl_json_url" required placeholder="https://example.com/products/products.json"></td>
						</tr>
						<tr>
							<th scope="row"><label for="vl_json_origin"><?php esc_html_e( 'Image base URL (optional)', 'vl-core' ); ?></label></th>
							<td>
								<input class="large-text" type="url" id="vl_json_origin" name="vl_json_origin" placeholder="https://example.com">
								<p class="description"><?php esc_html_e( 'Only if image paths are relative. Leave blank to auto-detect.', 'vl-core' ); ?></p>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Save JSON URL', 'vl-core' ), 'secondary' ); ?>
				</form>

				<div class="vl-json-help">
					<strong><?php esc_html_e( 'Expected JSON format (array):', 'vl-core' ); ?></strong>
					<pre>[
  {
    "id": 1,
    "title": "Product name",
    "price": 999,
    "category": "fashion",
    "description": "Optional text",
    "img1": "/path/or/full-image-url.jpg"
  }
]</pre>
				</div>
			</div>
		</div>
	</div>
	<style>
		.vl-import-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:18px;margin-top:16px;max-width:1100px}
		.vl-import-panel{background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:16px 18px}
		.vl-import-panel h2{margin-top:0}
		.vl-source-list{list-style:none;margin:0 0 12px;padding:0}
		.vl-source-list li{display:flex;justify-content:space-between;gap:10px;align-items:flex-start;padding:10px 0;border-bottom:1px solid #eee}
		.vl-source-list code{display:block;color:#646970;font-size:12px;margin-top:3px;word-break:break-all}
		.vl-json-help{margin-top:14px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:8px;padding:10px 12px}
		.vl-json-help pre{white-space:pre-wrap;margin:8px 0 0;font-size:12px}
		.vl-local-sources{margin:12px 0}
		@media (max-width:900px){.vl-import-grid{grid-template-columns:1fr}}
	</style>
	<?php
}

/**
 * Save a new JSON URL source.
 */
function vl_handle_save_json_source() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized', 'vl-core' ) );
	}
	check_admin_referer( 'vl_save_json_source', 'vl_save_json_nonce' );

	$label  = isset( $_POST['vl_json_label'] ) ? sanitize_text_field( wp_unslash( $_POST['vl_json_label'] ) ) : '';
	$url    = isset( $_POST['vl_json_url'] ) ? esc_url_raw( wp_unslash( $_POST['vl_json_url'] ) ) : '';
	$origin = isset( $_POST['vl_json_origin'] ) ? esc_url_raw( wp_unslash( $_POST['vl_json_origin'] ) ) : '';

	if ( '' === $label || '' === $url ) {
		wp_safe_redirect( admin_url( 'admin.php?page=vl-import-catalog&vl_msg=bad' ) );
		exit;
	}

	if ( ! $origin ) {
		$origin = vl_guess_origin_from_url( $url );
	}

	$sources   = vl_get_json_sources();
	$id        = 'url-' . substr( md5( $url . microtime( true ) ), 0, 10 );
	$sources[] = array(
		'id'     => $id,
		'label'  => $label,
		'type'   => 'url',
		'url'    => $url,
		'file'   => '',
		'origin' => $origin,
	);
	update_option( 'vl_json_sources', array_values( $sources ), false );

	wp_safe_redirect( admin_url( 'admin.php?page=vl-import-catalog&vl_msg=saved' ) );
	exit;
}
add_action( 'admin_post_vl_save_json_source', 'vl_handle_save_json_source' );

/**
 * Delete JSON source.
 */
function vl_handle_delete_json_source() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized', 'vl-core' ) );
	}
	$id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
	check_admin_referer( 'vl_delete_json_source_' . $id );

	$sources = vl_get_json_sources();
	$sources = array_values(
		array_filter(
			$sources,
			static function ( $src ) use ( $id ) {
				return empty( $src['id'] ) || $src['id'] !== $id;
			}
		)
	);
	update_option( 'vl_json_sources', $sources, false );

	wp_safe_redirect( admin_url( 'admin.php?page=vl-import-catalog&vl_msg=deleted' ) );
	exit;
}
add_action( 'admin_post_vl_delete_json_source', 'vl_handle_delete_json_source' );

/**
 * Merge selected source ids into source configs.
 *
 * @param array $selected Source ids.
 * @return array
 */
function vl_resolve_import_sources( $selected ) {
	$all   = array_merge( vl_default_remote_sources(), vl_get_json_sources(), vl_default_local_sources() );
	$by_id = array();
	foreach ( $all as $src ) {
		$by_id[ $src['id'] ] = $src;
	}
	$out = array();
	foreach ( (array) $selected as $id ) {
		$id = sanitize_text_field( $id );
		if ( isset( $by_id[ $id ] ) ) {
			$out[] = $by_id[ $id ];
		}
	}
	return $out;
}

/**
 * Add step stats into a running totals array.
 *
 * @param array $totals Running totals.
 * @param array $step   Step stats.
 * @param array $source Source config.
 * @return array
 */
function vl_merge_import_stats( $totals, $step, $source = array() ) {
	if ( ! is_array( $totals ) ) {
		$totals = array(
			'created'  => 0,
			'updated'  => 0,
			'skipped'  => 0,
			'existing' => 0,
			'images'   => 0,
			'total'    => 0,
			'sources'  => 0,
			'errors'   => array(),
		);
	}
	foreach ( array( 'created', 'updated', 'skipped', 'existing', 'images', 'total' ) as $key ) {
		$totals[ $key ] = (int) ( isset( $totals[ $key ] ) ? $totals[ $key ] : 0 ) + (int) ( isset( $step[ $key ] ) ? $step[ $key ] : 0 );
	}
	$totals['sources'] = (int) ( isset( $totals['sources'] ) ? $totals['sources'] : 0 ) + 1;
	if ( ! empty( $step['errors'] ) && is_array( $step['errors'] ) ) {
		$totals['errors'] = array_merge( isset( $totals['errors'] ) ? (array) $totals['errors'] : array(), $step['errors'] );
	}
	$totals['last_source'] = ! empty( $source['label'] ) ? $source['label'] : ( ! empty( $source['id'] ) ? $source['id'] : '' );
	return $totals;
}

/**
 * Start import queue — processes one URL, then continues via next step.
 */
function vl_handle_import_catalog() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized', 'vl-core' ) );
	}
	check_admin_referer( 'vl_import_catalog', 'vl_import_nonce' );

	$selected = isset( $_POST['vl_sources'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['vl_sources'] ) ) : array();
	$to_import = vl_resolve_import_sources( $selected );
	if ( empty( $to_import ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=vl-import-catalog&vl_msg=none' ) );
		exit;
	}

	$mode        = isset( $_POST['vl_sync_mode'] ) ? sanitize_key( wp_unslash( $_POST['vl_sync_mode'] ) ) : 'data';
	$with_images = ( 'images' === $mode ) || isset( $_POST['vl_with_images'] );
	$queue       = array(
		'remaining'   => $to_import,
		'with_images' => $with_images,
		'image_limit' => $with_images ? 20 : 0,
		'totals'      => array(
			'created'  => 0,
			'updated'  => 0,
			'skipped'  => 0,
			'existing' => 0,
			'images'   => 0,
			'total'    => 0,
			'sources'  => 0,
			'errors'   => array(),
		),
		'total'       => count( $to_import ),
		'done'        => 0,
	);
	set_transient( 'vl_import_queue_' . get_current_user_id(), $queue, HOUR_IN_SECONDS );

	vl_process_import_queue_step();
}
add_action( 'admin_post_vl_import_catalog', 'vl_handle_import_catalog' );

/**
 * Continue queued one-URL import step.
 */
function vl_handle_import_catalog_next() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized', 'vl-core' ) );
	}
	check_admin_referer( 'vl_import_catalog_next' );
	vl_process_import_queue_step();
}
add_action( 'admin_post_vl_import_catalog_next', 'vl_handle_import_catalog_next' );

/**
 * Import exactly one queued source (images in small batches), then redirect.
 */
function vl_process_import_queue_step() {
	@set_time_limit( 180 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	$key   = 'vl_import_queue_' . get_current_user_id();
	$queue = get_transient( $key );
	if ( ! is_array( $queue ) || empty( $queue['remaining'] ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=vl-import-catalog&vl_msg=none' ) );
		exit;
	}

	$source      = array_shift( $queue['remaining'] );
	$with_images = ! empty( $queue['with_images'] );
	$image_limit = $with_images ? max( 5, absint( isset( $queue['image_limit'] ) ? $queue['image_limit'] : 20 ) ) : 0;
	$stats       = vl_import_from_sources( array( $source ), $with_images, false, $image_limit );
	$queue['totals'] = vl_merge_import_stats( $queue['totals'], $stats, $source );

	// More missing images for this same URL — keep working on it.
	if ( ! empty( $stats['images_pending'] ) ) {
		array_unshift( $queue['remaining'], $source );
	} else {
		$queue['done'] = (int) $queue['done'] + 1;
	}

	$notice_stats = $queue['totals'];
	$notice_stats['batch_images'] = isset( $stats['images'] ) ? (int) $stats['images'] : 0;
	$notice_stats['last_source']  = $queue['totals']['last_source'];
	update_option( 'vl_last_import_stats', empty( $queue['remaining'] ) ? $queue['totals'] : $notice_stats );

	if ( ! empty( $queue['remaining'] ) ) {
		set_transient( $key, $queue, HOUR_IN_SECONDS );
		$step = max( 1, (int) $queue['done'] );
		$of   = max( 1, (int) $queue['total'] );
		wp_safe_redirect(
			admin_url(
				'admin.php?page=vl-import-catalog&vl_msg=progress&vl_step=' . $step . '&vl_of=' . $of
			)
		);
		exit;
	}

	delete_transient( $key );
	if ( function_exists( 'vl_sync_site_menus' ) ) {
		delete_option( 'vl_menus_synced_v8' );
		vl_sync_site_menus();
	}
	wp_safe_redirect( admin_url( 'admin.php?page=vl-import-catalog&vl_msg=imported' ) );
	exit;
}
