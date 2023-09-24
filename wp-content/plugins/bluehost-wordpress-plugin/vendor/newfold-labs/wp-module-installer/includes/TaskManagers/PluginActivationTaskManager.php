<?php
namespace NewfoldLabs\WP\Module\Installer\TaskManagers;

use NewfoldLabs\WP\Module\Installer\Data\Options;
use NewfoldLabs\WP\Module\Installer\Models\PriorityQueue;
use NewfoldLabs\WP\Module\Installer\Tasks\PluginActivationTask;

/**
 * Manages the execution of PluginActivationTasks.
 */
class PluginActivationTaskManager {

	/**
	 * The number of times a PluginActivationTask can be retried.
	 *
	 * @var int
	 */
	private static $retry_limit = 1;

	/**
	 * The name of the queue, might be prefixed.
	 *
	 * @var string
	 */
	private static $queue_name = 'plugin_activation_queue';

	/**
	 * Schedules the crons.
	 */
	public function __construct() {

		// Thirty second cron hook
		add_action( 'nfd_module_installer_plugin_activation_event', array( $this, 'activate' ) );

		if ( ! wp_next_scheduled( 'nfd_module_installer_plugin_activation_event' ) ) {
			wp_schedule_single_event( time() + 5, 'nfd_module_installer_plugin_activation_event' );
		}
	}


	/**
	 * Returns the queue name, might be prefixed.
	 *
	 * @return string
	 */
	public static function get_queue_name() {
		return self::$queue_name;
	}


	/**
	 * Queue out all the PluginActivationTask's in the plugin activation queue and execute them.
	 *
	 * @return array|false
	 */
	public function activate() {
		/*
		Get the plugins queued up to be activated, the PluginActivationTask gets
		converted to an associative array before storing it in the option.
		*/
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );
		\update_option( Options::get_option_name( self::$queue_name ), array() );

		$retries = array();
		foreach ( $plugins as $plugin ) {
			$plugin_activation_task = new PluginActivationTask(
				$plugin['slug'],
				$plugin['priority'],
				$plugin['retries']
			);
			$status                 = $plugin_activation_task->execute();
			if ( is_wp_error( $status ) ) {
				if ( $plugin_activation_task->get_retries() <= self::$retry_limit ) {
					array_push( $retries, $plugin_activation_task->to_array() );
				}
			}
		}
		// Get latest instance of the plugin activation queue and update it.
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );
		array_merge( $plugins, $retries );
		\update_option( Options::get_option_name( self::$queue_name ), $plugins );

		return true;
	}

	/**
	 * Adds a new PluginActivationTask to the Plugin activation queue.
	 * The Task will be inserted at an appropriate position in the queue based on it's priority.
	 *
	 * @param PluginActivationTask $plugin_activation_task The task to be inserted.
	 * @return array|false
	 */
	public static function add_to_queue( PluginActivationTask $plugin_activation_task ) {
		/*
		Get the plugins queued up to be activated, the PluginActivationTask task gets
		converted to an associative array before storing it in the option.
		*/
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );

		$queue = new PriorityQueue();
		foreach ( $plugins as $queued_plugin ) {
			/*
			Check if there is an already existing PluginActivationTask in the queue
			for a given slug.
			*/
			if ( $queued_plugin['slug'] === $plugin_activation_task->get_slug() ) {
				return false;
			}
			$queue->insert( $queued_plugin, $queued_plugin['priority'] );
		}

		// Insert a new PluginActivationTask at the appropriate position in the queue.
		$queue->insert(
			$plugin_activation_task->to_array(),
			$plugin_activation_task->get_priority()
		);

		return \update_option( Options::get_option_name( self::$queue_name ), $queue->to_array() );
	}

	/**
	 * Removes a PluginActivationTask from the queue.
	 *
	 * @param string $plugin The slug of the task to remove.
	 * @return array
	 */
	public static function remove_from_queue( $plugin ) {
		/*
		Get the plugins queued up to be activated, the PluginActivationTask task gets
		converted to an associative array before storing it in the option.
		*/
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );

		$queue = new PriorityQueue();
		foreach ( $plugins as $queued_plugin ) {
			/*
			If the Plugin slug does not match add it back to the queue.
			*/
			if ( $queued_plugin['slug'] !== $plugin ) {
				$queue->insert( $queued_plugin, $queued_plugin['priority'] );
			}
		}

		return \update_option( Options::get_option_name( self::$queue_name ), $queue->to_array() );
	}

	/**
	 * Get the status of a given plugin slug from the queue.
	 *
	 * @param string $plugin The slug of the plugin.
	 * @return boolean
	 */
	public static function status( $plugin ) {
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );
		return array_search( $plugin, array_column( $plugins, 'slug' ), true );
	}
}
