<?php
/**
 * Plugin Name: Gutenify Post Carousel
 * Plugin URI: https://gutenify.com
 * Description: Post Carousel block.
 * Version: 1.0.0
 * Author: Gutenify
 *
 * @package gutenify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load all translations for our plugin from the MO file.
 */
function gutenify_load_textdomain_block_post_carousel() {
	load_plugin_textdomain( 'gutenify', false, basename( __DIR__ ) . '/languages' );
}
add_action( 'init', 'gutenify_load_textdomain_block_post_carousel' );

/**
 * Renders the block on server.
 *
 * @param array $attributes The block attributes.
 *
 * @return string Returns the block content.
 */
function gutenify_render_post_carousel_block( $attributes, $content, $props ) {

	global $post;
	$args = array(
		'posts_per_page'   => ! empty( $attributes['query']['numberOfItems'] ) ? $attributes['query']['numberOfItems'] : 10,
		'post_status'      => 'publish',
		'order'            => ! empty( $attributes['query']['order'] ) ? $attributes['query']['order'] : 'desc',
		'orderby'          => ! empty( $attributes['query']['orderBy'] ) ? $attributes['query']['orderBy'] : 'date',
		'suppress_filters' => false,
		'post__not_in'     => array( $post->ID ),
		'category__in'     => array(),
	);

	if ( isset( $attributes['query']['selectedCategories'] ) ) {

		$args['category__in'] = array_column( $attributes['query']['selectedCategories'], 'id' );

	}

	if ( ! empty( $attributes['query']['tax']['category'] ) ) {

		$args['category__in'] = array_merge( $attributes['query']['tax']['category'], $args['category__in'] );

	}

	if ( ! empty( $attributes['query']['tax']['tag'] ) ) {
		$args['tag__in'] = $attributes['query']['tax']['tag'];
	}

	if ( ! empty( $attributes['query']['tax']['post_tag'] ) ) {
		$args['tag__in'] = $attributes['query']['tax']['post_tag'];
	}

	if ( ! empty( $attributes['query']['authorIds'] ) ) {
		$args['author__in'] = $attributes['query']['authorIds'];
	}

	if ( ! empty( $attributes['query']['postTypes'] ) && in_array( 'attachment', $attributes['query']['postTypes'] ) ) {
		$args['post_status'] = 'inherit';

	}

	$recent_posts    = get_posts( $args );
	$formatted_posts = gutenify_get_post_info( $recent_posts );

	return gutenify_posts( $formatted_posts, $attributes, $props );
}

/**
 * Renders the list and grid styles.
 *
 * @param array $posts Current posts.
 * @param array $attributes The block attributes.
 *
 * @return string Returns the block content for the list and grid styles.
 */
function gutenify_posts( $posts, $attributes, $props ) {
	$constants = \gutenify\Helpers::plugin_constants();
	$plugin_main_slug = $constants['plugin_main_slug'];
	$plugin_main_camel_case_name = $constants['plugin_main_camel_case_name'];

	$client_id   = ! empty( $attributes['blockClientId'] ) ? $attributes['blockClientId'] : '';
	$class_name  = array( 'wp-block-gutenify-post-carousel', 'gutenify-section-' . $attributes['blockClientId'], 'gutenify-post-carousel-' . $attributes['layout'], 'gutenify-post-carousel-version-2', 'swiper-container' );
	$class       = array( 'swiper-container', 'gutenify-post-carousel' );
	$styles      = array();

	$slider_var  = 'slider_' . str_replace( '-', '_', $client_id );

	wp_localize_script( 'gutenify-common-scripts-slider', $slider_var, $attributes );
	wp_add_inline_script( 'gutenify-common-scripts-slider', 'document.addEventListener("DOMContentLoaded", function(event) { window.' . $plugin_main_camel_case_name . 'SliderInit( "' . $client_id  . '" ) }) ' );

	if ( isset( $attributes['className'] ) ) {
		array_push( $class_name, $attributes['className'] );
	}

	if ( isset( $attributes['align'] ) ) {
		array_push( $class_name, 'align' . $attributes['align'] );
	}

	$class_name = apply_filters( 'gutenify--post-carousel--wrapper-class', $class_name, $props );
	$class_name = apply_filters( 'gutenify_block_wrapper_class', esc_attr( implode( ' ', $class_name ) ), $attributes );

	$block_content = sprintf( '<div class="%1$s"><div class="swiper-wrapper">', $class_name );

	$list_items_markup = '';

	foreach ( $posts as $post ) {

		$list_class       = '';
		$align_self_class = '';

		$list_items_markup .= '<div class="gutenify-post-carousel-item gutenify-post-carousel-item-wrapper swiper-slide">';
		$list_items_markup .= '<div class="gutenify-post-carousel-item-inner-wrapper">';

		if ( ! empty( $attributes['displayFeaturedImage'] ) && null !== $post['thumbnailURL'] && $post['thumbnailURL'] ) {

			$list_items_markup .= sprintf(
				'<div class="gutenify-post-carousel-thumb">
				<a class="image-hover-zoom" href="%1$s">
				 	 <img src="%2$s" />
				 </a>
			</div>',
				esc_url( $post['postLink'] ),
				esc_url( $post['thumbnailURL'] )
			);


		}

		$list_items_markup .= sprintf(
			'<div class="gutenify-post-carousel-text-content %s">',
			esc_attr( $align_self_class )
		);

		// if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {

		// 	$list_items_markup .= sprintf(
		// 		'<time datetime="%1$s" class="wp-block-gutenify-posts__date">%2$s</time>',
		// 		esc_url( $post['date'] ),
		// 		esc_html( $post['dateReadable'] )
		// 	);

		// }

		$title = $post['title'];

		if ( ! $title ) {

			$title = _x( '(no title)', 'placeholder when a post has no title', 'gutenify' );

		}

		$list_items_markup .= sprintf(
			'<h3 class="gutenify-post-carousel-title"><a href="%1$s" rel="bookmark">%2$s</a></h3> ',
			$post['postLink'],
			$title
		);
		$meta_data          = '';
		if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
			$meta_data .= sprintf(
				'<span class="posted-on"><time datetime="%1$s" class="gutenify-posts-date">%2$s</time></span>',
				esc_html( $post['date'] ),
				esc_html( $post['dateReadable'] )
			);

		}
		if ( ! empty( $attributes['displayPostAuthor'] ) ) {
			if ( ! get_the_author_meta( 'description' ) && post_type_supports( get_post_type(), 'author' ) ) {
				$meta_data .= '<span class="byline">';
				$meta_data .= sprintf(
					'%s',
					'<a href="' . esc_url( get_author_posts_url( $post['author_id'] ) ) . '" rel="author">' . esc_html( get_the_author_meta( 'display_name', $post['author_id'] ) ) . '</a>'
				);
				$meta_data .= '</span>';
			}
		}

		if ( ! empty( $attributes['displayPostCategories'] ) ) {
			/* translators: used between list items, there is a space after the comma. */
			$categories_list = get_the_category_list( __( ', ' ), '', $post['ID'] );
			if ( $categories_list ) {
				$meta_data .= sprintf(
					'<span class="cat-links">%s </span>',
					$categories_list // phpcs:ignore WordPress.Security.EscapeOutput
				);
			}
		}

		if ( ! empty( $meta_data ) ) {
			$list_items_markup .= sprintf( '<div class="gutenify-post-carousel-meta">%1$s</div>', $meta_data );
		}

		if ( isset( $attributes['displayPostContent'] ) && $attributes['displayPostContent'] ) {

			$post_excerpt       = $post['postExcerpt'];
			$trimmed_excerpt    = esc_html( wp_trim_words( $post_excerpt, $attributes['excerptLength'], ' &hellip; ' ) );
			$list_items_markup .= '<div class="entry-summary">';
			$list_items_markup .= sprintf(
				'<p>%1$s</p>',
				esc_html( $trimmed_excerpt )
			);

			if ( isset( $attributes['displayPostLink'] ) && $attributes['displayPostLink'] ) {

				$list_items_markup .= sprintf(
					'<a href="%1$s" class="more-link"><span class="more-button">%2$s<span class="screen-reader-text">%3$s</span></span></a>',
					esc_url( $post['postLink'] ),
					esc_html( $attributes['postLink'] ),
					esc_html( $post['title'] )
				);

			}

			$list_items_markup .= '</div>';
		}

		$list_items_markup .= '</div></div></div>';

	}

	$block_content .= $list_items_markup;
	$block_content .= '</div>';
	$has_navigation = ! empty( $attributes['hasNavigation'] ) && true === $attributes['hasNavigation'];
	$has_pagination = ! empty( $attributes['hasPagination'] ) && true === $attributes['hasPagination'];
	if ( $has_navigation ) {
		$block_content .= '<div class="navigation-wrap">
			<div class="swiper-button-prev"></div>
			<div class="swiper-button-next"></div>
		</div>';
	}
	if ( $has_pagination ) {
		$block_content .= '<div class="swiper-pagination"></div>';
	}
	// $block_content .= '</div>';
	$block_content .= '</div>';

	return $block_content;

}

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * Passes translations to JavaScript.
 */
function gutenify_register_block_post_carousel() {

	// Register the block by passing the location of block.json to register_block_type.
	register_block_type( __DIR__, array(
		'render_callback' => 'gutenify_render_post_carousel_block'
	) );

	if ( function_exists( 'wp_set_script_translations' ) ) {
		/**
		 * May be extended to wp_set_script_translations( 'my-handle', 'my-domain',
		 * plugin_dir_path( MY_PLUGIN ) . 'languages' ) ). For details see
		 * https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
		 */
		wp_set_script_translations( 'gutenify-block-post-carousel', 'gutenify' );
	}
}
add_action( 'init', 'gutenify_register_block_post_carousel' );
