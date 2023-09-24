<?php
namespace gutenify;
/**
 * Actions functions
 *
 * @package Gutenify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Actions
 */
class Actions {

	/**
	 * Actions constructor.
	 */
	public function __construct() {
		add_filter( 'wp_theme_json_data_theme', array( $this, 'add_fonts_to_theme_json' ), 9 );
		// add_filter( 'wp_theme_json_data_default', array( $this, 'add_fonts_to_user_json1' ), 100 );
		add_action( 'use_block_editor_for_post_type', array( $this, 'disable_gutenberg' ), 10, 2 );
		add_filter( 'gutenify_skip_gutenburg_post_type', array( $this, 'skip_gutenburg_post_type' ) );

		add_action(
			'save_post',
			function( $post_id, $post ) {
				// If this is just a revision, don't send the email.
				if ( wp_is_post_revision( $post_id ) ) {
					return;
				}

				$data = json_decode( file_get_contents( 'php://input' ), true );
				// error_log( print_r( $data, true ) );
				if ( ! empty( $data['meta']['gutenify_custom_css'] ) ) {
					$name = 'post-' . $post_id;
					if ( 'wp_template_parts' === $post->post_type ) {
						$name = 'part-' . $post_id;
					}
					if ( $this->create_styles_folder() ) {
						$this->create_styles_file( $name, gutenify_minimize_css_simple( $data['meta']['gutenify_custom_css'] ) );
					}
				}
			},
			10,
			2
		);

		add_action( 'block_type_metadata', array( $this, 'extend_block_type_metadata' ) );
	}

	/**
	 * Disable gutenber on post type.
	 *
	 * @param boolean $is_enabled If editor is enabled.
	 * @param string  $post_type  Post type name.
	 * @return boolean
	 */
	public function disable_gutenberg( $is_enabled, $post_type ) {
		$skip_gutenburg = apply_filters( 'gutenify_skip_gutenburg_post_type', array() );
		if ( ! in_array( $post_type, $skip_gutenburg, true ) ) {
			$settings          = gutenify_settings();
			$active_post_types = ! empty( $settings['active_post_types'] ) ? $settings['active_post_types'] : array();
			if ( ! in_array( $post_type, $active_post_types, true ) ) {
				return false;
			}
		}
		return $is_enabled;
	}

	/**
	 * Post types to skip the options.
	 *
	 * @param array $post_types Post types.
	 * @return array
	 */
	public function skip_gutenburg_post_type( $post_types ) {
		$post_types = array_merge( $post_types, array( 'attachment', 'wp_template', 'wp_block', 'gutenify_template' ) );
		return $post_types;
	}

	public function create_styles_folder() {
		$uploads_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'gutenify/styles';
		if ( ! file_exists( $uploads_dir ) ) {
			wp_mkdir_p( $uploads_dir );
		}
		return true;
		// $fileLocation = $uploads_dir . '/myfile.txt';
		// $file         = fopen( $fileLocation, 'w' );
		// $content      = 'Your text here';
		// fwrite( $file, $content );
		// fclose( $file );
	}

	public function create_styles_file( $name, $content ) {
		$uploads_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'gutenify/styles';
		if ( file_exists( $uploads_dir ) ) {
			$fileLocation = $uploads_dir . '/style-' . $name . '.css';
			$file         = fopen( $fileLocation, 'w' );
			fwrite( $file, $content );
			fclose( $file );
		}
	}

	public function add_fonts_to_theme_json( $theme_json ) {

		$plugin_settings = get_option( 'gutenify_site_options' );

		$site_fonts = array();
		$new_theme_json_data = array(
			'version'  => 2,
		);
		if ( ! empty( $plugin_settings['styles']['siteFonts'] ) ) {
			$theme_json_data = $theme_json->get_data();
			$default_font_families = ! empty( $theme_json_data['settings']['typography']['fontFamilies']['theme'] )? $theme_json_data['settings']['typography']['fontFamilies']['theme']  : array();
			$default_font_families_slugs = wp_list_pluck( $default_font_families, 'slug' );
			// $WP_Theme_JSON = new \WP_Theme_JSON();
			// error_log( print_r( $WP_Theme_JSON->get_data(), true ));
			// $wp_theme        = wp_get_theme();
			// if ( $wp_theme->parent() ) {
			// 	// WP_Theme_JSON_Resolver::get_file_path_from_theme
			// 	$parent_theme_json_file = \WP_Theme_JSON_Resolver::get_file_path_from_theme( 'theme.json', true );
			// 	if ( '' !== $parent_theme_json_file ) {
			// 		$parent_theme_json_data = \WP_Theme_JSON_Resolver::read_json_file( $parent_theme_json_file );
			// 		$parent_theme_json_data = \WP_Theme_JSON_Resolver::translate( $parent_theme_json_data, $wp_theme->parent()->get( 'TextDomain' ) );
			// 		error_log( print_r( $parent_theme_json_data, true ));
			// 		// $parent_theme           = new WP_Theme_JSON( $parent_theme_json_data );

			// 		/*
			// 		* Merge the child theme.json into the parent theme.json.
			// 		* The child theme takes precedence over the parent.
			// 		*/
			// 		// $parent_theme->merge( static::$theme );
			// 		// static::$theme = $parent_theme;
			// 	}
			// }
			foreach(  $plugin_settings['styles']['siteFonts']  as $font ) {
				if ( ! in_array( $font['value'], $default_font_families_slugs, true ) ) {
					$site_fonts[] = array(
						'fontFamily' => $font['label'],
						'name' => $font['label'],
						'slug' => $font['value'],
					);
				}
			}
			$new_font_families = array_merge( $default_font_families, $site_fonts );
			$new_theme_json_data['settings']['typography']['fontFamilies'] = $new_font_families;
		}

		return $theme_json->update_with( $new_theme_json_data );
	}

	public function add_fonts_to_user_json( $theme_json ) {

		$plugin_settings = get_option( 'gutenify_site_options' );

		$site_fonts = array();
		$new_theme_json_data = array(
			'version'  => 2,
		);
		if ( ! empty( $plugin_settings['styles']['siteFonts'] ) ) {
			$theme_json_data = $theme_json->get_data();
			// error_log( print_r( $theme_json_data['settings'], true ) );
			error_log( print_r( \WP_Theme_JSON_Resolver::get_theme_data(), true ) );
			$default_font_families = ! empty( $theme_json_data['settings']['typography']['fontFamilies']['theme'] )? $theme_json_data['settings']['typography']['fontFamilies']['theme']  : array();
			$default_font_families_slugs = wp_list_pluck( $default_font_families, 'slug' );
			// error_log( print_r( $default_font_families_slugs, true  ));
			foreach(  $plugin_settings['styles']['siteFonts']  as $font ) {
				// if ( ! in_array( $font['value'], $default_font_families_slugs, true ) ) {
					$site_fonts[] = array(
						'fontFamily' => $font['label'],
						'name' => $font['label'],
						'slug' => $font['value'],
					);
				// }
			}
			$new_font_families = array_merge( $default_font_families, $site_fonts );
			$new_theme_json_data['settings']['typography']['fontFamilies'] = $new_font_families;
			// error_log( print_r( $new_theme_json_data, true  ));
			// $site_settings['typography']['fontFamilies']['theme'] = $new_font_families;

			// gutenify_update_global_styles( $site_settings, $site_styles );
			// if ( class_exists( 'WP_Theme_JSON_Resolver_Gutenberg' ) ) {
			// 	WP_Theme_JSON_Resolver_Gutenberg::clean_cached_data();
			// }
		}

		return $theme_json;
	}

	public function extend_block_type_metadata( $metadata ) {
		$constants = Helpers::plugin_constants();
		$plugin_main_camel_case_name = $constants['plugin_main_camel_case_name'];
		// Extend Attributes for blocks.
		if ( empty( $metadata['attributes']['customCss'] ) ) {
			$metadata['attributes']['customCss'] = array(
				'type' => 'string',
				'default' => ''
			);
		}

		if ( empty( $metadata['attributes']['blockClientId'] ) ) {
			$metadata['attributes']['blockClientId'] = array(
				'type' => 'string',
				'default' => ''
			);
		}

		if ( empty( $metadata['attributes'][ $plugin_main_camel_case_name. 'Styles' ] ) ) {
			$metadata['attributes'][ $plugin_main_camel_case_name. 'Styles'] = array(
				'type' => 'string',
				'default' => ''
			);
		}

		return $metadata;
	}
}
new Actions();
