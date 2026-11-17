<?php
/**
 * Page template.
 *
 * @package Dynamic_Fashion_Theme
 */

get_header();

$is_shop_flow = is_page( array( 'cart', 'checkout', 'thanks' ) );
?>

<div class="vl-container<?php echo is_page( 'thanks' ) ? ' vl-container--thanks' : ''; ?>" style="padding:2rem 0 2.5rem;">
	<?php
	if ( function_exists( 'vl_render_breadcrumbs' ) && ! is_page( array( 'cart', 'checkout', 'thanks' ) ) ) {
		vl_render_breadcrumbs();
	}

	while ( have_posts() ) :
		the_post();
		if ( $is_shop_flow ) :
			?>
			<div class="entry-content<?php echo is_page( 'thanks' ) ? ' entry-content--thanks' : ''; ?>">
				<?php the_content(); ?>
			</div>
			<?php
		else :
			?>
			<article <?php post_class( 'vl-article vl-surface-card' ); ?> style="padding:clamp(1.25rem,3vw,2.25rem);">
				<h1><?php the_title(); ?></h1>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
		endif;
	endwhile;
	?>
</div>

<?php
get_footer();
