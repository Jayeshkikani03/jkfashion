<?php
/**
 * Single product template.
 *
 * @package Dynamic_Fashion_Theme
 */

get_header();

while ( have_posts() ) :
	the_post();

	$gallery = function_exists( 'vl_get_product_gallery_urls' ) ? vl_get_product_gallery_urls( get_the_ID() ) : array();
	if ( empty( $gallery ) && has_post_thumbnail() ) {
		$gallery[] = get_the_post_thumbnail_url( get_the_ID(), 'large' );
	}

	$terms = get_the_terms( get_the_ID(), 'product_category' );
	$title = get_the_title();
	if ( function_exists( 'vl_clean_product_title' ) ) {
		$title = vl_clean_product_title( $title );
	}
	?>

	<div class="vl-container" style="padding-top:1.5rem;padding-bottom:2.5rem;">
		<?php
		if ( function_exists( 'vl_render_breadcrumbs' ) ) {
			vl_render_breadcrumbs();
		}

		if ( isset( $_GET['vl_order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$ok  = 'ok' === sanitize_key( wp_unslash( $_GET['vl_order'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$msg = isset( $_GET['msg'] ) ? sanitize_text_field( rawurldecode( wp_unslash( $_GET['msg'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( $msg ) {
				printf(
					'<div class="vl-notice %1$s">%2$s</div>',
					$ok ? 'vl-notice--ok' : 'vl-notice--err',
					esc_html( $msg )
				);
			}
		}
		?>

		<article <?php post_class( 'vl-product-detail' ); ?>>
			<div class="vl-gallery">
				<div class="vl-gallery__main">
					<?php if ( ! empty( $gallery[0] ) ) : ?>
						<img data-vl-gallery-main src="<?php echo esc_url( $gallery[0] ); ?>" alt="<?php echo esc_attr( $title ); ?>">
					<?php else : ?>
						<div class="vl-placeholder-art"><?php echo esc_html( $title ); ?></div>
					<?php endif; ?>
				</div>
				<?php if ( count( $gallery ) > 1 ) : ?>
					<div class="vl-gallery__thumbs">
						<?php foreach ( array_slice( $gallery, 0, 4 ) as $url ) : ?>
							<button type="button" data-vl-gallery-thumb data-full="<?php echo esc_url( $url ); ?>">
								<img src="<?php echo esc_url( $url ); ?>" alt="" loading="lazy">
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="vl-product-summary">
				<p class="vl-product-time"><?php echo esc_html( sprintf( __( 'Added %s ago', 'dynamic-fashion' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ) ); ?></p>
				<h1><?php echo esc_html( $title ); ?></h1>
				<div class="vl-product-meta">
					<?php
					if ( $terms && ! is_wp_error( $terms ) ) {
						$links = array();
						foreach ( $terms as $term ) {
							$links[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
						}
						echo wp_kses_post( implode( ', ', $links ) );
					}
					?>
				</div>

				<div class="entry-content" style="margin:1.25rem 0;">
					<?php the_content(); ?>
				</div>

				<?php
				if ( function_exists( 'vl_render_cod_form' ) ) {
					vl_render_cod_form( get_the_ID() );
				} elseif ( function_exists( 'vl_get_product_price_html' ) ) {
					echo vl_get_product_price_html( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>

				<div class="vl-hero__actions" style="margin-top:1rem;">
					<button class="vl-btn vl-btn--dark" type="button" onclick="if(navigator.share){navigator.share({title:document.title,url:window.location.href});}else{navigator.clipboard.writeText(window.location.href);alert('Link copied');}">
						<?php esc_html_e( 'Share', 'dynamic-fashion' ); ?>
					</button>
				</div>
			</div>
		</article>

		<?php dft_render_ad( 'before_related' ); ?>

		<section class="vl-section" style="padding-bottom:0;">
			<div class="vl-section__head">
				<div>
					<h2><?php esc_html_e( 'You might also like', 'dynamic-fashion' ); ?></h2>
					<p><?php esc_html_e( 'Related looks from nearby collections.', 'dynamic-fashion' ); ?></p>
				</div>
			</div>
			<div class="vl-product-grid">
				<?php
				$related_args = array(
					'post_type'      => 'fashion_product',
					'posts_per_page' => 4,
					'post__not_in'   => array( get_the_ID() ),
				);
				if ( $terms && ! is_wp_error( $terms ) ) {
					$related_args['tax_query'] = array(
						array(
							'taxonomy' => 'product_category',
							'field'    => 'term_id',
							'terms'    => wp_list_pluck( $terms, 'term_id' ),
						),
					);
				}
				$related = new WP_Query( $related_args );
				if ( $related->have_posts() ) :
					while ( $related->have_posts() ) :
						$related->the_post();
						get_template_part( 'template-parts/products/card' );
					endwhile;
					wp_reset_postdata();
				else :
					?>
					<div class="vl-empty"><?php esc_html_e( 'More products coming soon.', 'dynamic-fashion' ); ?></div>
				<?php endif; ?>
			</div>
		</section>
	</div>
	<?php
endwhile;

get_footer();
