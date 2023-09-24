<?php
namespace gutenify;
/**
 * Load assets for our blocks.
 *
 * @package Gutenify
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load general assets for our blocks.
 *
 * @since 1.0.0
 */
class Block_Assets {

	/**
	 * The Constructor.
	 */
	public static function init() {
		add_action( 'block_type_metadata', array( __CLASS__, 'block_type_metadata' ) );
		add_action( 'init', array( __CLASS__, 'register_block_assets' ) );
	}

	public static function register_block_assets() {
		$active_blocks = Helpers::active_blocks();
		$plugin_constants = Helpers::plugin_constants();
		$plugin_main_slug = $plugin_constants['plugin_main_slug'];
		$base_url =  Helpers::core_base_url();
		$base_dir =  Helpers::core_base_dir();

		foreach( $active_blocks as $block_name ) {

			$asset_file = Helpers::asset_file_values( sprintf( '%s' . 'dist/blocks/' . $block_name . '/index.asset.php', $base_dir ) );

			$handle = $plugin_main_slug . '--' . $block_name;
			wp_register_script( $handle, $base_url . 'dist/blocks/' . $block_name . '/index.js', $asset_file['dependencies'], $asset_file['version'], true );

			if ( file_exists( sprintf( '%s' . 'dist/blocks/' . $block_name . '/index.css', $base_dir ) ) ) {
				wp_register_style( $handle . '--editor', $base_url . 'dist/blocks/' . $block_name . '/index.css', array(), $asset_file['version'] );
			}

			if ( file_exists( sprintf( '%s' . 'dist/blocks/' . $block_name . '/style-index.css', $base_dir ) ) ) {
				wp_register_style( $handle . '--frontend', $base_url . 'dist/blocks/' . $block_name . '/style-index.css', array(), $asset_file['version'] );
			}

			if ( file_exists( sprintf( '%s' . 'dist/blocks/' . $block_name . '/scripts/script.js', $base_dir ) ) ) {
				$asset_file = Helpers::asset_file_values( sprintf( '%s' . 'dist/blocks/' . $block_name . '/scripts/script.asset.php', $base_dir ) );
				wp_register_script( $handle . '--view-script', $base_url . 'dist/blocks/' . $block_name . '/scripts/script.js', $asset_file['dependencies'], $asset_file['version'], true );
			}
		}
	}

	public static function block_type_metadata( $metadata ) {
		$constants = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];

		$metadata['script'] = ! empty( $metadata['script'] ) ? $metadata['script'] : array( );
		$metadata['script'] = is_array( $metadata['script'] ) ? $metadata['script'] : array( $metadata['script'] );

		$metadata['script'][] = $plugin_main_slug . '-global-inline-handle';
		// $metadata['script'][] = $plugin_main_slug . '-frontend';

		$metadata['editorScript'] = ! empty( $metadata['editorScript'] ) ? $metadata['editorScript'] : array( );
		$metadata['editorScript'] = is_array( $metadata['editorScript'] ) ? $metadata['editorScript'] : array( $metadata['editorScript'] );
		$metadata['editorScript'][] = $plugin_main_slug . '-extend-block-inspector-controls';
		$metadata['editorScript'][] = $plugin_main_slug . '-extend-block-dynamic-css';
		$metadata['editorScript'][] = $plugin_main_slug . '-extend-block-custom-attributes';
		$metadata['editorScript'][] = $plugin_main_slug . '-extend-block-custom-classname';
		$metadata['editorScript'][] = $plugin_main_slug . '-extend-block-spacing';
		$metadata['editorScript'][] = $plugin_main_slug . '-extend-block-pro-notice';
		$metadata['editorScript'][] = $plugin_main_slug . '-admin-global';

		if ( gutenify_is_pro_active() ) {
			$metadata['editorScript'][] = $plugin_main_slug . '-extend-block-custom-css';
		}

		$metadata['style'] = ! empty( $metadata['style'] ) ? $metadata['style'] : array( );
		$metadata['style'] = is_array( $metadata['style'] ) ? $metadata['style'] : array( $metadata['style'] );

		// $metadata['style'][] = 'frontend-animation';

		$metadata['editorStyle'] = ! empty( $metadata['editorStyle'] ) ? $metadata['editorStyle'] : array( );
		$metadata['editorStyle'] = is_array( $metadata['editorStyle'] ) ? $metadata['editorStyle'] : array( $metadata['editorStyle'] );
		$metadata['editorStyle'][] = $plugin_main_slug . '-admin-global';
		$metadata['editorStyle'][] = $plugin_main_slug . '-fontawesome';
		$metadata['editorStyle'][] = $plugin_main_slug . '-extend-block-pro-notice';
		$metadata['editorStyle'][] = $plugin_main_slug . '-fonts';

		// if('core/group' === $metadata['name']) {
		// 	$metadata['editorScript'][] = $plugin_main_slug . '-extend-block-core-group';
		// }


		return $metadata;
	}
}

Block_Assets::init();
