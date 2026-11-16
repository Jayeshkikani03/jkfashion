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
 * Default local file sources.
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
 * Find existing imported product by source key.
 *
 * @param string $source_key Unique source key.
 * @return int
 */
function vl_find_imported_product( $source_key ) {
	$q = new WP_Query(
		array(
			'post_type'      => 'fashion_product',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_vl_source_key',
			'meta_value'     => $source_key,
		)
	);
	return $q->posts ? (int) $q->posts[0] : 0;
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
 * @param array $sources     Source configs.
 * @param bool  $with_images Download images.
 * @return array Stats.
 */
function vl_import_from_sources( $sources, $with_images = true ) {
	$stats = array(
		'created' => 0,
		'updated' => 0,
		'skipped' => 0,
		'images'  => 0,
		'errors'  => array(),
		'total'   => 0,
		'sources' => 0,
	);

	$term_ids = vl_ensure_import_categories();
	$map      = vl_import_category_map();

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

		$source_name = sanitize_key( ! empty( $source['id'] ) ? $source['id'] : $label );

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

			$source_key = $source_name . ':' . $id . ':' . md5( $title );
			$existing   = vl_find_imported_product( $source_key );

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

			update_post_meta( $post_id, '_vl_is_featured', in_array( $cat_slug, array( 'luxury-collection', 'new-arrivals' ), true ) ? '1' : '0' );
			update_post_meta( $post_id, '_vl_is_new', 'new-arrivals' === $cat_slug ? '1' : '0' );
			update_post_meta( $post_id, '_vl_is_trending', 'trending-fashion' === $cat_slug ? '1' : '0' );
			update_post_meta( $post_id, '_vl_is_luxury', 'luxury-collection' === $cat_slug ? '1' : '0' );
			update_post_meta( $post_id, '_vl_is_on_sale', 'sale-and-offers' === $cat_slug ? '1' : '0' );

			if ( isset( $term_ids[ $cat_slug ] ) ) {
				wp_set_object_terms( $post_id, array( $term_ids[ $cat_slug ] ), 'product_category' );
			}

			if ( $with_images && ! has_post_thumbnail( $post_id ) ) {
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
				if ( $img_rel ) {
					$img_url = ( 0 === strpos( $img_rel, 'http' ) ) ? $img_rel : rtrim( $origin, '/' ) . '/' . ltrim( $img_rel, '/' );
					$att_id  = vl_sideload_image( $img_url, $post_id, $title );
					if ( ! is_wp_error( $att_id ) ) {
						set_post_thumbnail( $post_id, $att_id );
						$stats['images']++;
						update_post_meta( $post_id, '_vl_gallery_ids', (string) $att_id );
					} else {
						$stats['errors'][] = $title . ': ' . $att_id->get_error_message();
					}
				}
			}
		}
	}

	update_option( 'vl_catalog_imported', time() );
	flush_rewrite_rules( false );
	return $stats;
}

/**
 * Backward-compatible local import.
 *
 * @param bool $with_images Download images.
 * @return array
 */
function vl_import_scraped_catalog( $with_images = true ) {
	return vl_import_from_sources( vl_default_local_sources(), $with_images );
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

	$saved  = vl_get_json_sources();
	$local  = vl_default_local_sources();
	$stats  = get_option( 'vl_last_import_stats', array() );
	$status = isset( $_GET['vl_msg'] ) ? sanitize_key( wp_unslash( $_GET['vl_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="wrap vl-import-wrap">
		<h1><?php esc_html_e( 'Import Catalog (JSON)', 'vl-core' ); ?></h1>
		<?php
		if ( function_exists( 'vl_settings_tabs' ) ) {
			vl_settings_tabs( 'vl-import-catalog' );
		}
		?>
		<p><?php esc_html_e( 'Add a products JSON URL, then import. Each product needs title, price, category, and image fields.', 'vl-core' ); ?></p>

		<?php if ( 'saved' === $status ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'JSON source saved. Select it below and click Import.', 'vl-core' ); ?></p></div>
		<?php elseif ( 'deleted' === $status ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'JSON source removed.', 'vl-core' ); ?></p></div>
		<?php elseif ( 'imported' === $status ) : ?>
			<div class="notice notice-success">
				<p>
					<?php
					printf(
						/* translators: 1: created, 2: updated, 3: images, 4: total */
						esc_html__( 'Import finished — Created: %1$s | Updated: %2$s | Images: %3$s | Total rows: %4$s', 'vl-core' ),
						esc_html( isset( $stats['created'] ) ? $stats['created'] : 0 ),
						esc_html( isset( $stats['updated'] ) ? $stats['updated'] : 0 ),
						esc_html( isset( $stats['images'] ) ? $stats['images'] : 0 ),
						esc_html( isset( $stats['total'] ) ? $stats['total'] : 0 )
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
			<div class="notice notice-error"><p><?php esc_html_e( 'Select at least one JSON source to import.', 'vl-core' ); ?></p></div>
		<?php elseif ( 'bad' === $status ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Please enter a valid JSON URL and a name.', 'vl-core' ); ?></p></div>
		<?php endif; ?>

		<div class="vl-import-grid">
			<div class="vl-import-panel">
				<h2><?php esc_html_e( '1. Add new JSON URL', 'vl-core' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Example: https://yoursite.com/products/products.json', 'vl-core' ); ?></p>
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
								<p class="description"><?php esc_html_e( 'Only needed if image paths in JSON are relative (start with /uploads/...). Leave blank to auto-detect from JSON URL.', 'vl-core' ); ?></p>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Save JSON source', 'vl-core' ), 'secondary' ); ?>
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

			<div class="vl-import-panel">
				<h2><?php esc_html_e( '2. Import selected sources', 'vl-core' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="vl_import_catalog">
					<?php wp_nonce_field( 'vl_import_catalog', 'vl_import_nonce' ); ?>

					<?php if ( $saved ) : ?>
						<h3><?php esc_html_e( 'Your JSON URLs', 'vl-core' ); ?></h3>
						<ul class="vl-source-list">
							<?php foreach ( $saved as $src ) : ?>
								<li>
									<label>
										<input type="checkbox" name="vl_sources[]" value="<?php echo esc_attr( $src['id'] ); ?>" checked>
										<strong><?php echo esc_html( $src['label'] ); ?></strong>
										<code><?php echo esc_html( $src['url'] ); ?></code>
									</label>
									<a class="button-link-delete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vl_delete_json_source&id=' . rawurlencode( $src['id'] ) ), 'vl_delete_json_source_' . $src['id'] ) ); ?>"><?php esc_html_e( 'Remove', 'vl-core' ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="description"><?php esc_html_e( 'No custom JSON URLs yet. Add one on the left.', 'vl-core' ); ?></p>
					<?php endif; ?>

					<details class="vl-local-sources">
						<summary><?php esc_html_e( 'Also import local files (docs/imports)', 'vl-core' ); ?></summary>
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
						<label><input type="checkbox" name="vl_with_images" value="1" checked> <?php esc_html_e( 'Download product images', 'vl-core' ); ?></label>
					</p>
					<?php submit_button( __( 'Import selected JSON now', 'vl-core' ), 'primary' ); ?>
				</form>
			</div>
		</div>
	</div>
	<style>
		.vl-import-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:18px;margin-top:16px;max-width:1100px}
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
 * Handle import submit.
 */
function vl_handle_import_catalog() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized', 'vl-core' ) );
	}
	check_admin_referer( 'vl_import_catalog', 'vl_import_nonce' );

	@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	$selected = isset( $_POST['vl_sources'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['vl_sources'] ) ) : array();
	if ( empty( $selected ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=vl-import-catalog&vl_msg=none' ) );
		exit;
	}

	$all = array_merge( vl_get_json_sources(), vl_default_local_sources() );
	$by_id = array();
	foreach ( $all as $src ) {
		$by_id[ $src['id'] ] = $src;
	}

	$to_import = array();
	foreach ( $selected as $id ) {
		if ( isset( $by_id[ $id ] ) ) {
			$to_import[] = $by_id[ $id ];
		}
	}

	if ( empty( $to_import ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=vl-import-catalog&vl_msg=none' ) );
		exit;
	}

	$with_images = isset( $_POST['vl_with_images'] );
	$stats       = vl_import_from_sources( $to_import, $with_images );
	update_option( 'vl_last_import_stats', $stats );

	if ( function_exists( 'vl_sync_site_menus' ) ) {
		delete_option( 'vl_menus_synced_v4' );
		vl_sync_site_menus();
	}

	wp_safe_redirect( admin_url( 'admin.php?page=vl-import-catalog&vl_msg=imported' ) );
	exit;
}
add_action( 'admin_post_vl_import_catalog', 'vl_handle_import_catalog' );
