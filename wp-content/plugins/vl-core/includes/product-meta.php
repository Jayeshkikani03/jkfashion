<?php
/**
 * Product meta boxes and saving.
 *
 * @package VL_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register product meta boxes.
 */
function vl_add_product_meta_boxes() {
	add_meta_box(
		'vl_product_details',
		__( 'Product Photo, Price & Details', 'vl-core' ),
		'vl_render_product_details_metabox',
		'fashion_product',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'vl_add_product_meta_boxes' );

/**
 * Render product details metabox.
 *
 * @param WP_Post $post Post object.
 */
function vl_render_product_details_metabox( $post ) {
	wp_nonce_field( 'vl_save_product_meta', 'vl_product_meta_nonce' );

	$price      = vl_get_product_meta( $post->ID, '_vl_price' );
	$sale_price = vl_get_product_meta( $post->ID, '_vl_sale_price' );
	$gallery    = vl_get_product_meta( $post->ID, '_vl_gallery_ids' );
	$is_featured  = vl_get_product_meta( $post->ID, '_vl_is_featured', '0' );
	$is_new       = vl_get_product_meta( $post->ID, '_vl_is_new', '0' );
	$is_trending  = vl_get_product_meta( $post->ID, '_vl_is_trending', '0' );
	$is_luxury    = vl_get_product_meta( $post->ID, '_vl_is_luxury', '0' );
	$is_on_sale   = vl_get_product_meta( $post->ID, '_vl_is_on_sale', '0' );

	$gallery_ids = array_filter( array_map( 'absint', explode( ',', (string) $gallery ) ) );
	$thumb_id    = (int) get_post_thumbnail_id( $post->ID );
	$selected    = wp_get_post_terms( $post->ID, 'product_category', array( 'fields' => 'ids' ) );
	if ( is_wp_error( $selected ) ) {
		$selected = array();
	}
	$selected = array_map( 'intval', $selected );
	$terms    = get_terms(
		array(
			'taxonomy'   => 'product_category',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);
	?>
	<div class="vl-admin-box">
		<div class="vl-admin-section vl-admin-section--photo">
			<h3><?php esc_html_e( '1. Product Photo', 'vl-core' ); ?></h3>
			<p class="description"><?php esc_html_e( 'This photo shows on the website product card.', 'vl-core' ); ?></p>
			<input type="hidden" id="vl_featured_id" name="vl_featured_id" value="<?php echo esc_attr( $thumb_id ? $thumb_id : '-1' ); ?>">
			<div id="vl-featured-preview" class="vl-featured-preview">
				<?php if ( $thumb_id ) : ?>
					<?php echo wp_get_attachment_image( $thumb_id, 'medium' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php else : ?>
					<span class="vl-featured-empty"><?php esc_html_e( 'No image selected', 'vl-core' ); ?></span>
				<?php endif; ?>
			</div>
			<p class="vl-featured-actions">
				<button type="button" class="button button-primary button-large" id="vl-featured-upload"><?php esc_html_e( 'Upload product photo', 'vl-core' ); ?></button>
				<button type="button" class="button" id="vl-featured-remove" <?php echo $thumb_id ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Remove photo', 'vl-core' ); ?></button>
			</p>
		</div>

		<div class="vl-admin-section">
			<h3><?php esc_html_e( '2. Price', 'vl-core' ); ?></h3>
			<div class="vl-admin-grid">
				<p>
					<label for="vl_price"><strong><?php esc_html_e( 'Price (₹)', 'vl-core' ); ?></strong></label>
					<input type="number" step="0.01" min="0" class="widefat vl-price-input" id="vl_price" name="vl_price" value="<?php echo esc_attr( $price ); ?>" placeholder="999">
				</p>
				<p>
					<label for="vl_sale_price"><strong><?php esc_html_e( 'Sale Price (₹) optional', 'vl-core' ); ?></strong></label>
					<input type="number" step="0.01" min="0" class="widefat vl-price-input" id="vl_sale_price" name="vl_sale_price" value="<?php echo esc_attr( $sale_price ); ?>" placeholder="799">
				</p>
			</div>
		</div>

		<div class="vl-admin-section vl-admin-section--cats">
			<h3><?php esc_html_e( '3. Product Category *', 'vl-core' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Choose where this product appears (Shop menu, collection pages, filters). Tick at least one.', 'vl-core' ); ?></p>
			<?php if ( is_wp_error( $terms ) || empty( $terms ) ) : ?>
				<p class="description">
					<?php esc_html_e( 'No categories yet.', 'vl-core' ); ?>
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=product_category&post_type=fashion_product' ) ); ?>"><?php esc_html_e( 'Add categories', 'vl-core' ); ?></a>
				</p>
			<?php else : ?>
				<div class="vl-cat-grid">
					<?php foreach ( $terms as $term ) : ?>
						<label class="vl-check vl-check--cat">
							<input type="checkbox" name="vl_product_cats[]" value="<?php echo esc_attr( (string) $term->term_id ); ?>" <?php checked( in_array( (int) $term->term_id, $selected, true ) ); ?>>
							<?php echo esc_html( $term->name ); ?>
						</label>
					<?php endforeach; ?>
				</div>
				<p class="description" style="margin-top:8px;">
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=product_category&post_type=fashion_product' ) ); ?>"><?php esc_html_e( 'Manage / add categories', 'vl-core' ); ?></a>
				</p>
			<?php endif; ?>
		</div>

		<div class="vl-admin-section">
			<h3><?php esc_html_e( '4. Buying', 'vl-core' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Customers buy with Cash on Delivery (COD) on the product page. Orders appear under Orders (COD) in admin.', 'vl-core' ); ?></p>
		</div>

		<div class="vl-admin-section">
			<h3><?php esc_html_e( '5. Extra gallery images (optional)', 'vl-core' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Only if you want more photos on the product page.', 'vl-core' ); ?></p>
			<input type="hidden" id="vl_gallery_ids" name="vl_gallery_ids" value="<?php echo esc_attr( implode( ',', $gallery_ids ) ); ?>">
			<div id="vl-gallery-preview" class="vl-gallery-preview">
				<?php foreach ( $gallery_ids as $gid ) : ?>
					<?php echo wp_get_attachment_image( $gid, array( 72, 72 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endforeach; ?>
			</div>
			<p>
				<button type="button" class="button" id="vl-gallery-upload"><?php esc_html_e( 'Add gallery images', 'vl-core' ); ?></button>
				<button type="button" class="button-link-delete" id="vl-gallery-clear"><?php esc_html_e( 'Clear gallery', 'vl-core' ); ?></button>
			</p>
		</div>

		<div class="vl-admin-section">
			<h3><?php esc_html_e( '6. Show on homepage section', 'vl-core' ); ?></h3>
			<label class="vl-check"><input type="checkbox" name="vl_is_featured" value="1" <?php checked( $is_featured, '1' ); ?>> <?php esc_html_e( 'Featured', 'vl-core' ); ?></label>
			<label class="vl-check"><input type="checkbox" name="vl_is_new" value="1" <?php checked( $is_new, '1' ); ?>> <?php esc_html_e( 'New Arrival', 'vl-core' ); ?></label>
			<label class="vl-check"><input type="checkbox" name="vl_is_trending" value="1" <?php checked( $is_trending, '1' ); ?>> <?php esc_html_e( 'Trending', 'vl-core' ); ?></label>
			<label class="vl-check"><input type="checkbox" name="vl_is_luxury" value="1" <?php checked( $is_luxury, '1' ); ?>> <?php esc_html_e( 'Luxury', 'vl-core' ); ?></label>
			<label class="vl-check"><input type="checkbox" name="vl_is_on_sale" value="1" <?php checked( $is_on_sale, '1' ); ?>> <?php esc_html_e( 'On Sale', 'vl-core' ); ?></label>
		</div>
	</div>
	<?php
}

/**
 * Save product meta.
 *
 * @param int $post_id Post ID.
 */
function vl_save_product_meta( $post_id ) {
	if ( ! isset( $_POST['vl_product_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vl_product_meta_nonce'] ) ), 'vl_save_product_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'fashion_product' !== get_post_type( $post_id ) ) {
		return;
	}

	// Keep title clean when saving.
	if ( isset( $_POST['post_title'] ) ) {
		$clean = vl_clean_product_title( wp_unslash( $_POST['post_title'] ) );
		if ( $clean ) {
			remove_action( 'save_post', 'vl_save_product_meta' );
			wp_update_post(
				array(
					'ID'         => $post_id,
					'post_title' => $clean,
				)
			);
			add_action( 'save_post', 'vl_save_product_meta' );
		}
	}

	// Featured image from our main upload button (preferred).
	if ( isset( $_POST['vl_featured_id'] ) ) {
		$thumb = (int) wp_unslash( $_POST['vl_featured_id'] );
		if ( $thumb > 0 ) {
			set_post_thumbnail( $post_id, $thumb );
		} elseif ( -1 === $thumb ) {
			delete_post_thumbnail( $post_id );
		}
	}

	$price = isset( $_POST['vl_price'] ) ? floatval( wp_unslash( $_POST['vl_price'] ) ) : '';
	update_post_meta( $post_id, '_vl_price', $price );

	$sale_price = isset( $_POST['vl_sale_price'] ) ? floatval( wp_unslash( $_POST['vl_sale_price'] ) ) : '';
	update_post_meta( $post_id, '_vl_sale_price', $sale_price );

	$gallery = isset( $_POST['vl_gallery_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['vl_gallery_ids'] ) ) : '';
	$gallery = preg_replace( '/[^0-9,\s]/', '', $gallery );
	update_post_meta( $post_id, '_vl_gallery_ids', $gallery );

	$flags = array( 'vl_is_featured', 'vl_is_new', 'vl_is_trending', 'vl_is_luxury', 'vl_is_on_sale' );
	foreach ( $flags as $flag ) {
		$value = isset( $_POST[ $flag ] ) ? '1' : '0';
		update_post_meta( $post_id, '_' . $flag, $value );
	}

	// Categories from the main product box (clear when none checked).
	if ( isset( $_POST['vl_product_meta_nonce'] ) ) {
		$cat_ids = isset( $_POST['vl_product_cats'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['vl_product_cats'] ) ) : array();
		$cat_ids = array_values( array_filter( $cat_ids ) );
		wp_set_object_terms( $post_id, $cat_ids, 'product_category', false );
	}
}
add_action( 'save_post', 'vl_save_product_meta' );

/**
 * Display price HTML for a product.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function vl_get_product_price_html( $post_id ) {
	$price      = vl_get_product_meta( $post_id, '_vl_price' );
	$sale_price = vl_get_product_meta( $post_id, '_vl_sale_price' );

	if ( $sale_price && floatval( $sale_price ) > 0 && floatval( $sale_price ) < floatval( $price ) ) {
		return '<div class="vl-price vl-price--sale"><del>' . esc_html( vl_format_price( $price ) ) . '</del> <ins>' . esc_html( vl_format_price( $sale_price ) ) . '</ins></div>';
	}

	if ( $price && floatval( $price ) > 0 ) {
		return '<div class="vl-price">' . esc_html( vl_format_price( $price ) ) . '</div>';
	}

	return '';
}

/**
 * Get gallery image URLs for a product.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function vl_get_product_gallery_urls( $post_id ) {
	$urls    = array();
	$thumb   = get_the_post_thumbnail_url( $post_id, 'large' );
	$gallery = vl_get_product_meta( $post_id, '_vl_gallery_ids' );

	if ( $thumb ) {
		$urls[] = $thumb;
	}

	if ( $gallery ) {
		$ids = array_filter( array_map( 'absint', explode( ',', $gallery ) ) );
		foreach ( $ids as $id ) {
			$url = wp_get_attachment_image_url( $id, 'large' );
			if ( $url && ! in_array( $url, $urls, true ) ) {
				$urls[] = $url;
			}
		}
	}

	return $urls;
}
