<?php
function gutenify_block_custom_css( $block_content, $block ) {
	if ( ! empty( $block['attrs']['customCss'] ) && ! empty( $block['attrs']['blockClientId'] ) ) {
		$pattern = '/\$selector/m';
		$replacement = '.gutenify-section-' . $block['attrs']['blockClientId'];
		$custom_css = preg_replace($pattern, $replacement,  $block['attrs']['customCss'] );
		wp_add_inline_style( 'wp-block-library', $custom_css );
	}

	return $block_content;
}

add_filter( 'render_block', 'gutenify_block_custom_css', 10, 2 );
