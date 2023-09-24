<?php
/**
 * Plugin Name: Gutenify WooCommerce Product Carousel
 * Plugin URI: https://gutenify.com
 * Description: WooCommerce Product Carousel block.
 * Version: 1.0.0
 * Author: Gutenify
 *
 * @package gutenify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load all translations for our plugin from the MO file.
 */
function gutenify_load_textdomain_block_wc_product_carousel() {
	load_plugin_textdomain( 'gutenify', false, basename( __DIR__ ) . '/languages' );
}
add_action( 'init', 'gutenify_load_textdomain_block_wc_product_carousel' );

function gutenify_render_wc_product_carousel_block( $attributes, $content, $props ) {
	if ( ! class_exists( 'woocommerce' ) ) {
		return '';
	}

	$constants = \gutenify\Helpers::plugin_constants();
	$plugin_main_slug = $constants['plugin_main_slug'];
	$plugin_main_camel_case_name = $constants['plugin_main_camel_case_name'];

	$client_id   = ! empty( $attributes['blockClientId'] ) ? $attributes['blockClientId'] : '';

	$slider_var  = 'slider_' . str_replace( '-', '_', $client_id );

	wp_localize_script( 'gutenify-common-scripts-slider', $slider_var, $attributes );
	wp_add_inline_script( 'gutenify-common-scripts-slider', 'document.addEventListener("DOMContentLoaded", function(event) { window.' . $plugin_main_camel_case_name . 'SliderInit( "' . $client_id  . '" ) }) ' );

	$query = $attributes['query'];
	$args  = array(
		'post_status' => 'publish',
		'post_type'   => 'product',
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
	$has_navigation = ! empty( $attributes['hasNavigation'] ) && true === $attributes['hasNavigation'];
	$has_pagination = ! empty( $attributes['hasPagination'] ) && true === $attributes['hasPagination'];

	ob_start();
	if ( $posts->have_posts() ) {
		$wrapper_class = array(
			'gutenify-section-' . $attributes['blockClientId'],
			'wp-block-gutenify-wc-product-carousel',
			'gutenify--wc-product-carousel-' . $attributes['layout'],
			'gutenify--wc-product-carousel-col-' . $attributes['columns'],
			'gutenify-wc-product-carousel-version-2',
			'swiper-container'
		);
		if ( isset( $attributes['className'] ) ) {
			array_push( $wrapper_class, $attributes['className'] );
		}
		$wrapper_class = apply_filters( 'gutenify--slider--wrapper-class', $wrapper_class, $props );
		echo sprintf( '<div class="%s">', implode( ' ', $wrapper_class ) );
		// echo '<div class="swiper-container">';
		echo '<div class="swiper-wrapper">';
		while ( $posts->have_posts() ) {
			$posts->the_post();
			$product   = wc_get_product( get_the_ID() );
			$permalink = get_the_permalink( $product->get_id() );
			echo '<div class="gutenify--wc-product--item swiper-slide">';
			echo '<div class="gutenify--wc-product--item-wrapper">';

			echo '<div class="gutenify--wc-product--thumb">';
			echo '<a class="image-hover-zoom" href="' . $permalink . '" tabindex="-1">';
			echo woocommerce_get_product_thumbnail();
			echo '</a>'; // Product thumb link
			echo $product->is_on_sale() ? '<div class="gutenify--wc-product--onsale"><span aria-hidden="true">Sale</span><span class="screen-reader-text">Product on sale</span></div>' : '';
			echo '</div>'; // Product thumb


			echo '<div class="gutenify--wc-product--item-content">';
			echo '<h3 class="gutenify--wc-product--title">';
			echo '<a rel="bookmark" href="' . $permalink . '" tabindex="-1">';
			echo $product->get_name();
			echo '</a>'; // Product title link
			echo '</h3>'; // Product title

			echo '<div class="gutenify--wc-product--price">';
			echo $product->get_price_html();
			echo '</div>'; // Product price

			echo '<div class="wp-block-button wc-block-grid__product-add-to-cart">';
			echo gutenify_wc_get_add_to_cart( $product );
			// var_dump( $product );
			echo '</div>'; // Product add to cart wrapper
			echo '</div>'; // .gutenify--wc-product--item-content

			echo '</div>'; // Product individual wrapper
			echo '</div>'; // Product individual wrapper
		}

		echo '</div>'; // .swiper-wrapper
		if ( $has_navigation ) {
			echo '<div class="swiper-button-next"></div>';
			echo '<div class="swiper-button-prev"></div>';
		}
		if ( $has_pagination ) {
			echo '<div class="swiper-pagination"></div>';
		}
		// echo '</div>'; // .swiper-container
		echo '</div>'; // Product carousels wrapper
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
function gutenify_register_block_wc_product_carousel() {

	// Register the block by passing the location of block.json to register_block_type.
	register_block_type( __DIR__, array(
		'render_callback' => 'gutenify_render_wc_product_carousel_block',
	) );

	if ( function_exists( 'wp_set_script_translations' ) ) {
		/**
		 * May be extended to wp_set_script_translations( 'my-handle', 'my-domain',
		 * plugin_dir_path( MY_PLUGIN ) . 'languages' ) ). For details see
		 * https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
		 */
		wp_set_script_translations( 'gutenify-block-wc-product-carousel', 'gutenify' );
	}
}
add_action( 'init', 'gutenify_register_block_wc_product_carousel' );
