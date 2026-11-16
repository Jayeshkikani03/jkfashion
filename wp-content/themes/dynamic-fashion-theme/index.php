<?php
/**
 * Main index fallback.
 *
 * @package Dynamic_Fashion_Theme
 */

get_header();
?>

<div class="vl-container" style="padding:2rem 0;">
	<?php if ( have_posts() ) : ?>
		<div class="vl-guide-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'vl-guide-card' ); ?>>
					<div class="vl-guide-card__body">
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt() ? get_the_excerpt() : get_the_content(), 24 ) ); ?></p>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<div class="vl-pagination"><?php the_posts_pagination(); ?></div>
	<?php else : ?>
		<div class="vl-empty"><?php esc_html_e( 'Nothing found.', 'dynamic-fashion' ); ?></div>
	<?php endif; ?>
</div>

<?php
get_footer();
