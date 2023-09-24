<?php

/**
 * Server-side rendering of the `posts` block.
 *
 * @package WordPress
 */

function gutenify_wc_get_add_to_cart( $product ) {
	$attributes = array(
		'aria-label'       => $product->add_to_cart_description(),
		'data-quantity'    => '1',
		'data-product_id'  => $product->get_id(),
		'data-product_sku' => $product->get_sku(),
		'rel'              => 'nofollow',
		'class'            => 'wp-block-button__link add_to_cart_button',
	);

	if (
		$product->supports( 'ajax_add_to_cart' ) &&
		$product->is_purchasable() &&
		( $product->is_in_stock() || $product->backorders_allowed() )
	) {
		$attributes['class'] .= ' ajax_add_to_cart';
	}

	return sprintf(
		'<a href="%s" %s>%s</a>',
		esc_url( $product->add_to_cart_url() ),
		wc_implode_html_attributes( $attributes ),
		esc_html( $product->add_to_cart_text() )
	);
}

function gutenify_get_wc_product_item( $product_id ) {
		$product = wc_get_product( $product_id );
		$permalink = get_the_permalink( $product->get_id() );
		echo '<div class="gutenify--wc-product--item has-no-hover-shadow-dark">';
		echo '<div class="gutenify--wc-product--item-wrapper">';


		echo '<div class="gutenify--wc-product--thumb">';
		echo '<a class="image-zoom-hover" href="' . $permalink . '" tabindex="-1">';
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
		echo '</div>'; // Product individual wrapper
		echo '</div>'; // Product individual wrapper
		echo '</div>'; // Product individual wrapper
}
