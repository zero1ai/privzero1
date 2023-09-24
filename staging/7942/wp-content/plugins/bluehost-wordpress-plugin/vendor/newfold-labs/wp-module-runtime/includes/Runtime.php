<?php

namespace NewfoldLabs\WP\Module\Runtime;

use NewfoldLabs\WP\ModuleLoader\Container;

/**
 * Class Runtime
 *
 * @package NewfoldLabs\WP\Module\Runtime
 */
class Runtime {
	/**
	 * Main Runtime file
	 *
	 * Entry point via bootstrap.php
	 */

	/**
	 * Container loaded from the brand plugin.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Runtime constructor.
	 *
	 * @param Container $container Container loaded from the brand plugin.
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	public function loadIntoPage( $page ) {
		add_action( $page, array( $this, 'register_runtime' ) );
	}

	public function prepareRuntime() {
		global $wp_version;
		$sdk = apply_filters( 'newfold-runtime', array( 'wpversion' => $wp_version ) );
		return array(
			'site'         => array(
				'url'   => \get_site_url(),
				'title' => htmlspecialchars_decode( \get_bloginfo( 'name' ) ),
			),
			'admin_url'    => \admin_url(),
			'base_url'     => \get_home_url() . '/index.php',
			'capabilities' => $this->container->get( 'capabilities' )->all(),
			'sdk'          => $sdk,
		);
	}

	/**
	 * Load Runtime into the page.
	 */
	public function register_runtime() {
		\wp_add_inline_script(
			$this->container->plugin()->id . '-script',
			'window.NewfoldRuntime =' . wp_json_encode( $this->prepareRuntime( $this->container ) ) . ';',
			'before'
		);
	}

}
