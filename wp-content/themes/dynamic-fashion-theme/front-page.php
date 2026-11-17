<?php
/**
 * Homepage — ads then products (no hero filters / featured category cards).
 *
 * @package Dynamic_Fashion_Theme
 */

get_header();

$settings = function_exists( 'vl_get_homepage_settings' ) ? vl_get_homepage_settings() : array();

dft_render_ad( 'below_hero' );

$sections = array(
	array(
		'title' => __( 'New arrivals', 'dynamic-fashion' ),
		'text'  => __( 'Fresh edits ready for your next look.', 'dynamic-fashion' ),
		'args'  => array( 'meta_flag' => '_vl_is_new', 'posts_per_page' => 8 ),
	),
	array(
		'title' => __( 'Trending now', 'dynamic-fashion' ),
		'text'  => __( 'Styles gaining attention this season.', 'dynamic-fashion' ),
		'args'  => array( 'meta_flag' => '_vl_is_trending', 'posts_per_page' => 8 ),
	),
	array(
		'title' => __( 'Luxury collection', 'dynamic-fashion' ),
		'text'  => __( 'Refined pieces for elevated occasions.', 'dynamic-fashion' ),
		'args'  => array( 'meta_flag' => '_vl_is_luxury', 'posts_per_page' => 8 ),
	),
	array(
		'title' => __( 'Sale and offers', 'dynamic-fashion' ),
		'text'  => __( 'Selected looks with promotional pricing.', 'dynamic-fashion' ),
		'args'  => array( 'meta_flag' => '_vl_is_on_sale', 'posts_per_page' => 8 ),
	),
);

$rendered = 0;
foreach ( $sections as $section ) {
	$query = function_exists( 'vl_query_products' )
		? vl_query_products( $section['args'] )
		: new WP_Query(
			array(
				'post_type'      => 'fashion_product',
				'posts_per_page' => 8,
			)
		);
	if ( ! $query->have_posts() ) {
		continue;
	}
	$rendered++;
	?>
	<section class="vl-section">
		<div class="vl-container">
			<div class="vl-section__head">
				<div>
					<h2><?php echo esc_html( $section['title'] ); ?></h2>
					<p><?php echo esc_html( $section['text'] ); ?></p>
				</div>
			</div>
			<div class="vl-product-grid">
				<?php
				$count = 0;
				while ( $query->have_posts() ) :
					$query->the_post();
					$count++;
					dft_render_product_and_maybe_ad( $count, 4 );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
}

if ( $rendered < 1 ) {
	$any = new WP_Query(
		array(
			'post_type'      => 'fashion_product',
			'posts_per_page' => 12,
		)
	);
	if ( $any->have_posts() ) :
		?>
		<section class="vl-section">
			<div class="vl-container">
				<div class="vl-section__head">
					<div>
						<h2><?php esc_html_e( 'Latest looks', 'dynamic-fashion' ); ?></h2>
						<p><?php esc_html_e( 'Fresh products from the catalog.', 'dynamic-fashion' ); ?></p>
					</div>
				</div>
				<div class="vl-product-grid">
					<?php
					$count = 0;
					while ( $any->have_posts() ) :
						$any->the_post();
						$count++;
						dft_render_product_and_maybe_ad( $count, 4 );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
		<?php
	endif;
}

$guides = new WP_Query(
	array(
		'post_type'      => 'fashion_article',
		'posts_per_page' => 3,
	)
);
if ( $guides->have_posts() ) :
	?>
	<section class="vl-section">
		<div class="vl-container">
			<div class="vl-section__head">
				<div>
					<h2><?php esc_html_e( 'Fashion guides', 'dynamic-fashion' ); ?></h2>
					<p><?php esc_html_e( 'Ideas for festive capsules and wearable luxury.', 'dynamic-fashion' ); ?></p>
				</div>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'fashion_article' ) ); ?>"><?php esc_html_e( 'View all', 'dynamic-fashion' ); ?></a>
			</div>
			<div class="vl-guide-grid">
				<?php
				while ( $guides->have_posts() ) :
					$guides->the_post();
					?>
					<article <?php post_class( 'vl-guide-card' ); ?>>
						<a class="vl-guide-card__media" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
							<?php else : ?>
								<div class="vl-placeholder-art" style="color:#f7f3ec;"><?php esc_html_e( 'Guide', 'dynamic-fashion' ); ?></div>
							<?php endif; ?>
						</a>
						<div class="vl-guide-card__body">
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt() ? get_the_excerpt() : get_the_content(), 22 ) ); ?></p>
						</div>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
endif;
?>

<section class="vl-section">
	<div class="vl-container">
		<div class="vl-newsletter">
			<div>
				<h2><?php echo esc_html( ! empty( $settings['newsletter_title'] ) ? $settings['newsletter_title'] : __( 'Style notes in your inbox', 'dynamic-fashion' ) ); ?></h2>
				<p><?php echo esc_html( ! empty( $settings['newsletter_text'] ) ? $settings['newsletter_text'] : '' ); ?></p>
			</div>
			<div class="vl-newsletter__note">
				<?php esc_html_e( 'Connect your preferred newsletter form later. This section is ready for integration.', 'dynamic-fashion' ); ?>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
