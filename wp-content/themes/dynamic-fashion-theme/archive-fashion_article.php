<?php
/**
 * Fashion guides archive.
 *
 * @package Dynamic_Fashion_Theme
 */

get_header();
?>

<div class="vl-container" style="padding-top:1.5rem;padding-bottom:2.5rem;">
	<?php
	if ( function_exists( 'vl_render_breadcrumbs' ) ) {
		vl_render_breadcrumbs();
	}
	?>
	<header class="vl-archive-head">
		<h1><?php esc_html_e( 'Fashion Guides', 'dynamic-fashion' ); ?></h1>
		<p><?php esc_html_e( 'Editorial styling ideas and wardrobe guidance.', 'dynamic-fashion' ); ?></p>
	</header>

	<div class="vl-guide-grid">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
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
			<?php endwhile; ?>
		<?php else : ?>
			<div class="vl-empty"><?php esc_html_e( 'No guides published yet.', 'dynamic-fashion' ); ?></div>
		<?php endif; ?>
	</div>

	<div class="vl-pagination">
		<?php the_posts_pagination(); ?>
	</div>
</div>

<?php
get_footer();
