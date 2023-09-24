<?php
namespace gutenify;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rest_Demo_Importer_V2 {
	/**
	 * Class Init.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __class__, 'add_endpoint' ) );
	}

	/**
	 * Add API Endpoints.
	 *
	 * @return void
	 */
	public static function add_endpoint() {
		$constants = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];
		$plugin_main_function_prefix = $constants['plugin_main_function_prefix'];
		$plugin_main_base_url = $constants['plugin_main_base_url'];
		$plugin_main_version = $constants['plugin_main_version'];
		$plugin_main_post_type_prefix = $constants['plugin_main_post_type_prefix'];


		register_rest_route(
			$plugin_main_slug . '/demo-import/v1',
			'/import',
			array(
				'methods'             => 'POST',
				'callback'            => array( __class__, 'import' ),
				'permission_callback' => array( __class__, 'update_settings_permission' ),
			)
		);

		register_rest_route(
			$plugin_main_slug . '/demo-import/v1',
			'/demos',
			array(
				'methods'             => 'GET',
				'callback'            => array( __class__, 'get_demos' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$plugin_main_slug . '/demo-import/v1',
			'/demo-content',
			array(
				'methods'             => 'GET',
				'callback'            => array( __class__, 'get_demo_content' ),
				'permission_callback' => array( __class__, 'update_settings_permission' ),
			)
		);

		register_rest_route(
			$plugin_main_slug . '/demo-import/v1',
			'/activate-theme',
			array(
				'methods'             => 'POST',
				'callback'            => array( __class__, 'activate_theme' ),
				'permission_callback' => array( __class__, 'update_settings_permission' ),
			)
		);

		register_rest_route(
			$plugin_main_slug . '/demo-import/v1',
			'/verify-theme-installation',
			array(
				'methods'             => 'POST',
				'callback'            => array( __class__, 'verify_theme_installation' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Get edit options permissions.
	 *
	 * @return bool
	 */
	public static function update_settings_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		return true;
	}

	public static function import( \WP_REST_Request $req ) {
		$params        = (array) $req->get_params();
		$response_data = array();
		if ( ! empty( $params['type'] ) ) {
			if ( in_array( $params['type'], array( 'pages', 'posts', 'navigations', 'plugin_templates' ), true ) ) {
				$response = self::import_posts( $req );
				$response_data = ! empty( $params['data'] ) ? $response : array( 'success' => true );
			} elseif ( in_array( $params['type'], array( 'template_parts', 'templates' ), true ) ) {
				$response = self::import_templates( $req );
				$response_data = ! empty( $params['data'] ) ? $response : array( 'success' => true );
			} elseif ( 'plugin_settings' === $params['type'] ) {
				$response_data = self::import_plugin_settings( $req );
			} elseif ( 'set_home' === $params['type'] || 'wp_settings' === $params['type'] ) {
				$response_data = self::import_set_home( $req );
			} elseif ( 'global_styles_settings' === $params['type'] ) {
				$response_data = self::import_global_styles_settings( $req );
			} elseif ( 'global_styles' === $params['type'] ) {
				$response_data = self::import_global_styles( $req );
			}
		}
		if ( empty( $response_data ) ) {
			wp_send_json( array( 'message' => 'error' ), 404 );
		}
		wp_send_json( $response_data );
	}

	public static function import_posts( \WP_REST_Request $req ) {

		$constants = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];
		$plugin_main_post_type_prefix = $constants['plugin_main_post_type_prefix'];

		$types['posts']            = 'post';
		$types['pages']            = 'page';
		$types['navigations']      = 'wp_navigation';
		$types['plugin_templates'] = $plugin_main_post_type_prefix . '_template';

		$params        = (array) $req->get_params();
		$created_posts = array();
		if ( ! empty( $params['data'] ) ) {
			$demo_posts = $params['data'];
			foreach ( $demo_posts as $demo_post ) {
				$demo_post                         = (array) $demo_post;
				$demo_post['theme']                = ! empty( $params['theme'] ) ? $params['theme'] : '';
				$post_id                           = self::create_post( $demo_post, $types[ $params['type'] ] );
				$created_posts[ $demo_post['ID'] ] = ( ! is_wp_error( $post_id ) ) ? $post_id : false;
			}
		}
		return $created_posts;
	}

	private static function import_set_home( \WP_REST_Request $req ) {
		$params = (array) $req->get_params();
		if ( ! empty( $params['data']['page_on_front'] ) && ! empty( $params['importedData']['pages'][ $params['data']['page_on_front'] ] ) ) {
			$page_id = $params['importedData']['pages'][ $params['data']['page_on_front'] ];
			update_option( 'page_on_front', $page_id );
			update_option( 'show_on_front', 'page' );
			return true;
		}
		return false;
	}

	private static function import_plugin_settings( \WP_REST_Request $req ) {

		$constants = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];
		$plugin_main_function_prefix = $constants['plugin_main_function_prefix'];
		$plugin_main_post_type_prefix = $constants['plugin_main_post_type_prefix'];

		$params = (array) $req->get_params();
		if ( ! empty( $params['data']['plugin_settings'] ) ) {
			gutenify_update_settings( $params['data']['plugin_settings'] );
		}
		if ( ! empty( $params['data']['plugin_site_options'] ) ) {
			$options         = (array) $params['data']['plugin_site_options'];
			$current_options = get_option( $plugin_main_function_prefix . '_site_options', array() );
			$current_options = ! empty( $current_options ) ? $current_options : array();
			$merged_options  = array_merge( $current_options, $options );
			update_option( $plugin_main_function_prefix . '_site_options', $merged_options );
		}

		if ( ! empty( $params['data']['plugin_admin_global_style'] ) ) {
			update_option( $plugin_main_function_prefix . '_admin_global_style', $params['data']['plugin_admin_global_style'] );
		}

		if ( ! empty( $params['data']['plugin_global_style'] ) ) {
			update_option( $plugin_main_function_prefix . '_global_style', $params['data']['plugin_global_style'] );
		}
		return true;
	}

	public static function import_global_styles_settings( \WP_REST_Request $req ) {
		$post_id   = \WP_Theme_JSON_Resolver::get_user_global_styles_post_id();
		$post_data = false;
		$params = (array) $req->get_params();
		if ( ! empty( $params['data'] ) ) {
			$data = (array) json_decode( $params['data'], true );

			// Remove font families as its not required.
			if ( ! empty( $data['settings']['typography']['fontFamilies']['theme'] ) ) {
				unset( $data['settings']['typography']['fontFamilies']['theme']  );
			}
			gutenify_update_global_styles( $data['settings'], $data['styles'] );
		}

		return true;
	}

	public static function import_global_styles( \WP_REST_Request $req ) {
		$post_id      = \WP_Theme_JSON_Resolver::get_user_global_styles_post_id();
		$post_data    = get_post( $post_id, ARRAY_A );
		$post_content = ! empty( $post_data['post_content'] ) ? (array) json_decode( $post_data['post_content'] ) : array();
		$params       = (array) $req->get_params();
		// error_log( print_r( $params, true ) );
		if ( ! empty( $params['data'] ) ) {
			$post_content['styles'] = (array) $params['data'];
		}

		$my_post = array(
			'ID'           => $post_id,
			'post_content' => wp_json_encode( $post_content ),
		);

		  // Update the post into the database.
		$response = wp_update_post( $my_post );

		delete_transient( 'global_styles' );
		delete_transient( 'global_styles_' . get_stylesheet() );
		delete_transient( 'gutenberg_global_styles' );
		delete_transient( 'gutenberg_global_styles_' . get_stylesheet() );

		if ( class_exists( 'WP_Theme_JSON_Resolver_Gutenberg' ) ) {
			\WP_Theme_JSON_Resolver_Gutenberg::clean_cached_data();
		}

		return $response;
	}

	private static function replace_navigation( $navs, $content ) {
		if ( ! empty( $navs ) ) {
			$theme = wp_get_theme()->get_stylesheet();
			foreach ( $navs as $old_key => $id ) {
				// replace id.
				$pattern = '/<!--[ \t]+wp:navigation[ \t]+{.*"ref".*:.*' . absint( $old_key ) . ',/m';
				$content = preg_replace( $pattern, '<!-- wp:navigation {"ref":' . absint( $id ) . ',', $content );
			}
		}

		return $content;
	}

	private static function replace_theme_name( $old_theme, $content ) {
		$theme   = wp_get_theme()->get_stylesheet();
		$pattern = '/"theme":"' . $old_theme . '"/m';
		$content = preg_replace( $pattern, '"theme":"' . $theme . '"', $content );

		return $content;
	}

	public static function import_templates( \WP_REST_Request $req ) {
		$params = (array) $req->get_params();
		$data   = array();
		if ( ! empty( $params['data'] ) ) {
			$posts = $params['data'];
			foreach ( $posts as $post ) {
				$post    = (array) $post;
				$content = $post['content'];
				if ( ! empty( $params['importedData']['navigations'] ) ) {
					$content = self::replace_navigation( $params['importedData']['navigations'], $post['content'] );
				}

				$post_data = array();

				if ( ! empty( $post['theme'] ) ) {
					$content = self::replace_theme_name( $post['theme'], $content );
				}
				$post_data['theme']        = ! empty( $params['theme'] ) ? $params['theme'] : '';
				$post_data['post_title']   = $post['title'];
				$post_data['post_content'] = $content;
				$post_data['area']         = $post['area'];
				$post_data['post_name']    = $post['slug'];
				$post_id                   = self::create_post( $post_data, $post['type'] );
				$data[ $post['wp_id'] ]    = ( ! is_wp_error( $post_id ) ) ? $post_id : false;
			}
		}
		if ( empty( $data ) ) {
			wp_send_json( array(), 404 );
		}
		wp_send_json( $data );
	}

	private static function create_post( $data, $type = 'post' ) {
		$constants = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];
		$plugin_main_function_prefix = $constants['plugin_main_function_prefix'];
		$plugin_main_post_type_prefix = $constants['plugin_main_post_type_prefix'];

		$theme   = wp_get_theme()->get_stylesheet();
		$data    = (array) $data;
		$content = $data['post_content'];
		if ( in_array( $type, array( 'wp_navigation' ) ) ) {
			$old_theme = ! empty( $data['theme'] ) ? $data['theme'] : '';
			$content   = str_replace( '/' . $old_theme . '/', '/', $data['post_content'] );
			// error_log( $old_theme );
			// error_log( $content );
		}
		// Create post object.
		$my_post = array(
			'post_title'   => wp_strip_all_tags( $data['post_title'] ),
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => $type,
		);

		if ( ! empty( $data['post_name'] ) ) {
			$my_post['post_name'] = $data['post_name'];
		}

		if ( in_array( $type, array( 'wp_template_part', 'wp_template' ) ) ) {
			$my_post['tax_input'] = array(
				'wp_theme' => $theme,
			);
			if ( ! empty( $data['area'] ) ) {
				$my_post['tax_input']['wp_template_part_area'] = _filter_block_template_part_area( $data['area'] );
			}
		}

		// Insert the post into the database.
		$post_id = wp_insert_post( $my_post );
		if ( ! is_wp_error( $post_id ) ) {
			if ( in_array( $type, array( 'post', 'page', $plugin_main_post_type_prefix . '_template' ), true ) ) {
				// if ( ! empty( $data['_meta_data']['_wp_page_template'] ) ) {
				// update_post_meta( $post_id, '_wp_page_template', $data['_meta_data']['_wp_page_template'] );
				// }
				if ( ! empty( $data['_meta_data'] ) ) {
					foreach ( $data['_meta_data'] as $meta_key => $meta ) {
						update_post_meta( $post_id, $meta_key, $meta );
					}
				}
			}

			update_post_meta( $post_id, '_is_' . $plugin_main_function_prefix. '_demo_imported', true );
			update_post_meta( $post_id, '_' . $plugin_main_function_prefix. '_demo_imported_data', $data );
			// if ( ! empty( $data['area'] ) ) {
			// $result = wp_set_post_terms( $post_id, 'wp_template_part_area', _filter_block_template_part_area( $data['area'] ) );
			// error_log( print_r( $result, true ) );
		}

		// $result = wp_set_post_terms( $post_id, 'wp_theme', $theme );
		// error_log( print_r( $result, true ) );
		// }
		// }
		return $post_id;
	}

	/**
	 * Get all demos.
	 *
	 * @return void
	 */
	public static function get_demos() {
		$constants = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];
		$plugin_main_function_prefix = $constants['plugin_main_function_prefix'];
		$plugin_main_site_domain = $constants['plugin_main_site_domain'];

		// [TODO] Update url.
		$json_data = wp_remote_get(
			'https://demo.'.$plugin_main_site_domain.'/wp-json/liger/v1/demos',
			array(
				'timeout' => 10,
				'headers' => array(
					'liger_hkey' => 'sskdfjks3qw4sdfjs',
				),
			)
		);
		try {
			$json = json_decode( $json_data['body'] );
			$json = ! empty( $json ) ? $json : array();
		} catch ( Exception $ex ) {
			$json = array();
		}

		wp_send_json( $json );
	}

	public static function get_demo_content( $request ) {
		$constants = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];
		$plugin_main_function_prefix = $constants['plugin_main_function_prefix'];
		$plugin_main_site_domain = $constants['plugin_main_site_domain'];

		$params = (array) $request->get_params();
		$name   = ! empty( $params['name'] ) ? $params['name'] : '';
		$json   = array();
		if ( ! empty( $name ) ) {
			$json_data = wp_remote_get(
				'https://demo.'.$plugin_main_site_domain.'/' . $name . '/wp-json/liger/v1/demo-content',
				array(
					'timeout' => 10,
					'headers' => array(
						'liger_hkey' => 'sskdfjks3qw4sdfjs',
					),
				)
			);
			try {
				$json = json_decode( $json_data['body'] );
				$json = ! empty( $json ) ? $json : array();
				error_log( print_r( $json, true ) );
			} catch ( Exception $ex ) {
				$json = array();
			}
		}
		wp_send_json( $json );
	}

	public static function activate_theme( \WP_REST_Request $req ) {
		$params = (array) $req->get_params();
		$result = false;
		if ( ! empty( $params['slug'] ) ) {
			$theme  = trim( $params['slug'] );
			switch_theme( $theme );
			$result = true;
		}
		wp_send_json( $result );
	}

	public static function verify_theme_installation( \WP_REST_Request $req ) {
		$params = (array) $req->get_params();
		$result = array();
		if ( ! empty( $params['slug'] ) ) {
			$all_themes      = wp_get_themes();
			$theme           = trim( $params['slug'] );
			$themes          = array_keys( $all_themes );
			$in_theme_active = get_stylesheet() === $theme;
			$result          = in_array( $theme, $themes, true );
			wp_send_json(
				array(
					'installed_theme' => $result,
					'theme_activated' => $in_theme_active,
				)
			);
		}
	}
}

Rest_Demo_Importer_V2::init();
