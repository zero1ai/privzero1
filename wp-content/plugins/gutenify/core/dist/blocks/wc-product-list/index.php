<?php
/**
 * Plugin Name: Gutenify WooCommerce Product List
 * Plugin URI: https://gutenify.com
 * Description: WooCommerce Product List block.
 * Version: 1.0.0
 * Author: Gutenify
 *
 * @package gutenify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load all translations for our plugin from the MO file.
 */
function gutenify_load_textdomain_block_wc_product_list() {
	load_plugin_textdomain( 'gutenify', false, basename( __DIR__ ) . '/languages' );
}
add_action( 'init', 'gutenify_load_textdomain_block_wc_product_list' );

function gutenify_render_wc_product_list_block( $attributes ) {
	if ( ! class_exists( 'woocommerce' ) ) {
		return '';
	}
	$query = $attributes['query'];
	$args  = array(
		'post_status' => 'publish',
		'post_type' => 'product',
	);

	if ( ! empty( $query['numberOfItems'] ) ) {
		$args['posts_per_page'] = $query['numberOfItems'];
	}
	if ( ! empty( $query['orderBy'] ) ) {
		$args['orderby'] = $query['orderBy'];
	}
	if ( ! empty( $query['order'] ) ) {
		$args['order'] = strtoupper( $query['order'] );
	}


	if ( ! empty( $query['tax']['product_cat'] ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'id',
			'terms'    => $query['tax']['product_cat'],
		);
	}
	if ( ! empty( $query['tax']['product_tag'] ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'product_tag',
			'field'    => 'id',
			'terms'    => $query['tax']['product_tag'],
		);
	}
	$posts = new WP_Query( $args );

	ob_start();
	if ( $posts->have_posts() ) {
		$wrapper_class = array(
			'gutenify-section-' . $attributes['blockClientId'],
			'wp-block-gutenify-wc-product-list',
			'gutenify--wc-product-list-' . $attributes['layout'],
			'gutenify--wc-product-list-col-' . $attributes['columns'],
			'gutenify-wc-product-list-version-2',
		);
		if ( isset( $attributes['className'] ) ) {
			array_push( $wrapper_class, $attributes['className'] );
		}
		echo sprintf( '<div class="%s">', implode( ' ', $wrapper_class ) );
		while( $posts->have_posts() ) {
			$posts->the_post();
			gutenify_get_wc_product_item( get_the_ID() );
		}
		echo '</div>'; // Product lists wrapper
		wp_reset_postdata();
	}
	$result = ob_get_clean();
	return $result;
}


/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * Passes translations to JavaScript.
 */
function gutenify_register_block_wc_product_list() {

	// Register the block by passing the location of block.json to register_block_type.
	register_block_type( __DIR__, array(
		'render_callback' => 'gutenify_render_wc_product_list_block',
	) );

	if ( function_exists( 'wp_set_script_translations' ) ) {
		/**
		 * May be extended to wp_set_script_translations( 'my-handle', 'my-domain',
		 * plugin_dir_path( MY_PLUGIN ) . 'languages' ) ). For details see
		 * https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
		 */
		wp_set_script_translations( 'gutenify-block-wc-product-list', 'gutenify' );
	}
}
add_action( 'init', 'gutenify_register_block_wc_product_list' );
