<?php
/**
 * Page template.
 *
 * @package Dynamic_Fashion_Theme
 */

get_header();
?>

<div class="vl-container" style="padding:2rem 0 2.5rem;">
	<?php
	if ( function_exists( 'vl_render_breadcrumbs' ) ) {
		vl_render_breadcrumbs();
	}

	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'vl-article vl-surface-card' ); ?> style="padding:clamp(1.25rem,3vw,2.25rem);">
			<h1><?php the_title(); ?></h1>
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</div>

<?php
get_footer();
