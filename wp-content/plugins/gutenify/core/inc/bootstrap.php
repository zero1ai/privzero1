<?php
require 'class-helpers.php';
require 'helpers.php';
require 'typography-helpers.php';
require 'get-dynamic-blocks.php';
require 'class-block-inline-styles.php';
require 'class-block-assets.php';
require 'class-templates.php';
require 'class-rest.php';
require 'class-actions.php';
require 'class-admin-menu.php';
include 'styles.php';
include 'demo-importer.php';
include 'class-rest-demo-importer-v2.php';
include 'extend/custom-css.php';
include 'woocommerce-template-functions.php';
include 'class-assets.php';

// Blocks.
$active_blocks = \gutenify\Helpers::active_blocks();
$base_dir = \gutenify\Helpers::core_base_dir();

if ( ! empty( $active_blocks ) ) {
	foreach( $active_blocks as $block ) {
		$file = $base_dir . 'dist/blocks/' . $block. '/index.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
