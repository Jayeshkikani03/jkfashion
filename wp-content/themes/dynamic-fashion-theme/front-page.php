<?php
/**
 * Homepage template.
 *
 * @package Dynamic_Fashion_Theme
 */

get_header();

$settings = function_exists( 'vl_get_homepage_settings' ) ? vl_get_homepage_settings() : array(
	'hero_title'         => 'Discover refined fashion edits',
	'hero_text'          => 'Curated looks across new arrivals, luxury collections, trending styles, and seasonal offers.',
	'hero_cta_label'     => 'Explore collections',
	'hero_cta_url'       => home_url( '/' ),
	'hero_secondary'     => 'Our story',
	'hero_secondary_url' => home_url( '/about/' ),
	'newsletter_title'   => 'Style notes in your inbox',
	'newsletter_text'    => 'Get new arrivals and styling ideas.',
);
?>

<section class="vl-hero">
	<div class="vl-container">
		<div class="vl-hero__panel">
			<div>
				<span class="vl-hero__eyebrow"><?php esc_html_e( 'Vastralaxmi Fashion', 'dynamic-fashion' ); ?></span>
				<h1><?php echo esc_html( $settings['hero_title'] ); ?></h1>
				<p><?php echo esc_html( $settings['hero_text'] ); ?></p>
				<div class="vl-hero__actions">
					<a class="vl-btn vl-btn--primary" href="<?php echo esc_url( $settings['hero_cta_url'] ); ?>"><?php echo esc_html( $settings['hero_cta_label'] ); ?></a>
					<a class="vl-btn vl-btn--ghost" href="<?php echo esc_url( $settings['hero_secondary_url'] ); ?>"><?php echo esc_html( $settings['hero_secondary'] ); ?></a>
				</div>
			</div>
			<div class="vl-hero__stats">
				<div class="vl-stat-card">
					<strong><?php esc_html_e( 'Editorial', 'dynamic-fashion' ); ?></strong>
					<span><?php esc_html_e( 'Polished modern presentation', 'dynamic-fashion' ); ?></span>
				</div>
				<div class="vl-stat-card">
					<strong><?php esc_html_e( 'Palette', 'dynamic-fashion' ); ?></strong>
					<span><?php esc_html_e( 'Midnight, champagne, ivory', 'dynamic-fashion' ); ?></span>
				</div>
				<div class="vl-stat-card">
					<strong><?php esc_html_e( 'Browse', 'dynamic-fashion' ); ?></strong>
					<span><?php esc_html_e( 'Collections to product detail', 'dynamic-fashion' ); ?></span>
				</div>
			</div>
		</div>
	</div>
</section>

<?php dft_render_ad( 'below_hero' ); ?>

<section class="vl-section">
	<div class="vl-container">
		<div class="vl-section__head">
			<div>
				<h2><?php esc_html_e( 'Featured categories', 'dynamic-fashion' ); ?></h2>
				<p><?php esc_html_e( 'Shop by collection across fashion, luxury, slides, and seasonal offers.', 'dynamic-fashion' ); ?></p>
			</div>
		</div>
		<div class="vl-category-grid">
			<?php
			$wanted_cats = array( 'fashion', 'trending-fashion', 'luxury-collection', 'sale-and-offers' );
			$terms       = get_terms(
				array(
					'taxonomy'   => 'product_category',
					'hide_empty' => false,
					'slug'       => $wanted_cats,
				)
			);
			if ( ! is_wp_error( $terms ) && $terms ) :
				$by_slug = array();
				foreach ( $terms as $term ) {
					$by_slug[ $term->slug ] = $term;
				}
				foreach ( $wanted_cats as $slug ) :
					if ( empty( $by_slug[ $slug ] ) ) {
						continue;
					}
					$term = $by_slug[ $slug ];
					?>
					<a class="vl-category-card" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
						<h3><?php echo esc_html( $term->name ); ?></h3>
						<span><?php echo esc_html( sprintf( _n( '%s look', '%s looks', (int) $term->count, 'dynamic-fashion' ), number_format_i18n( $term->count ) ) ); ?></span>
					</a>
					<?php
				endforeach;
			else :
				?>
				<div class="vl-empty"><?php esc_html_e( 'Categories will appear after seeding content.', 'dynamic-fashion' ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php
$sections = array(
	array(
		'title' => __( 'New arrivals', 'dynamic-fashion' ),
		'text'  => __( 'Fresh edits ready for your next look.', 'dynamic-fashion' ),
		'args'  => array( 'meta_flag' => '_vl_is_new', 'posts_per_page' => 4 ),
	),
	array(
		'title' => __( 'Trending now', 'dynamic-fashion' ),
		'text'  => __( 'Styles gaining attention this season.', 'dynamic-fashion' ),
		'args'  => array( 'meta_flag' => '_vl_is_trending', 'posts_per_page' => 4 ),
	),
	array(
		'title' => __( 'Luxury collection', 'dynamic-fashion' ),
		'text'  => __( 'Refined pieces for elevated occasions.', 'dynamic-fashion' ),
		'args'  => array( 'meta_flag' => '_vl_is_luxury', 'posts_per_page' => 4 ),
	),
	array(
		'title' => __( 'Sale and offers', 'dynamic-fashion' ),
		'text'  => __( 'Selected looks with promotional pricing.', 'dynamic-fashion' ),
		'args'  => array( 'meta_flag' => '_vl_is_on_sale', 'posts_per_page' => 4 ),
	),
);

foreach ( $sections as $section ) :
	$query = function_exists( 'vl_query_products' ) ? vl_query_products( $section['args'] ) : new WP_Query( array( 'post_type' => 'fashion_product', 'posts_per_page' => 4 ) );
	?>
	<section class="vl-section">
		<div class="vl-container">
			<div class="vl-section__head">
				<div>
					<h2><?php echo esc_html( $section['title'] ); ?></h2>
					<p><?php echo esc_html( $section['text'] ); ?></p>
				</div>
			</div>
			<?php if ( $query->have_posts() ) : ?>
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
			<?php else : ?>
				<div class="vl-empty"><?php esc_html_e( 'No products in this section yet. Add products in admin and set homepage flags.', 'dynamic-fashion' ); ?></div>
			<?php endif; ?>
		</div>
	</section>
	<?php
endforeach;
?>

<section class="vl-section">
	<div class="vl-container">
		<div class="vl-section__head">
			<div>
				<h2><?php esc_html_e( 'Fashion guides', 'dynamic-fashion' ); ?></h2>
				<p><?php esc_html_e( 'Editorial ideas for festive capsules, slides, and wearable luxury.', 'dynamic-fashion' ); ?></p>
			</div>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'fashion_article' ) ); ?>"><?php esc_html_e( 'View all', 'dynamic-fashion' ); ?></a>
		</div>
		<div class="vl-guide-grid">
			<?php
			$guides = new WP_Query(
				array(
					'post_type'      => 'fashion_article',
					'posts_per_page' => 3,
				)
			);
			if ( $guides->have_posts() ) :
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
			else :
				?>
				<div class="vl-empty"><?php esc_html_e( 'Fashion guides will appear here.', 'dynamic-fashion' ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="vl-section">
	<div class="vl-container">
		<div class="vl-newsletter">
			<div>
				<h2><?php echo esc_html( $settings['newsletter_title'] ); ?></h2>
				<p><?php echo esc_html( $settings['newsletter_text'] ); ?></p>
			</div>
			<div class="vl-newsletter__note">
				<?php esc_html_e( 'Connect your preferred newsletter form or CRM later. This section is ready for integration.', 'dynamic-fashion' ); ?>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
