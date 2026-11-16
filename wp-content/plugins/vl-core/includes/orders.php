<?php
/**
 * COD orders — cart form → admin Orders list.
 *
 * @package VL_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Orders post type.
 */
function vl_register_orders_cpt() {
	register_post_type(
		'vl_order',
		array(
			'labels'              => array(
				'name'               => __( 'Orders (COD)', 'vl-core' ),
				'singular_name'      => __( 'Order', 'vl-core' ),
				'add_new_item'       => __( 'Add Order', 'vl-core' ),
				'edit_item'          => __( 'View Order', 'vl-core' ),
				'search_items'       => __( 'Search Orders', 'vl-core' ),
				'not_found'          => __( 'No orders yet', 'vl-core' ),
				'menu_name'          => __( 'Orders (COD)', 'vl-core' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-cart',
			'menu_position'       => 6,
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		)
	);
}
add_action( 'init', 'vl_register_orders_cpt' );

/**
 * Order status options.
 *
 * @return array
 */
function vl_order_statuses() {
	return array(
		'new'        => __( 'New', 'vl-core' ),
		'confirmed'  => __( 'Confirmed', 'vl-core' ),
		'shipped'    => __( 'Shipped', 'vl-core' ),
		'delivered'  => __( 'Delivered', 'vl-core' ),
		'cancelled'  => __( 'Cancelled', 'vl-core' ),
	);
}

/**
 * Handle COD order submit (AJAX + non-JS fallback).
 */
function vl_handle_cod_order() {
	$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

	$nonce = isset( $_POST['vl_cod_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['vl_cod_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'vl_cod_order' ) ) {
		vl_cod_respond( false, __( 'Security check failed. Refresh and try again.', 'vl-core' ), $is_ajax );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$name       = isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '';
	$phone      = isset( $_POST['customer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) ) : '';
	$address    = isset( $_POST['customer_address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['customer_address'] ) ) : '';
	$city       = isset( $_POST['customer_city'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_city'] ) ) : '';
	$pincode    = isset( $_POST['customer_pincode'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_pincode'] ) ) : '';
	$qty        = isset( $_POST['qty'] ) ? max( 1, absint( $_POST['qty'] ) ) : 1;
	$note       = isset( $_POST['customer_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['customer_note'] ) ) : '';

	if ( ! $product_id || 'fashion_product' !== get_post_type( $product_id ) ) {
		vl_cod_respond( false, __( 'Invalid product.', 'vl-core' ), $is_ajax );
	}
	if ( '' === $name || '' === $phone || '' === $address ) {
		vl_cod_respond( false, __( 'Please fill name, phone and address.', 'vl-core' ), $is_ajax );
	}

	$price      = floatval( vl_get_product_meta( $product_id, '_vl_price', 0 ) );
	$sale_price = floatval( vl_get_product_meta( $product_id, '_vl_sale_price', 0 ) );
	$unit       = ( $sale_price > 0 && $sale_price < $price ) ? $sale_price : $price;
	$total      = $unit * $qty;
	$title      = vl_clean_product_title( get_the_title( $product_id ) );

	$order_id = wp_insert_post(
		array(
			'post_type'   => 'vl_order',
			'post_status' => 'publish',
			'post_title'  => sprintf(
				/* translators: 1: customer name, 2: product title */
				__( 'COD — %1$s — %2$s', 'vl-core' ),
				$name,
				$title
			),
		),
		true
	);

	if ( is_wp_error( $order_id ) ) {
		vl_cod_respond( false, __( 'Could not place order. Try again.', 'vl-core' ), $is_ajax );
	}

	$meta = array(
		'_vl_order_product_id'   => $product_id,
		'_vl_order_product_name' => $title,
		'_vl_order_qty'          => $qty,
		'_vl_order_unit_price'   => $unit,
		'_vl_order_total'        => $total,
		'_vl_order_name'         => $name,
		'_vl_order_phone'        => $phone,
		'_vl_order_address'      => $address,
		'_vl_order_city'         => $city,
		'_vl_order_pincode'      => $pincode,
		'_vl_order_note'         => $note,
		'_vl_order_status'       => 'new',
		'_vl_order_payment'      => 'COD',
	);
	foreach ( $meta as $key => $value ) {
		update_post_meta( $order_id, $key, $value );
	}

	// Also leave a product comment so it appears under Comments on the product.
	wp_insert_comment(
		array(
			'comment_post_ID'      => $product_id,
			'comment_author'       => $name,
			'comment_author_email' => '',
			'comment_author_url'   => '',
			'comment_content'      => sprintf(
				"COD Order #%d\nPhone: %s\nAddress: %s, %s %s\nQty: %d | Total: %s\nNote: %s",
				$order_id,
				$phone,
				$address,
				$city,
				$pincode,
				$qty,
				vl_format_price( $total ),
				$note
			),
			'comment_type'         => 'vl_cod_order',
			'comment_approved'     => 1,
			'user_id'              => 0,
		)
	);

	vl_cod_respond(
		true,
		sprintf(
			/* translators: %s: order id */
			__( 'Order placed! Pay Cash on Delivery. Order #%s', 'vl-core' ),
			$order_id
		),
		$is_ajax,
		$order_id
	);
}
add_action( 'wp_ajax_vl_cod_order', 'vl_handle_cod_order' );
add_action( 'wp_ajax_nopriv_vl_cod_order', 'vl_handle_cod_order' );
add_action( 'admin_post_nopriv_vl_cod_order', 'vl_handle_cod_order' );
add_action( 'admin_post_vl_cod_order', 'vl_handle_cod_order' );

/**
 * JSON / redirect response helper.
 *
 * @param bool   $ok      Success.
 * @param string $message Message.
 * @param bool   $is_ajax Ajax.
 * @param int    $order_id Order ID.
 */
function vl_cod_respond( $ok, $message, $is_ajax, $order_id = 0 ) {
	if ( $is_ajax ) {
		if ( $ok ) {
			wp_send_json_success(
				array(
					'message'  => $message,
					'order_id' => $order_id,
				)
			);
		}
		wp_send_json_error( array( 'message' => $message ) );
	}

	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$redirect = add_query_arg(
		array(
			'vl_order' => $ok ? 'ok' : 'err',
			'msg'      => rawurlencode( $message ),
		),
		$redirect
	);
	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Admin columns for orders.
 *
 * @param array $columns Columns.
 * @return array
 */
function vl_order_columns( $columns ) {
	return array(
		'cb'         => $columns['cb'],
		'title'      => __( 'Order', 'vl-core' ),
		'vl_product' => __( 'Product', 'vl-core' ),
		'vl_customer'=> __( 'Customer', 'vl-core' ),
		'vl_phone'   => __( 'Phone', 'vl-core' ),
		'vl_total'   => __( 'Total', 'vl-core' ),
		'vl_status'  => __( 'Status', 'vl-core' ),
		'date'       => __( 'Date', 'vl-core' ),
	);
}
add_filter( 'manage_vl_order_posts_columns', 'vl_order_columns' );

/**
 * Order column content.
 *
 * @param string $column Column.
 * @param int    $post_id Post ID.
 */
function vl_order_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'vl_product':
			$pid  = (int) get_post_meta( $post_id, '_vl_order_product_id', true );
			$name = get_post_meta( $post_id, '_vl_order_product_name', true );
			$qty  = get_post_meta( $post_id, '_vl_order_qty', true );
			if ( $pid ) {
				echo '<a href="' . esc_url( get_edit_post_link( $pid ) ) . '">' . esc_html( $name ) . '</a>';
			} else {
				echo esc_html( $name );
			}
			echo ' × ' . esc_html( $qty );
			break;
		case 'vl_customer':
			echo esc_html( get_post_meta( $post_id, '_vl_order_name', true ) );
			$city = get_post_meta( $post_id, '_vl_order_city', true );
			if ( $city ) {
				echo '<br><span style="color:#646970;">' . esc_html( $city ) . '</span>';
			}
			break;
		case 'vl_phone':
			$phone = get_post_meta( $post_id, '_vl_order_phone', true );
			echo '<a href="tel:' . esc_attr( preg_replace( '/\s+/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a>';
			break;
		case 'vl_total':
			echo esc_html( vl_format_price( get_post_meta( $post_id, '_vl_order_total', true ) ) );
			echo ' <span style="color:#1f7a4d;font-weight:600;">COD</span>';
			break;
		case 'vl_status':
			$statuses = vl_order_statuses();
			$status   = get_post_meta( $post_id, '_vl_order_status', true );
			echo esc_html( isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status );
			break;
	}
}
add_action( 'manage_vl_order_posts_custom_column', 'vl_order_column_content', 10, 2 );

/**
 * Order detail metabox.
 */
function vl_order_meta_boxes() {
	add_meta_box(
		'vl_order_details',
		__( 'COD Order Details', 'vl-core' ),
		'vl_render_order_metabox',
		'vl_order',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'vl_order_meta_boxes' );

/**
 * Render order metabox.
 *
 * @param WP_Post $post Post.
 */
function vl_render_order_metabox( $post ) {
	wp_nonce_field( 'vl_save_order', 'vl_order_nonce' );
	$get = static function ( $key ) use ( $post ) {
		return get_post_meta( $post->ID, $key, true );
	};
	$statuses = vl_order_statuses();
	$status   = $get( '_vl_order_status' );
	?>
	<style>
		.vl-order-table{width:100%;border-collapse:collapse}
		.vl-order-table th{text-align:left;width:180px;padding:8px 10px;background:#f6f7f7;border:1px solid #dcdcde}
		.vl-order-table td{padding:8px 10px;border:1px solid #dcdcde}
	</style>
	<table class="vl-order-table">
		<tr><th><?php esc_html_e( 'Payment', 'vl-core' ); ?></th><td><strong>Cash on Delivery (COD)</strong></td></tr>
		<tr><th><?php esc_html_e( 'Status', 'vl-core' ); ?></th><td>
			<select name="vl_order_status">
				<?php foreach ( $statuses as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</td></tr>
		<tr><th><?php esc_html_e( 'Product', 'vl-core' ); ?></th><td><?php echo esc_html( $get( '_vl_order_product_name' ) ); ?> × <?php echo esc_html( $get( '_vl_order_qty' ) ); ?></td></tr>
		<tr><th><?php esc_html_e( 'Total', 'vl-core' ); ?></th><td><strong><?php echo esc_html( vl_format_price( $get( '_vl_order_total' ) ) ); ?></strong></td></tr>
		<tr><th><?php esc_html_e( 'Customer', 'vl-core' ); ?></th><td><?php echo esc_html( $get( '_vl_order_name' ) ); ?></td></tr>
		<tr><th><?php esc_html_e( 'Phone', 'vl-core' ); ?></th><td><a href="tel:<?php echo esc_attr( $get( '_vl_order_phone' ) ); ?>"><?php echo esc_html( $get( '_vl_order_phone' ) ); ?></a></td></tr>
		<tr><th><?php esc_html_e( 'Address', 'vl-core' ); ?></th><td><?php echo nl2br( esc_html( $get( '_vl_order_address' ) ) ); ?><br><?php echo esc_html( trim( $get( '_vl_order_city' ) . ' ' . $get( '_vl_order_pincode' ) ) ); ?></td></tr>
		<tr><th><?php esc_html_e( 'Note', 'vl-core' ); ?></th><td><?php echo esc_html( $get( '_vl_order_note' ) ?: '—' ); ?></td></tr>
	</table>
	<?php
}

/**
 * Save order status.
 *
 * @param int $post_id Post ID.
 */
function vl_save_order_meta( $post_id ) {
	if ( ! isset( $_POST['vl_order_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vl_order_nonce'] ) ), 'vl_save_order' ) ) {
		return;
	}
	if ( 'vl_order' !== get_post_type( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['vl_order_status'] ) ) {
		$status = sanitize_key( wp_unslash( $_POST['vl_order_status'] ) );
		if ( array_key_exists( $status, vl_order_statuses() ) ) {
			update_post_meta( $post_id, '_vl_order_status', $status );
		}
	}
}
add_action( 'save_post', 'vl_save_order_meta' );

/**
 * Front COD form markup.
 *
 * @param int $product_id Product ID.
 */
function vl_render_cod_form( $product_id ) {
	$price_html = vl_get_product_price_html( $product_id );
	?>
	<div class="vl-cod" id="vl-cod-box" data-product="<?php echo esc_attr( $product_id ); ?>">
		<?php
		$cod_note = __( 'No online payment — pay when product arrives.', 'vl-core' );
		if ( function_exists( 'vl_get_homepage_settings' ) ) {
			$s = vl_get_homepage_settings();
			if ( ! empty( $s['cod_note'] ) ) {
				$cod_note = $s['cod_note'];
			}
		}
		?>
		<div class="vl-cod__head">
			<strong><?php esc_html_e( 'Buy with Cash on Delivery', 'vl-core' ); ?></strong>
			<span><?php echo esc_html( $cod_note ); ?></span>
		</div>
		<?php if ( $price_html ) : ?>
			<div class="vl-cod__price"><?php echo $price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<?php endif; ?>
		<form class="vl-cod__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="vl_cod_order">
			<input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">
			<?php wp_nonce_field( 'vl_cod_order', 'vl_cod_nonce' ); ?>
			<p>
				<label for="vl_cod_name"><?php esc_html_e( 'Full name *', 'vl-core' ); ?></label>
				<input type="text" id="vl_cod_name" name="customer_name" required autocomplete="name">
			</p>
			<p>
				<label for="vl_cod_phone"><?php esc_html_e( 'Phone *', 'vl-core' ); ?></label>
				<input type="tel" id="vl_cod_phone" name="customer_phone" required autocomplete="tel" placeholder="10-digit mobile">
			</p>
			<p>
				<label for="vl_cod_address"><?php esc_html_e( 'Delivery address *', 'vl-core' ); ?></label>
				<textarea id="vl_cod_address" name="customer_address" rows="3" required placeholder="<?php esc_attr_e( 'House / street / landmark', 'vl-core' ); ?>"></textarea>
			</p>
			<div class="vl-cod__row">
				<p>
					<label for="vl_cod_city"><?php esc_html_e( 'City', 'vl-core' ); ?></label>
					<input type="text" id="vl_cod_city" name="customer_city" autocomplete="address-level2">
				</p>
				<p>
					<label for="vl_cod_pin"><?php esc_html_e( 'Pincode', 'vl-core' ); ?></label>
					<input type="text" id="vl_cod_pin" name="customer_pincode" autocomplete="postal-code">
				</p>
				<p>
					<label for="vl_cod_qty"><?php esc_html_e( 'Qty', 'vl-core' ); ?></label>
					<input type="number" id="vl_cod_qty" name="qty" min="1" value="1">
				</p>
			</div>
			<p>
				<label for="vl_cod_note"><?php esc_html_e( 'Note (optional)', 'vl-core' ); ?></label>
				<input type="text" id="vl_cod_note" name="customer_note" placeholder="<?php esc_attr_e( 'Size / colour preference', 'vl-core' ); ?>">
			</p>
			<button type="submit" class="vl-btn vl-btn--primary vl-cod__submit"><?php esc_html_e( 'Add to Cart — Place COD Order', 'vl-core' ); ?></button>
			<p class="vl-cod__msg" hidden></p>
		</form>
	</div>
	<?php
}
