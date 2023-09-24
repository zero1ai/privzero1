<?php
/**
 * Plugin Name: Gutenify Gallery Carousel
 * Plugin URI: https://gutenify.com
 * Description: Gallery Carousel block.
 * Version: 1.0.0
 * Author: Gutenify
 *
 * @package gutenify
 */

defined( 'ABSPATH' ) || exit;


/**
 * Renders the list and grid styles.
 *
 * @param array $attributes The block attributes.
 *
 * @return string Returns the block content for the list and grid styles.
 */
function gutenify_render_gallery_carousel( $attributes, $context, $props ) {
	$constants = \gutenify\Helpers::plugin_constants();
	$plugin_main_slug = $constants['plugin_main_slug'];
	$plugin_main_camel_case_name = $constants['plugin_main_camel_case_name'];

	$client_id   = ! empty( $attributes['blockClientId'] ) ? $attributes['blockClientId'] : '';
	$class_name  = array();
	$class       = array( 'swiper-container', 'gutenify-gallery-carousel' );
	$styles      = array();
	$block_style = ! empty( $attributes['className'] ) && strpos( $attributes['className'], 'is-style-stacked' ) !== false ? 'stacked' : 'horizontal';

	$gallery_carousel_var  = 'slider_' . str_replace( '-', '_', $client_id );

	wp_localize_script( 'gutenify-common-scripts-slider', $gallery_carousel_var, $attributes );
	wp_add_inline_script( 'gutenify-common-scripts-slider', 'document.addEventListener("DOMContentLoaded", function(event) { window.' . $plugin_main_camel_case_name . 'SliderInit( "' . $client_id  . '" ) }) ' );

	array_push( $class_name, 'wp-block-gutenify-gallery-carousel', 'gutenify-section-' . $attributes['blockClientId'], 'gutenify--gallery-carousel--' . $attributes['layout'], 'gutenify-gallery-carousel-version-2', 'swiper-container' );

	if ( isset( $attributes['className'] ) ) {
		array_push( $class_name, $attributes['className'] );
	}

	if ( isset( $attributes['align'] ) ) {
		array_push( $class_name, 'align' . $attributes['align'] );
	}

	$class_name    = apply_filters( 'gutenify--gallery-carousel--wrapper-class', $class_name, $props );
	$block_content = sprintf(
		'<div class="%1$s"><div class="swiper-wrapper">',
		esc_attr( implode( ' ', $class_name ) ),
	);

	$list_items_markup = '';

	if ( ! empty( $attributes['images'] ) ) {
		foreach ( $attributes['images'] as $image ) {
			$title              = ! empty( $image['id'] ) && ! empty( $attributes['imagesData'][ $image['id'] ]['title'] ) ? $attributes['imagesData'][ $image['id'] ]['title'] : '';
			$sub_title          = ! empty( $image['id'] ) && ! empty( $attributes['imagesData'][ $image['id'] ]['subTitle'] ) ? $attributes['imagesData'][ $image['id'] ]['subTitle'] : '';
			$description        = ! empty( $image['id'] ) && ! empty( $attributes['imagesData'][ $image['id'] ]['description'] ) ? $attributes['imagesData'][ $image['id'] ]['description'] : '';
			$button_text        = ! empty( $image['id'] ) && ! empty( $attributes['imagesData'][ $image['id'] ]['buttonText'] ) ? $attributes['imagesData'][ $image['id'] ]['buttonText'] : '';

			$list_items_markup .= '<div class="gutenify-gallery-carousel-item swiper-slide">';
			if (  ! empty( $image['url'] ) ) {
				$list_items_markup .= '
					<div class="gutenify-slider-image-wrapper">
						<div class="gutenify-slider-content-image featured-image">
							<img src="' . ( $image['url'] ) . '" />
						</div>
					</div>';
			}
			$list_items_markup .='
				<div class="gutenify-slider-content-wrapper">
				<div class="gutenify-slider-content-inner">';
			if ( ! empty( $title ) ) {
				$list_items_markup .= '<h2 class="gutenify-slider-title">' . $title . '</h2>';
			}
			if ( ! empty( $sub_title ) ) {
				$list_items_markup .= '<h2 class="gutenify-slider-sub-title">' . $sub_title . '</h2>';
			}

				$list_items_markup .= '<div class="gutenify-slider-content-inner-wrapper">';
			if ( ! empty( $description ) ) {
				$list_items_markup .= '<div class="gutenify-slider-content clear-fix">
							<p>' . $description . '</p>
						</div>';
			}
			// if ( ! empty( $button_text ) ) {
			// $list_items_markup .= '<p class="gutenify-slider-buttons clear-fix gutenify-button-link wp-block-button__link">
			// ' . $button_text . '
			// </p>';
			// }
				$list_items_markup .= '</div>';
				$list_items_markup .= '</div>
			</div></div>';
		}
	}

	$block_content .= $list_items_markup;
	$block_content .= '</div>';
	$has_navigation = ! empty( $attributes['hasNavigation'] ) && true === $attributes['hasNavigation'];
	$has_pagination = ! empty( $attributes['hasPagination'] ) && true === $attributes['hasPagination'];
	if ( $has_pagination ) {
		$block_content .= '<div class="swiper-pagination"></div>';
	}
	if ( $has_navigation ) {
		// $block_content .= '<div class="swiper-button-next"></div>
		// <div class="swiper-button-prev"></div>';
		$block_content .= '<div class="navigation-wrap">
			<div class="swiper-button-prev"></div>
			<div class="swiper-button-next"></div>
		</div>';
	}
	$block_content .= '</div>';

	return $block_content;

}

/**
 * Load all translations for our plugin from the MO file.
 */
function gutenify_load_textdomain_block_gallery_carousel() {
	load_plugin_textdomain( 'gutenify', false, basename( __DIR__ ) . '/languages' );
}
add_action( 'init', 'gutenify_load_textdomain_block_gallery_carousel' );

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * Passes translations to JavaScript.
 */
function gutenify_register_block_gallery_carousel() {

	// Register the block by passing the location of block.json to register_block_type.
	register_block_type( __DIR__, array(
		'render_callback' => 'gutenify_render_gallery_carousel'
	) );

	if ( function_exists( 'wp_set_script_translations' ) ) {
		/**
		 * May be extended to wp_set_script_translations( 'my-handle', 'my-domain',
		 * plugin_dir_path( MY_PLUGIN ) . 'languages' ) ). For details see
		 * https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
		 */
		wp_set_script_translations( 'gutenify-block-gallery-carousel', 'gutenify' );
	}
}
add_action( 'init', 'gutenify_register_block_gallery_carousel' );
