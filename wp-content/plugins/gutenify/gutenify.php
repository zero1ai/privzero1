<?php
/**
 * Plugin Name: Gutenify - Visual Site Builder Blocks & Site Templates
 * Description: Gutenify is a World’s First True Gutenberg (Block) Template Kit pluigin that is compatible with WordPress Full Site Editing to help you create the website you always wanted. Gutenify is a free WordPress plugin which allows you to add different block effortlessly in your site.
 * Author: Gutenify
 * Author URI: https://www.gutenify.com
 * Plugin URI: https://www.gutenify.com
 * Version: 1.3.8
 * Text Domain: gutenify
 * Domain Path: /languages
 * Tested up to: 6.2
 * Requires at least: 6.1
 * Requires PHP: 5.5
 *
 * Gutenify is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with Gutenify. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Gutenify
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants.
define( 'GUTENIFY_VERSION', '1.3.8' );
define( 'GUTENIFY_BASE_DIR', plugin_dir_path( __FILE__ ) );
define( 'GUTENIFY_BASE_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'GUTENIFY_BASE_FILE', __FILE__ );
define(
	'GUTENIFY_BRAND_LOGO',
	'<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1080 1080" style="enable-background:new 0 0 1080 1080;" xml:space="preserve">
<style type="text/css">
  .st0 {
    fill: #FFFFFF;
  }

  .st1 {
    fill: #67BC45;
  }

</style>
<g>
  <g>
    <path class="st0" d="M828.5,552.9c-6.8,152.9-133.3,275.1-287.9,275.1c-158.9,0-288.2-129.3-288.2-288.2
		      c0-150.6,116.2-274.5,263.5-287.1V0.4C229.1,13.2,0.5,249.9,0.5,539.9c0,298.2,241.7,540.1,540.1,540.1
		      c293.9,0,533-234.8,539.8-527H828.5V552.9z" />
    <rect x="518.9" y="254.6" class="st1" width="309.8" height="298.2" />
  </g>
</g>
</svg>
'
);

function gutenify_add_plugin_constants( $args ) {
	$consts = gutenify_constants();
	return wp_parse_args( $consts, $args );
}
add_filter( 'gutenify_plugin_constants', 'gutenify_add_plugin_constants' );

function gutenify() {
	require 'core/inc/bootstrap.php';
	if ( function_exists( 'gutenify_pro' ) && version_compare( GUTENIFY_PRO_VERSION, '1.1.5', '>' ) ) {
		gutenify_pro();
	}
}

gutenify();

function gutenify_activation_redirect( $plugin ) {
	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		if ( ! empty( $screen->id ) && 'appearance_page_tgmpa-install-plugins' === $screen->id ) {
			return false;
		}
	}

	if ( $plugin == plugin_basename( __FILE__ ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=gutenify' ) );
		exit;
	}
}
// add_action( 'activated_plugin', 'gutenify_activation_redirect' );

function gutenify_constants() {
	$title = 'Gutenify';
	return array(
		'title'                        => $title,
		'prefix'                       => 'gutenify',
		'slug'                         => 'gutenify',
		'authorWebSite'                => 'https://gutenify.com',
		'authorDemoWebSite'            => 'https://demo.gutenify.com',
		'authorWebSiteProPage'         => 'https://gutenify.com/pricing',
		'authorWebSiteSupport'         => 'https://gutenify.com/product-support',
		'defaultTheme'                 => array(
			'slug'  => 'gutenify-base',
			'title' => 'Gutenify Base',
		),
		'plugin_main_slug'             => 'gutenify',
		'plugin_main_camel_case_name'  => 'gutenify',
		'plugin_main_function_prefix'  => 'gutenify',
		'plugin_main_base_url'         => trailingslashit( GUTENIFY_BASE_URL ),
		'plugin_main_base_dir'         => trailingslashit( GUTENIFY_BASE_DIR ),
		'plugin_main_version'          => GUTENIFY_VERSION,
		'plugin_main_post_type_prefix' => 'gutenify',
		'plugin_main_site_domain'      => 'gutenify.com',
		'core_base_dir'                => trailingslashit( GUTENIFY_BASE_DIR ) . 'core/',
		'core_base_url'                => trailingslashit( GUTENIFY_BASE_URL ) . 'core/',
		'brand_color'                => '#2196f3',

		// Pro vars.
		'pro_title' => $title . ' Pro',
	);
}
