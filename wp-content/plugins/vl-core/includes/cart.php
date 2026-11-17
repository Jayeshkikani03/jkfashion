<?php
/**
 * Shopping cart — add from cards, review, then COD checkout.
 *
 * @package VL_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie name for cart payload.
 *
 * @return string
 */
function vl_cart_cookie_name() {
	return 'vl_cart';
}

/**
 * Cart page URL.
 *
 * @return string
 */
function vl_get_cart_url() {
	$page = get_page_by_path( 'cart' );
	return $page ? get_permalink( $page ) : home_url( '/cart/' );
}

/**
 * Checkout page URL.
 *
 * @return string
 */
function vl_get_checkout_url() {
	$page = get_page_by_path( 'checkout' );
	return $page ? get_permalink( $page ) : home_url( '/checkout/' );
}

/**
 * Thanks page URL.
 *
 * @param int $order_id Optional order ID.
 * @return string
 */
function vl_get_thanks_url( $order_id = 0 ) {
	$page = get_page_by_path( 'thanks' );
	$url  = $page ? get_permalink( $page ) : home_url( '/thanks/' );
	if ( $order_id ) {
		$url = add_query_arg( 'order', absint( $order_id ), $url );
	}
	return $url;
}

/**
 * Unit price for a product (sale if lower).
 *
 * @param int $product_id Product ID.
 * @return float
 */
function vl_get_product_unit_price( $product_id ) {
	$price      = floatval( vl_get_product_meta( $product_id, '_vl_price', 0 ) );
	$sale_price = floatval( vl_get_product_meta( $product_id, '_vl_sale_price', 0 ) );
	if ( $sale_price > 0 && $sale_price < $price ) {
		return $sale_price;
	}
	return max( 0, $price );
}

/**
 * Raw cart map: product_id => qty.
 *
 * @return array<int,int>
 */
function vl_get_cart() {
	$name = vl_cart_cookie_name();
	if ( empty( $_COOKIE[ $name ] ) ) {
		return array();
	}

	$raw = wp_unslash( $_COOKIE[ $name ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		return array();
	}

	$cart = array();
	foreach ( $data as $id => $qty ) {
		$id  = absint( $id );
		$qty = absint( $qty );
		if ( $id && $qty > 0 && 'fashion_product' === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) {
			$cart[ $id ] = min( 99, $qty );
		}
	}
	return $cart;
}

/**
 * Persist cart cookie.
 *
 * @param array<int,int> $cart Cart map.
 */
function vl_set_cart( $cart ) {
	$clean = array();
	foreach ( (array) $cart as $id => $qty ) {
		$id  = absint( $id );
		$qty = absint( $qty );
		if ( $id && $qty > 0 ) {
			$clean[ (string) $id ] = min( 99, $qty );
		}
	}

	$json = wp_json_encode( $clean );
	$path = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
	$dom  = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
	setcookie( vl_cart_cookie_name(), $json, time() + WEEK_IN_SECONDS, $path, $dom, is_ssl(), true );
	$_COOKIE[ vl_cart_cookie_name() ] = $json;
}

/**
 * Empty the cart.
 */
function vl_clear_cart() {
	vl_set_cart( array() );
}

/**
 * Item count (sum of qty).
 *
 * @return int
 */
function vl_cart_count() {
	return (int) array_sum( vl_get_cart() );
}

/**
 * Cart lines with product display data.
 *
 * @return array<int,array>
 */
function vl_get_cart_lines() {
	$lines = array();
	foreach ( vl_get_cart() as $product_id => $qty ) {
		$title = vl_clean_product_title( get_the_title( $product_id ) );
		$unit  = vl_get_product_unit_price( $product_id );
		$thumb = get_the_post_thumbnail_url( $product_id, 'thumbnail' );
		$lines[] = array(
			'product_id' => $product_id,
			'title'      => $title,
			'qty'        => $qty,
			'unit'       => $unit,
			'line_total' => $unit * $qty,
			'url'        => get_permalink( $product_id ),
			'thumb'      => $thumb ? $thumb : '',
			'price_html' => vl_get_product_price_html( $product_id ),
		);
	}
	return $lines;
}

/**
 * Cart grand total.
 *
 * @return float
 */
function vl_cart_total() {
	$total = 0;
	foreach ( vl_get_cart_lines() as $line ) {
		$total += $line['line_total'];
	}
	return $total;
}

/**
 * Add / bump qty for a product.
 *
 * @param int $product_id Product ID.
 * @param int $qty        Qty to add.
 * @return bool
 */
function vl_cart_add_item( $product_id, $qty = 1 ) {
	$product_id = absint( $product_id );
	$qty        = max( 1, absint( $qty ) );
	if ( ! $product_id || 'fashion_product' !== get_post_type( $product_id ) ) {
		return false;
	}
	$cart = vl_get_cart();
	$cart[ $product_id ] = min( 99, ( isset( $cart[ $product_id ] ) ? $cart[ $product_id ] : 0 ) + $qty );
	vl_set_cart( $cart );
	return true;
}

/**
 * Set absolute qty (0 removes).
 *
 * @param int $product_id Product ID.
 * @param int $qty        Qty.
 * @return bool
 */
function vl_cart_set_qty( $product_id, $qty ) {
	$product_id = absint( $product_id );
	$qty        = absint( $qty );
	if ( ! $product_id ) {
		return false;
	}
	$cart = vl_get_cart();
	if ( $qty < 1 ) {
		unset( $cart[ $product_id ] );
	} else {
		if ( 'fashion_product' !== get_post_type( $product_id ) ) {
			return false;
		}
		$cart[ $product_id ] = min( 99, $qty );
	}
	vl_set_cart( $cart );
	return true;
}

/**
 * AJAX: add to cart.
 */
function vl_ajax_cart_add() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'vl_cart' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Refresh and try again.', 'vl-core' ) ) );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$qty        = isset( $_POST['qty'] ) ? max( 1, absint( $_POST['qty'] ) ) : 1;

	if ( ! vl_cart_add_item( $product_id, $qty ) ) {
		wp_send_json_error( array( 'message' => __( 'Could not add this product.', 'vl-core' ) ) );
	}

	wp_send_json_success(
		array(
			'message'  => __( 'Added to cart.', 'vl-core' ),
			'count'    => vl_cart_count(),
			'cart_url' => vl_get_cart_url(),
			'checkout' => vl_get_checkout_url(),
		)
	);
}
add_action( 'wp_ajax_vl_cart_add', 'vl_ajax_cart_add' );
add_action( 'wp_ajax_nopriv_vl_cart_add', 'vl_ajax_cart_add' );

/**
 * AJAX: update qty / remove.
 */
function vl_ajax_cart_update() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'vl_cart' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Refresh and try again.', 'vl-core' ) ) );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$qty        = isset( $_POST['qty'] ) ? absint( $_POST['qty'] ) : 0;

	if ( ! vl_cart_set_qty( $product_id, $qty ) ) {
		wp_send_json_error( array( 'message' => __( 'Could not update cart.', 'vl-core' ) ) );
	}

	wp_send_json_success(
		array(
			'message'      => $qty < 1 ? __( 'Removed from cart.', 'vl-core' ) : __( 'Cart updated.', 'vl-core' ),
			'count'        => vl_cart_count(),
			'total'        => vl_cart_total(),
			'total_html'   => vl_format_price( vl_cart_total() ),
			'empty'        => 0 === vl_cart_count(),
			'cart_url'     => vl_get_cart_url(),
			'checkout_url' => vl_get_checkout_url(),
		)
	);
}
add_action( 'wp_ajax_vl_cart_update', 'vl_ajax_cart_update' );
add_action( 'wp_ajax_nopriv_vl_cart_update', 'vl_ajax_cart_update' );

/**
 * Shortcode: cart page.
 *
 * @return string
 */
function vl_shortcode_cart() {
	ob_start();
	vl_render_cart_page();
	return ob_get_clean();
}
add_shortcode( 'vl_cart', 'vl_shortcode_cart' );

/**
 * Shortcode: checkout page.
 *
 * @return string
 */
function vl_shortcode_checkout() {
	ob_start();
	vl_render_checkout_page();
	return ob_get_clean();
}
add_shortcode( 'vl_checkout', 'vl_shortcode_checkout' );

/**
 * Shortcode: thanks page.
 *
 * @return string
 */
function vl_shortcode_thanks() {
	ob_start();
	vl_render_thanks_page();
	return ob_get_clean();
}
add_shortcode( 'vl_thanks', 'vl_shortcode_thanks' );

/**
 * Engagement coupon for thank-you (display only).
 *
 * @param float $total Order total.
 * @return array{show:bool,code:string,line:string,url:string}|null
 */
function vl_thanks_coupon_for_total( $total ) {
	$total = floatval( $total );
	if ( $total < 99 ) {
		return null;
	}

	$sale = home_url( '/collection/sale-and-offers/' );
	$new  = home_url( '/collection/new-arrivals/' );

	if ( $total >= 140 && $total < 160 ) {
		return array(
			'show' => true,
			'code' => 'VL99',
			'line' => __( 'Next look: ₹149 styles at ₹99 — use code VL99 while browsing.', 'vl-core' ),
			'url'  => $sale,
		);
	}

	if ( $total >= 99 && $total < 120 ) {
		return array(
			'show' => true,
			'code' => 'VL49',
			'line' => __( 'Next look: ₹99 styles at ₹49 — use code VL49 while browsing.', 'vl-core' ),
			'url'  => $new,
		);
	}

	return array(
		'show' => true,
		'code' => 'VL50',
		'line' => __( 'Extra ₹50 off your next browse — use code VL50 on Sale picks.', 'vl-core' ),
		'url'  => $sale,
	);
}

/**
 * Thanks page markup.
 */
function vl_render_thanks_page() {
	$order_id = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$total    = 0;
	$name     = '';
	$phone    = '';
	$address  = '';
	$city     = '';
	$pincode  = '';
	$items    = array();
	$payment  = 'COD';
	$valid    = false;

	if ( $order_id && 'vl_order' === get_post_type( $order_id ) ) {
		$valid   = true;
		$total   = floatval( get_post_meta( $order_id, '_vl_order_total', true ) );
		$name    = (string) get_post_meta( $order_id, '_vl_order_name', true );
		$phone   = (string) get_post_meta( $order_id, '_vl_order_phone', true );
		$address = (string) get_post_meta( $order_id, '_vl_order_address', true );
		$city    = (string) get_post_meta( $order_id, '_vl_order_city', true );
		$pincode = (string) get_post_meta( $order_id, '_vl_order_pincode', true );
		$payment = (string) get_post_meta( $order_id, '_vl_order_payment', true );
		$raw     = get_post_meta( $order_id, '_vl_order_items', true );
		$items   = is_array( $raw ) ? $raw : array();
		if ( ! $payment ) {
			$payment = 'COD';
		}
	}

	$coupon   = vl_thanks_coupon_for_total( $total );
	$shop_url = get_post_type_archive_link( 'fashion_product' );
	if ( ! $shop_url ) {
		$shop_url = home_url( '/' );
	}
	$home_url = home_url( '/' );
	?>
	<div class="vl-thanks" data-vl-thanks>
		<section class="vl-thanks__hero" aria-labelledby="vl-thanks-title">
			<span class="vl-thanks__mark" aria-hidden="true">
				<svg width="28" height="28" viewBox="0 0 24 24" fill="none" focusable="false">
					<path d="M5 12.5l4.2 4.2L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</span>
			<p class="vl-thanks__eyebrow"><?php esc_html_e( 'Order confirmed', 'vl-core' ); ?></p>
			<h1 id="vl-thanks-title"><?php esc_html_e( 'Thank you', 'vl-core' ); ?><?php echo $name ? esc_html( ', ' . $name ) : ''; ?></h1>
			<p class="vl-thanks__lead">
				<?php
				if ( $valid ) {
					printf(
						/* translators: %s: order id */
						esc_html__( 'Order #%s is placed with Cash on Delivery. We’ll confirm delivery details soon.', 'vl-core' ),
						esc_html( (string) $order_id )
					);
				} else {
					esc_html_e( 'Your order was received. Continue browsing for more looks.', 'vl-core' );
				}
				?>
			</p>
		</section>

		<?php if ( $valid ) : ?>
			<div class="vl-thanks__layout">
				<div class="vl-thanks__main">
					<div class="vl-thanks__panel">
						<div class="vl-thanks__panel-head">
							<h2><?php esc_html_e( 'Order summary', 'vl-core' ); ?></h2>
							<span class="vl-thanks__chip"><?php echo esc_html( $payment ); ?></span>
						</div>

						<?php if ( $items ) : ?>
							<ul class="vl-thanks__items">
								<?php foreach ( $items as $line ) : ?>
									<?php
									$line_title = isset( $line['title'] ) ? $line['title'] : ( isset( $line['name'] ) ? $line['name'] : __( 'Item', 'vl-core' ) );
									$line_qty   = isset( $line['qty'] ) ? absint( $line['qty'] ) : 1;
									$line_total = isset( $line['line_total'] ) ? $line['line_total'] : ( isset( $line['total'] ) ? $line['total'] : 0 );
									$pid        = isset( $line['product_id'] ) ? absint( $line['product_id'] ) : 0;
									$thumb      = $pid ? get_the_post_thumbnail_url( $pid, 'thumbnail' ) : '';
									?>
									<li class="vl-thanks__item">
										<span class="vl-thanks__thumb">
											<?php if ( $thumb ) : ?>
												<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
											<?php else : ?>
												<span class="vl-placeholder-art"><?php echo esc_html( wp_trim_words( $line_title, 2, '' ) ); ?></span>
											<?php endif; ?>
										</span>
										<span class="vl-thanks__item-info">
											<strong><?php echo esc_html( $line_title ); ?></strong>
											<span><?php echo esc_html( sprintf( /* translators: %d qty */ __( 'Qty %d', 'vl-core' ), $line_qty ) ); ?></span>
										</span>
										<span class="vl-thanks__item-price"><?php echo esc_html( function_exists( 'vl_format_price' ) ? vl_format_price( $line_total ) : $line_total ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<p class="vl-thanks__muted"><?php echo esc_html( (string) get_post_meta( $order_id, '_vl_order_product_name', true ) ); ?></p>
						<?php endif; ?>

						<div class="vl-thanks__total">
							<span><?php esc_html_e( 'Total to pay on delivery', 'vl-core' ); ?></span>
							<strong><?php echo esc_html( function_exists( 'vl_format_price' ) ? vl_format_price( $total ) : $total ); ?></strong>
						</div>
					</div>

					<?php if ( $address || $city || $phone ) : ?>
						<div class="vl-thanks__panel vl-thanks__panel--soft">
							<h2><?php esc_html_e( 'Delivery details', 'vl-core' ); ?></h2>
							<dl class="vl-thanks__meta">
								<?php if ( $name ) : ?>
									<div><dt><?php esc_html_e( 'Name', 'vl-core' ); ?></dt><dd><?php echo esc_html( $name ); ?></dd></div>
								<?php endif; ?>
								<?php if ( $phone ) : ?>
									<div><dt><?php esc_html_e( 'Phone', 'vl-core' ); ?></dt><dd><?php echo esc_html( $phone ); ?></dd></div>
								<?php endif; ?>
								<?php if ( $address || $city || $pincode ) : ?>
									<div>
										<dt><?php esc_html_e( 'Address', 'vl-core' ); ?></dt>
										<dd>
											<?php
											echo esc_html(
												trim(
													implode(
														', ',
														array_filter(
															array( $address, $city, $pincode )
														)
													)
												)
											);
											?>
										</dd>
									</div>
								<?php endif; ?>
							</dl>
						</div>
					<?php endif; ?>
				</div>

				<aside class="vl-thanks__side">
					<?php if ( $coupon && ! empty( $coupon['show'] ) ) : ?>
						<div class="vl-thanks__coupon">
							<span class="vl-thanks__coupon-label"><?php esc_html_e( 'Your next look offer', 'vl-core' ); ?></span>
							<strong class="vl-thanks__coupon-code"><?php echo esc_html( $coupon['code'] ); ?></strong>
							<p><?php echo esc_html( $coupon['line'] ); ?></p>
							<a class="vl-btn vl-btn--primary" href="<?php echo esc_url( $coupon['url'] ); ?>"><?php esc_html_e( 'Browse more looks', 'vl-core' ); ?></a>
						</div>
					<?php endif; ?>

					<div class="vl-thanks__actions">
						<a class="vl-btn vl-btn--primary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Continue shopping', 'vl-core' ); ?></a>
						<a class="vl-btn vl-btn--dark" href="<?php echo esc_url( $home_url ); ?>"><?php esc_html_e( 'Back to home', 'vl-core' ); ?></a>
					</div>
					<p class="vl-thanks__note"><?php esc_html_e( 'Keep your phone reachable — our team may call to confirm the COD delivery.', 'vl-core' ); ?></p>
				</aside>
			</div>
		<?php else : ?>
			<div class="vl-thanks__actions vl-thanks__actions--solo">
				<a class="vl-btn vl-btn--primary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Continue shopping', 'vl-core' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Cart page markup.
 */
function vl_render_cart_page() {
	$lines = vl_get_cart_lines();
	?>
	<div class="vl-cart" data-vl-cart>
		<div class="vl-section__head" style="margin-bottom:1.25rem;">
			<div>
				<h1><?php esc_html_e( 'Your cart', 'vl-core' ); ?></h1>
				<p><?php esc_html_e( 'Review items, then checkout with Cash on Delivery.', 'vl-core' ); ?></p>
			</div>
		</div>

		<?php if ( empty( $lines ) ) : ?>
			<div class="vl-empty vl-cart__empty">
				<p><?php esc_html_e( 'Your cart is empty.', 'vl-core' ); ?></p>
				<a class="vl-btn vl-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Continue shopping', 'vl-core' ); ?></a>
			</div>
		<?php else : ?>
			<div class="vl-cart__list">
				<?php foreach ( $lines as $line ) : ?>
					<article class="vl-cart__row" data-product-id="<?php echo esc_attr( $line['product_id'] ); ?>">
						<a class="vl-cart__thumb" href="<?php echo esc_url( $line['url'] ); ?>">
							<?php if ( $line['thumb'] ) : ?>
								<img src="<?php echo esc_url( $line['thumb'] ); ?>" alt="" loading="lazy">
							<?php else : ?>
								<span class="vl-placeholder-art"><?php echo esc_html( wp_trim_words( $line['title'], 3, '' ) ); ?></span>
							<?php endif; ?>
						</a>
						<div class="vl-cart__info">
							<h2 class="vl-cart__title"><a href="<?php echo esc_url( $line['url'] ); ?>"><?php echo esc_html( $line['title'] ); ?></a></h2>
							<div class="vl-cart__unit"><?php echo $line['price_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
							<div class="vl-cart__controls">
								<label class="screen-reader-text" for="vl-qty-<?php echo esc_attr( $line['product_id'] ); ?>"><?php esc_html_e( 'Quantity', 'vl-core' ); ?></label>
								<input
									class="vl-cart__qty"
									id="vl-qty-<?php echo esc_attr( $line['product_id'] ); ?>"
									type="number"
									min="1"
									max="99"
									value="<?php echo esc_attr( $line['qty'] ); ?>"
									data-vl-cart-qty
									data-product-id="<?php echo esc_attr( $line['product_id'] ); ?>"
								>
								<button type="button" class="vl-cart__remove" data-vl-cart-remove data-product-id="<?php echo esc_attr( $line['product_id'] ); ?>">
									<?php esc_html_e( 'Remove', 'vl-core' ); ?>
								</button>
							</div>
						</div>
						<div class="vl-cart__line-total" data-vl-line-total>
							<?php echo esc_html( vl_format_price( $line['line_total'] ) ); ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>

			<div class="vl-cart__footer">
				<div class="vl-cart__totals">
					<span><?php esc_html_e( 'Subtotal', 'vl-core' ); ?></span>
					<strong data-vl-cart-total><?php echo esc_html( vl_format_price( vl_cart_total() ) ); ?></strong>
				</div>
				<?php
				$cart_coupon = vl_thanks_coupon_for_total( vl_cart_total() );
				if ( $cart_coupon && ! empty( $cart_coupon['show'] ) ) :
					?>
					<div class="vl-offer-banner vl-offer-banner--cart">
						<div class="vl-offer-banner__copy">
							<span class="vl-offer-banner__eyebrow"><?php esc_html_e( 'Unlocked — cart ₹99+', 'vl-core' ); ?></span>
							<strong class="vl-offer-banner__title"><?php echo esc_html( $cart_coupon['code'] ); ?></strong>
							<p><?php echo esc_html( $cart_coupon['line'] ); ?></p>
						</div>
					</div>
				<?php endif; ?>
				<p class="vl-cart__note"><?php esc_html_e( 'Payment: Cash on Delivery only. No online payment.', 'vl-core' ); ?></p>
				<div class="vl-cart__actions">
					<a class="vl-btn vl-btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Continue shopping', 'vl-core' ); ?></a>
					<a class="vl-btn vl-btn--primary" href="<?php echo esc_url( vl_get_checkout_url() ); ?>"><?php esc_html_e( 'Proceed to checkout', 'vl-core' ); ?></a>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Checkout page markup (COD for cart).
 */
function vl_render_checkout_page() {
	$lines = vl_get_cart_lines();

	if ( isset( $_GET['vl_order'] ) && 'err' === sanitize_key( wp_unslash( $_GET['vl_order'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$msg = isset( $_GET['msg'] ) ? sanitize_text_field( rawurldecode( wp_unslash( $_GET['msg'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $msg ) {
			printf( '<div class="vl-notice vl-notice--err">%s</div>', esc_html( $msg ) );
		}
	}

	if ( empty( $lines ) ) {
		?>
		<div class="vl-empty">
			<p><?php esc_html_e( 'Your cart is empty. Add products before checkout.', 'vl-core' ); ?></p>
			<a class="vl-btn vl-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Browse products', 'vl-core' ); ?></a>
		</div>
		<?php
		return;
	}

	$cod_note = __( 'No online payment — pay when products arrive.', 'vl-core' );
	if ( function_exists( 'vl_get_homepage_settings' ) ) {
		$s = vl_get_homepage_settings();
		if ( ! empty( $s['cod_note'] ) ) {
			$cod_note = $s['cod_note'];
		}
	}

	$cart_total = vl_cart_total();
	$coupon     = vl_thanks_coupon_for_total( $cart_total );
	?>
	<div class="vl-checkout" data-vl-checkout>
		<div class="vl-section__head" style="margin-bottom:1.25rem;">
			<div>
				<h1><?php esc_html_e( 'Checkout', 'vl-core' ); ?></h1>
				<p><?php echo esc_html( $cod_note ); ?></p>
			</div>
		</div>

		<?php if ( $coupon && ! empty( $coupon['show'] ) ) : ?>
			<div class="vl-offer-banner" role="status">
				<div class="vl-offer-banner__copy">
					<span class="vl-offer-banner__eyebrow"><?php esc_html_e( 'Unlocked — order ₹99+', 'vl-core' ); ?></span>
					<strong class="vl-offer-banner__title"><?php esc_html_e( 'Your exclusive next-look coupon', 'vl-core' ); ?></strong>
					<p><?php echo esc_html( $coupon['line'] ); ?></p>
				</div>
				<div class="vl-offer-banner__code">
					<span><?php esc_html_e( 'Code', 'vl-core' ); ?></span>
					<em><?php echo esc_html( $coupon['code'] ); ?></em>
				</div>
			</div>
		<?php else : ?>
			<div class="vl-offer-banner vl-offer-banner--locked" role="status">
				<div class="vl-offer-banner__copy">
					<span class="vl-offer-banner__eyebrow"><?php esc_html_e( 'Almost there', 'vl-core' ); ?></span>
					<strong class="vl-offer-banner__title"><?php esc_html_e( 'Add more to unlock a coupon', 'vl-core' ); ?></strong>
					<p>
						<?php
						printf(
							/* translators: %s: remaining amount */
							esc_html__( 'Reach ₹99 total to unlock your next-look coupon. Need about %s more.', 'vl-core' ),
							esc_html( function_exists( 'vl_format_price' ) ? vl_format_price( max( 0, 99 - $cart_total ) ) : ( 99 - $cart_total ) )
						);
						?>
					</p>
				</div>
				<a class="vl-btn vl-btn--dark" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Add more looks', 'vl-core' ); ?></a>
			</div>
		<?php endif; ?>

		<div class="vl-checkout__grid">
			<aside class="vl-checkout__summary">
				<h2><?php esc_html_e( 'Order summary', 'vl-core' ); ?></h2>
				<ul class="vl-checkout__items">
					<?php foreach ( $lines as $line ) : ?>
						<li>
							<span class="vl-checkout__item-name"><?php echo esc_html( $line['title'] ); ?> × <?php echo esc_html( (string) $line['qty'] ); ?></span>
							<span><?php echo esc_html( vl_format_price( $line['line_total'] ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
				<div class="vl-checkout__total">
					<span><?php esc_html_e( 'Total (COD)', 'vl-core' ); ?></span>
					<strong><?php echo esc_html( vl_format_price( $cart_total ) ); ?></strong>
				</div>
				<?php if ( $coupon && ! empty( $coupon['show'] ) ) : ?>
					<div class="vl-checkout__perk">
						<span class="vl-checkout__perk-label"><?php esc_html_e( 'Included perk', 'vl-core' ); ?></span>
						<strong><?php echo esc_html( $coupon['code'] ); ?></strong>
						<p><?php esc_html_e( 'Shown again on the thank-you page after you place this COD order.', 'vl-core' ); ?></p>
					</div>
				<?php endif; ?>
				<a class="vl-checkout__edit" href="<?php echo esc_url( vl_get_cart_url() ); ?>"><?php esc_html_e( 'Edit cart', 'vl-core' ); ?></a>
			</aside>

			<div class="vl-checkout__form-wrap">
				<?php vl_render_cod_checkout_form(); ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * COD form for multi-item cart checkout.
 */
function vl_render_cod_checkout_form() {
	?>
	<div class="vl-cod vl-cod--checkout" id="vl-cod-box">
		<div class="vl-cod__head">
			<strong><?php esc_html_e( 'Delivery details', 'vl-core' ); ?></strong>
			<span><?php esc_html_e( 'Place order — pay Cash on Delivery.', 'vl-core' ); ?></span>
		</div>
		<form class="vl-cod__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-vl-checkout-form>
			<input type="hidden" name="action" value="vl_cod_order">
			<input type="hidden" name="from_cart" value="1">
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
			</div>
			<p>
				<label for="vl_cod_note"><?php esc_html_e( 'Note (optional)', 'vl-core' ); ?></label>
				<input type="text" id="vl_cod_note" name="customer_note" placeholder="<?php esc_attr_e( 'Size / colour preference', 'vl-core' ); ?>">
			</p>
			<button type="submit" class="vl-btn vl-btn--primary vl-cod__submit"><?php esc_html_e( 'Place COD order', 'vl-core' ); ?></button>
			<p class="vl-cod__msg" hidden></p>
		</form>
	</div>
	<?php
}

/**
 * Compact add-to-cart controls for product page.
 *
 * @param int $product_id Product ID.
 */
function vl_render_add_to_cart_box( $product_id ) {
	$product_id = absint( $product_id );
	$price_html = vl_get_product_price_html( $product_id );
	?>
	<div class="vl-atc" data-vl-atc data-product-id="<?php echo esc_attr( $product_id ); ?>">
		<?php if ( $price_html ) : ?>
			<div class="vl-atc__price"><?php echo $price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<?php endif; ?>
		<div class="vl-atc__row">
			<label class="vl-atc__qty-label" for="vl-atc-qty-<?php echo esc_attr( $product_id ); ?>"><?php esc_html_e( 'Qty', 'vl-core' ); ?></label>
			<input type="number" class="vl-atc__qty" id="vl-atc-qty-<?php echo esc_attr( $product_id ); ?>" min="1" max="99" value="1" data-vl-atc-qty>
		</div>
		<div class="vl-atc__actions">
			<button type="button" class="vl-btn vl-btn--primary" data-vl-add-cart data-product-id="<?php echo esc_attr( $product_id ); ?>" data-vl-goto-cart="1">
				<?php esc_html_e( 'Add to cart', 'vl-core' ); ?>
			</button>
		</div>
		<p class="vl-atc__msg" hidden></p>
		<p class="vl-atc__hint"><?php esc_html_e( 'Add to cart, then complete checkout when you are ready.', 'vl-core' ); ?></p>
	</div>
	<?php
}

/**
 * Ensure Cart + Checkout pages exist.
 *
 * @return array
 */
function vl_ensure_cart_pages() {
	$defs = array(
		'cart'     => array(
			'title'   => 'Cart',
			'content' => '[vl_cart]',
		),
		'checkout' => array(
			'title'   => 'Checkout',
			'content' => '[vl_checkout]',
		),
		'thanks'   => array(
			'title'   => 'Thanks',
			'content' => '[vl_thanks]',
		),
	);
	$ids = array();

	foreach ( $defs as $slug => $def ) {
		$page = get_page_by_path( $slug );
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
				update_post_meta( $id, '_vl_show_in_footer', '0' );
				update_post_meta( $id, '_vl_show_in_header', '0' );
			}
			continue;
		}
		$ids[ $slug ] = (int) $page->ID;
		if ( false === strpos( $page->post_content, '[vl_' ) ) {
			wp_update_post(
				array(
					'ID'           => $page->ID,
					'post_content' => $def['content'],
					'post_title'   => $def['title'],
				)
			);
		}
	}

	return $ids;
}
add_action( 'init', 'vl_ensure_cart_pages', 30 );
