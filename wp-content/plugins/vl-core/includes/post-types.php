<?php
/**
 * Post types and taxonomies.
 *
 * @package VL_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register custom post types.
 */
function vl_register_post_types() {
	register_post_type(
		'fashion_product',
		array(
			'labels'              => array(
				'name'               => __( 'Products', 'vl-core' ),
				'singular_name'      => __( 'Product', 'vl-core' ),
				'add_new'            => __( 'Add Product', 'vl-core' ),
				'add_new_item'       => __( 'Add New Product', 'vl-core' ),
				'edit_item'          => __( 'Edit Product', 'vl-core' ),
				'new_item'           => __( 'New Product', 'vl-core' ),
				'view_item'          => __( 'View Product', 'vl-core' ),
				'search_items'       => __( 'Search Products', 'vl-core' ),
				'not_found'          => __( 'No products found', 'vl-core' ),
				'not_found_in_trash' => __( 'No products found in Trash', 'vl-core' ),
				'all_items'          => __( 'All Products', 'vl-core' ),
				'menu_name'          => __( 'Products', 'vl-core' ),
			),
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'product' ),
			'menu_icon'           => 'dashicons-products',
			'show_in_rest'        => true,
			'show_in_admin_bar'   => true,
			'supports'            => array( 'title', 'editor', 'thumbnail' ),
			'menu_position'       => 5,
		)
	);

	register_post_type(
		'fashion_article',
		array(
			'labels'              => array(
				'name'               => __( 'Fashion Guides', 'vl-core' ),
				'singular_name'      => __( 'Fashion Guide', 'vl-core' ),
				'add_new_item'       => __( 'Add New Fashion Guide', 'vl-core' ),
				'edit_item'          => __( 'Edit Fashion Guide', 'vl-core' ),
				'menu_name'          => __( 'Fashion Guides', 'vl-core' ),
			),
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'guides' ),
			'menu_icon'           => 'dashicons-book-alt',
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' ),
			'menu_position'       => 6,
		)
	);

	register_post_type(
		'vl_ad',
		array(
			'labels'              => array(
				'name'          => __( 'Advertisements', 'vl-core' ),
				'singular_name' => __( 'Advertisement', 'vl-core' ),
				'add_new_item'  => __( 'Add New Advertisement', 'vl-core' ),
				'edit_item'     => __( 'Edit Advertisement', 'vl-core' ),
				'menu_name'     => __( 'Advertisements', 'vl-core' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-megaphone',
			'show_in_rest'        => false,
			'supports'            => array( 'title' ),
			'menu_position'       => 7,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		)
	);
}

/**
 * Register taxonomies.
 */
function vl_register_taxonomies() {
	register_taxonomy(
		'product_category',
		array( 'fashion_product' ),
		array(
			'labels'            => array(
				'name'          => __( 'Product Categories', 'vl-core' ),
				'singular_name' => __( 'Product Category', 'vl-core' ),
				'search_items'  => __( 'Search Categories', 'vl-core' ),
				'all_items'     => __( 'All Categories', 'vl-core' ),
				'edit_item'     => __( 'Edit Category', 'vl-core' ),
				'update_item'   => __( 'Update Category', 'vl-core' ),
				'add_new_item'  => __( 'Add New Category', 'vl-core' ),
				'menu_name'     => __( 'Categories', 'vl-core' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'collection' ),
		)
	);

	register_taxonomy(
		'guide_topic',
		array( 'fashion_article' ),
		array(
			'labels'            => array(
				'name'          => __( 'Guide Topics', 'vl-core' ),
				'singular_name' => __( 'Guide Topic', 'vl-core' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'guide-topic' ),
		)
	);
}
