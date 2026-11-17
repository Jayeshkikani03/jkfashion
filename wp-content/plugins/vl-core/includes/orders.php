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
 * Build order line items from cart or single product POST.
 *
 * @return array{lines:array,total:float,summary:string}|WP_Error
 */
function vl_cod_collect_order_lines() {
	$from_cart = ! empty( $_POST['from_cart'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$lines     = array();

	if ( $from_cart && function_exists( 'vl_get_cart_lines' ) ) {
		foreach ( vl_get_cart_lines() as $line ) {
			$lines[] = array(
				'product_id' => (int) $line['product_id'],
				'title'      => $line['title'],
				'qty'        => (int) $line['qty'],
				'unit'       => (float) $line['unit'],
				'line_total' => (float) $line['line_total'],
			);
		}
	} else {
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$qty        = isset( $_POST['qty'] ) ? max( 1, absint( $_POST['qty'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! $product_id || 'fashion_product' !== get_post_type( $product_id ) ) {
			return new WP_Error( 'invalid_product', __( 'Invalid product.', 'vl-core' ) );
		}
		$unit  = function_exists( 'vl_get_product_unit_price' ) ? vl_get_product_unit_price( $product_id ) : floatval( vl_get_product_meta( $product_id, '_vl_price', 0 ) );
		$title = vl_clean_product_title( get_the_title( $product_id ) );
		$lines[] = array(
			'product_id' => $product_id,
			'title'      => $title,
			'qty'        => $qty,
			'unit'       => $unit,
			'line_total' => $unit * $qty,
		);
	}

	if ( empty( $lines ) ) {
		return new WP_Error( 'empty_cart', __( 'Your cart is empty.', 'vl-core' ) );
	}

	$total   = 0;
	$names   = array();
	$qty_all = 0;
	foreach ( $lines as $line ) {
		$total  += $line['line_total'];
		$qty_all += $line['qty'];
		$names[] = $line['title'];
	}

	$summary = $names[0];
	if ( count( $names ) > 1 ) {
		$summary = sprintf(
			/* translators: 1: first product, 2: extra item count */
			__( '%1$s + %2$d more', 'vl-core' ),
			$names[0],
			count( $names ) - 1
		);
	}

	return array(
		'lines'   => $lines,
		'total'   => $total,
		'summary' => $summary,
		'qty'     => $qty_all,
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

	$name    = isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '';
	$phone   = isset( $_POST['customer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) ) : '';
	$address = isset( $_POST['customer_address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['customer_address'] ) ) : '';
	$city    = isset( $_POST['customer_city'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_city'] ) ) : '';
	$pincode = isset( $_POST['customer_pincode'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_pincode'] ) ) : '';
	$note    = isset( $_POST['customer_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['customer_note'] ) ) : '';
	$from_cart = ! empty( $_POST['from_cart'] );

	if ( '' === $name || '' === $phone || '' === $address ) {
		vl_cod_respond( false, __( 'Please fill name, phone and address.', 'vl-core' ), $is_ajax );
	}

	$bundle = vl_cod_collect_order_lines();
	if ( is_wp_error( $bundle ) ) {
		vl_cod_respond( false, $bundle->get_error_message(), $is_ajax );
	}

	$lines   = $bundle['lines'];
	$total   = $bundle['total'];
	$summary = $bundle['summary'];
	$first   = $lines[0];

	$order_id = wp_insert_post(
		array(
			'post_type'   => 'vl_order',
			'post_status' => 'publish',
			'post_title'  => sprintf(
				/* translators: 1: customer name, 2: product summary */
				__( 'COD — %1$s — %2$s', 'vl-core' ),
				$name,
				$summary
			),
		),
		true
	);

	if ( is_wp_error( $order_id ) ) {
		vl_cod_respond( false, __( 'Could not place order. Try again.', 'vl-core' ), $is_ajax );
	}

	$meta = array(
		'_vl_order_product_id'   => $first['product_id'],
		'_vl_order_product_name' => $summary,
		'_vl_order_qty'          => $bundle['qty'],
		'_vl_order_unit_price'   => $first['unit'],
		'_vl_order_total'        => $total,
		'_vl_order_items'        => $lines,
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

	foreach ( $lines as $line ) {
		wp_insert_comment(
			array(
				'comment_post_ID'      => $line['product_id'],
				'comment_author'       => $name,
				'comment_author_email' => '',
				'comment_author_url'   => '',
				'comment_content'      => sprintf(
					"COD Order #%d\nPhone: %s\nAddress: %s, %s %s\nQty: %d | Line: %s\nOrder total: %s\nNote: %s",
					$order_id,
					$phone,
					$address,
					$city,
					$pincode,
					$line['qty'],
					vl_format_price( $line['line_total'] ),
					vl_format_price( $total ),
					$note
				),
				'comment_type'         => 'vl_cod_order',
				'comment_approved'     => 1,
				'user_id'              => 0,
			)
		);
	}

	if ( $from_cart && function_exists( 'vl_clear_cart' ) ) {
		vl_clear_cart();
	}

	$redirect = function_exists( 'vl_get_thanks_url' ) ? vl_get_thanks_url( $order_id ) : home_url( '/thanks/' );

	vl_cod_respond(
		true,
		sprintf(
			/* translators: %s: order id */
			__( 'Order placed! Pay Cash on Delivery. Order #%s', 'vl-core' ),
			$order_id
		),
		$is_ajax,
		$order_id,
		$redirect
	);
}
add_action( 'wp_ajax_vl_cod_order', 'vl_handle_cod_order' );
add_action( 'wp_ajax_nopriv_vl_cod_order', 'vl_handle_cod_order' );
add_action( 'admin_post_nopriv_vl_cod_order', 'vl_handle_cod_order' );
add_action( 'admin_post_vl_cod_order', 'vl_handle_cod_order' );

/**
 * JSON / redirect response helper.
 *
 * @param bool   $ok       Success.
 * @param string $message  Message.
 * @param bool   $is_ajax  Ajax.
 * @param int    $order_id Order ID.
 * @param string $base_url Optional redirect base.
 */
function vl_cod_respond( $ok, $message, $is_ajax, $order_id = 0, $base_url = '' ) {
	if ( $is_ajax ) {
		if ( $ok ) {
			$redirect_url = $base_url ? $base_url : '';
			if ( $redirect_url && false === strpos( $redirect_url, 'thanks' ) ) {
				$redirect_url = add_query_arg(
					array(
						'vl_order' => 'ok',
						'msg'      => rawurlencode( $message ),
					),
					$redirect_url
				);
			}
			wp_send_json_success(
				array(
					'message'      => $message,
					'order_id'     => $order_id,
					'count'        => 0,
					'redirect_url' => $redirect_url,
				)
			);
		}
		wp_send_json_error( array( 'message' => $message ) );
	}

	$redirect = $base_url ? $base_url : ( wp_get_referer() ? wp_get_referer() : home_url( '/' ) );
	if ( $ok && $base_url && false !== strpos( $base_url, 'thanks' ) ) {
		wp_safe_redirect( $redirect );
		exit;
	}
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
			$items = get_post_meta( $post_id, '_vl_order_items', true );
			if ( is_array( $items ) && count( $items ) > 1 ) {
				foreach ( $items as $item ) {
					$label = isset( $item['title'] ) ? $item['title'] : '';
					$iqty  = isset( $item['qty'] ) ? $item['qty'] : 1;
					$pid   = isset( $item['product_id'] ) ? (int) $item['product_id'] : 0;
					if ( $pid ) {
						echo '<a href="' . esc_url( get_edit_post_link( $pid ) ) . '">' . esc_html( $label ) . '</a>';
					} else {
						echo esc_html( $label );
					}
					echo ' × ' . esc_html( (string) $iqty ) . '<br>';
				}
				break;
			}
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
		<tr><th><?php esc_html_e( 'Items', 'vl-core' ); ?></th><td>
			<?php
			$items = get_post_meta( $post->ID, '_vl_order_items', true );
			if ( is_array( $items ) && $items ) {
				echo '<ul style="margin:0;padding-left:1.1rem;">';
				foreach ( $items as $item ) {
					printf(
						'<li>%1$s × %2$s — %3$s</li>',
						esc_html( isset( $item['title'] ) ? $item['title'] : '' ),
						esc_html( (string) ( isset( $item['qty'] ) ? $item['qty'] : 1 ) ),
						esc_html( vl_format_price( isset( $item['line_total'] ) ? $item['line_total'] : 0 ) )
					);
				}
				echo '</ul>';
			} else {
				echo esc_html( $get( '_vl_order_product_name' ) ) . ' × ' . esc_html( $get( '_vl_order_qty' ) );
			}
			?>
		</td></tr>
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
 * Front product buy box — add to cart / buy now (COD at checkout).
 *
 * @param int $product_id Product ID.
 */
function vl_render_cod_form( $product_id ) {
	if ( function_exists( 'vl_render_add_to_cart_box' ) ) {
		vl_render_add_to_cart_box( $product_id );
		return;
	}
	echo '<p><a class="vl-btn vl-btn--primary" href="' . esc_url( get_permalink( $product_id ) ) . '">' . esc_html__( 'View product', 'vl-core' ) . '</a></p>';
}
