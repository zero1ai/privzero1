<?php
/**
 * Plugin Name: Gutenify Advance Slider
 * Plugin URI: https://gutenify.com
 * Description: Advance slider block.
 * Version: 1.0.0
 * Author: Gutenify
 *
 * @package gutenify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load all translations for our plugin from the MO file.
 */
function gutenify_load_textdomain_block_advance_slider() {
	load_plugin_textdomain( 'gutenify', false, basename( __DIR__ ) . '/languages' );
}
add_action( 'init', 'gutenify_load_textdomain_block_advance_slider' );

function gutenify_render_advance_slider( $attributes, $content, $props ) {
	$constants = \gutenify\Helpers::plugin_constants();
	$plugin_main_slug = $constants['plugin_main_slug'];
	$plugin_main_camel_case_name = $constants['plugin_main_camel_case_name'];

	// wp_enqueue_script( 'gutenify-swiper' );

	$client_id   = ! empty( $attributes['blockClientId'] ) ? $attributes['blockClientId'] : '';
	$slider_var  = 'slider_' . str_replace( '-', '_', $client_id );

	//
	wp_localize_script( 'gutenify-common-scripts-slider', $slider_var, $attributes );
	wp_add_inline_script( 'gutenify-common-scripts-slider', 'document.addEventListener("DOMContentLoaded", function(event) { window.' . $plugin_main_camel_case_name . 'SliderInit( "' . $client_id  . '" ) }) ' );

	$has_navigation = ! empty( $attributes['blockAdvanceOptions']['hasNavigation'] ) && true === $attributes['blockAdvanceOptions']['hasNavigation'];
	$has_pagination = ! empty( $attributes['blockAdvanceOptions']['hasPagination'] ) && true === $attributes['blockAdvanceOptions']['hasPagination'];


	$class_name  = array();

	$layout = ! empty( $attributes['blockAdvanceOptions']['layout'] ) ? $attributes['blockAdvanceOptions']['layout'] : 'layout-1';
	array_push( $class_name, 'wp-block-gutenify-advance-slider', 'gutenify-advance-slider', 'gutenify-section-' . $attributes['blockClientId'], 'gutenify-section-' . $layout, 'gutenify-advance-slider-version-2', );

	if ( isset( $attributes['className'] ) ) {
		array_push( $class_name, $attributes['className'] );
	}

	if ( isset( $attributes['align'] ) ) {
		array_push( $class_name, 'align' . $attributes['align'] );
	}

	$class_name = apply_filters( 'gutenify--slider--wrapper-class', $class_name, $props );
	$block_content = sprintf(
		'<div class="%1$s">',
		esc_attr( implode( ' ', $class_name ) ),
	);

	$block_content .= '<div class="swiper-wrapper">';

	$block_content .= $content;
	$block_content .= '</div>';
	if ( $has_navigation ) {
		$block_content .= '<div class="swiper-button-next"></div>
		<div class="swiper-button-prev"></div>';
	}
	if ( $has_pagination ) {
		$block_content .= '<div class="swiper-pagination"></div>';
	}
	$block_content .= '</div>';


	return $block_content;
}

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * Passes translations to JavaScript.
 */
function gutenify_register_block_advance_slider() {

	// Register the block by passing the location of block.json to register_block_type.
	register_block_type( __DIR__, array(
		'render_callback' => 'gutenify_render_advance_slider'
	) );

	if ( function_exists( 'wp_set_script_translations' ) ) {
		/**
		 * May be extended to wp_set_script_translations( 'my-handle', 'my-domain',
		 * plugin_dir_path( MY_PLUGIN ) . 'languages' ) ). For details see
		 * https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
		 */
		wp_set_script_translations( 'gutenify-block-advance-slider', 'gutenify' );
	}
}
add_action( 'init', 'gutenify_register_block_advance_slider' );
