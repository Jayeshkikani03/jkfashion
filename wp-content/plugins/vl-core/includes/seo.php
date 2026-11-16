<?php
/**
 * Lightweight SEO helpers.
 *
 * @package VL_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output basic meta description.
 */
function vl_output_meta_description() {
	if ( is_admin() ) {
		return;
	}

	$settings    = function_exists( 'vl_get_homepage_settings' ) ? vl_get_homepage_settings() : array();
	$description = ! empty( $settings['meta_description'] ) ? $settings['meta_description'] : get_bloginfo( 'description' );

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 28 );
			if ( $excerpt ) {
				$description = $excerpt;
			}
		}
	} elseif ( is_tax() ) {
		$term = get_queried_object();
		if ( $term && ! empty( $term->description ) ) {
			$description = $term->description;
		} elseif ( $term ) {
			$description = sprintf(
				/* translators: 1: term name, 2: site name */
				__( 'Browse %1$s at %2$s.', 'vl-core' ),
				$term->name,
				get_bloginfo( 'name' )
			);
		}
	}

	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $description ) ) . '">' . "\n";
	}

	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( wp_get_document_title() ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( wp_strip_all_tags( $description ) ) . '">' . "\n";
	echo '<meta property="og:type" content="' . ( is_singular( 'fashion_product' ) ? 'product' : 'website' ) . '">' . "\n";
	$og_url = is_singular() ? get_permalink() : ( is_tax() ? get_term_link( get_queried_object() ) : home_url( '/' ) );
	if ( ! is_wp_error( $og_url ) ) {
		echo '<meta property="og:url" content="' . esc_url( $og_url ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'vl_output_meta_description', 1 );

/**
 * Improve document title parts for CPTs.
 *
 * @param array $parts Title parts.
 * @return array
 */
function vl_document_title_parts( $parts ) {
	if ( is_singular( 'fashion_product' ) ) {
		$parts['title'] = get_the_title();
	}
	return $parts;
}
add_filter( 'document_title_parts', 'vl_document_title_parts' );

/**
 * Breadcrumb markup helper.
 */
function vl_render_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$items   = array();
	$items[] = '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'vl-core' ) . '</a>';

	if ( is_tax( 'product_category' ) ) {
		$term    = get_queried_object();
		$items[] = esc_html( $term->name );
	} elseif ( is_singular( 'fashion_product' ) ) {
		$terms = get_the_terms( get_the_ID(), 'product_category' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$term    = array_shift( $terms );
			$items[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
		}
		$items[] = esc_html( get_the_title() );
	} elseif ( is_singular( 'fashion_article' ) ) {
		$items[] = '<a href="' . esc_url( get_post_type_archive_link( 'fashion_article' ) ) . '">' . esc_html__( 'Fashion Guides', 'vl-core' ) . '</a>';
		$items[] = esc_html( get_the_title() );
	} elseif ( is_post_type_archive( 'fashion_article' ) ) {
		$items[] = esc_html__( 'Fashion Guides', 'vl-core' );
	} elseif ( is_page() ) {
		$items[] = esc_html( get_the_title() );
	}

	echo '<nav class="vl-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'vl-core' ) . '"><ol class="vl-breadcrumbs__list">';
	foreach ( $items as $item ) {
		echo '<li class="vl-breadcrumbs__item">' . $item . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- items escaped above.
	}
	echo '</ol></nav>';
}
