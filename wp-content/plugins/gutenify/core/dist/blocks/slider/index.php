<?php
/**
 * Plugin Name: Gutenify Slider
 * Plugin URI: https://gutenify.com
 * Description: Slider block.
 * Version: 1.0.0
 * Author: Gutenify
 *
 * @package gutenify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load all translations for our plugin from the MO file.
 */
function gutenify_load_textdomain_block_slider() {
	load_plugin_textdomain( 'gutenify', false, basename( __DIR__ ) . '/languages' );
}
add_action( 'init', 'gutenify_load_textdomain_block_slider' );

function gutenify_render_slider_block( $attributes, $content, $props ) {
	$constants = \gutenify\Helpers::plugin_constants();
	$plugin_main_slug = $constants['plugin_main_slug'];
	$plugin_main_camel_case_name = $constants['plugin_main_camel_case_name'];

	$client_id   = ! empty( $attributes['blockClientId'] ) ? $attributes['blockClientId'] : '';
	$class_name  = array();
	$class       = array( 'swiper-container', 'gutenify-slider' );
	$styles      = array();
	$block_style = ! empty( $attributes['className'] ) && strpos( $attributes['className'], 'is-style-stacked' ) !== false ? 'stacked' : 'horizontal';

	$slider_var  = 'slider_' . str_replace( '-', '_', $client_id );

	wp_localize_script( 'gutenify-common-scripts-slider', $slider_var, $attributes );
	wp_add_inline_script( 'gutenify-common-scripts-slider', 'document.addEventListener("DOMContentLoaded", function(event) { window.' . $plugin_main_camel_case_name . 'SliderInit( "' . $client_id  . '" ) }) ' );

	$layout = ! empty( $attributes['blockAdvanceOptions']['layout'] ) ? $attributes['blockAdvanceOptions']['layout'] : 'layout-1';
	array_push( $class_name, 'wp-block-gutenify-slider', 'gutenify-section-' . $attributes['blockClientId'], 'gutenify-section-' . $layout,
	'swiper-container' );

	if ( isset( $attributes['className'] ) ) {
		array_push( $class_name, $attributes['className'] );
	}

	if ( isset( $attributes['align'] ) ) {
		array_push( $class_name, 'align' . $attributes['align'] );
	}

	if ( isset( $attributes['columns'] ) ) {
		array_push( $class, 'has-columns has-' . $attributes['columns'] . '-columns has-responsive-columns' );
	}

	if ( isset( $attributes['listPosition'] ) && 'right' === $attributes['listPosition'] && 'horizontal' === $block_style ) {
		array_push( $class, 'has-image-right' );
	}

	if ( isset( $attributes['imageSize'] ) && 'horizontal' === $block_style ) {
		array_push( $class, 'has-' . $attributes['imageSize'] . '-image' );
	}

	if ( isset( $attributes['imageStyle'] ) ) {
		array_push( $class, 'has-' . $attributes['imageStyle'] . '-image' );
	}

	$class_name = apply_filters( 'gutenify--slider--wrapper-class', $class_name, $props );
	$block_content = sprintf(
		'<div class="%1$s"> <div class="swiper-wrapper">',
		esc_attr( implode( ' ', $class_name ) ),
		// esc_attr( implode( ' ', apply_filters( 'gutenify_render_wrapper_class', $class, $attributes ) ) ),
		esc_attr( implode( ' ', apply_filters( 'gutenify_render_wrapper_styles', $styles, $attributes ) ) )
	);

	$list_items_markup = '';

	$list_items_markup = $content;

	$block_content .= $list_items_markup;
	$block_content .= '</div>';
	$has_navigation = ! empty( $attributes['hasNavigation'] ) && true === $attributes['hasNavigation'];
	$has_pagination = ! empty( $attributes['hasPagination'] ) && true === $attributes['hasPagination'];
	if ( $has_navigation ) {
		$block_content .= '<div class="swiper-button-next"></div>
		<div class="swiper-button-prev"></div>';
	}
	if ( $has_pagination ) {
		$block_content .= '<div class="swiper-pagination"></div>';
	}
	$block_content .= '</div>';
	$block_content .= '</div>';
	// $block_content .= '</div>';


	return $block_content;
}

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * Passes translations to JavaScript.
 */
function gutenify_register_block_slider() {

	// Register the block by passing the location of block.json to register_block_type.
	register_block_type( __DIR__,  array(
		'render_callback' => 'gutenify_render_slider_block',
	) );

	if ( function_exists( 'wp_set_script_translations' ) ) {
		/**
		 * May be extended to wp_set_script_translations( 'my-handle', 'my-domain',
		 * plugin_dir_path( MY_PLUGIN ) . 'languages' ) ). For details see
		 * https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
		 */
		wp_set_script_translations( 'gutenify-block-slider', 'gutenify' );
	}
}
add_action( 'init', 'gutenify_register_block_slider' );
