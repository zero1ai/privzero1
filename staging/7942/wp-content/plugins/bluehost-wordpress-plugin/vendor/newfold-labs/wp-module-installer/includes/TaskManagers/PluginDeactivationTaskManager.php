<?php
namespace NewfoldLabs\WP\Module\Installer\TaskManagers;

use NewfoldLabs\WP\Module\Installer\Data\Options;
use NewfoldLabs\WP\Module\Installer\Models\PriorityQueue;
use NewfoldLabs\WP\Module\Installer\Tasks\PluginDeactivationTask;

/**
 * Manages the execution of PluginDeactivationTasks.
 */
class PluginDeactivationTaskManager {

	/**
	 * The number of times a PluginDeactivationTask can be retried.
	 *
	 * @var int
	 */
	private static $retry_limit = 1;

	/**
	 * The name of the queue, might be prefixed.
	 *
	 * @var string
	 */
	private static $queue_name = 'plugin_deactivation_queue';

	/**
	 * Schedules the crons.
	 */
	public function __construct() {

		// Thirty second cron hook
		add_action( 'nfd_module_installer_plugin_deactivation_event', array( $this, 'deactivate' ) );

		// Register the cron task
		if ( ! wp_next_scheduled( 'nfd_module_installer_plugin_deactivation_event' ) ) {
			wp_schedule_single_event( time() + 5, 'nfd_module_installer_plugin_deactivation_event' );
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
	 * Queue out all the PluginDeactivationTask's in the plugin deactivation queue and execute them.
	 *
	 * @return array|false
	 */
	public function deactivate() {
		/*
		Get the plugins queued up to be deactivated, the PluginDeactivationTask gets
		converted to an associative array before storing it in the option.
		*/
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );
		\update_option( Options::get_option_name( self::$queue_name ), array() );
		$retries = array();
		foreach ( $plugins as $plugin ) {
			$plugin_deactivation_task = new PluginDeactivationTask(
				$plugin['slug'],
				$plugin['priority'],
				$plugin['retries']
			);
			$status                   = $plugin_deactivation_task->execute();
			if ( ! $status ) {
				if ( $plugin_deactivation_task->get_retries() <= self::$retry_limit ) {
					array_push( $retries, $plugin_deactivation_task->to_array() );
				}
			}
		}
		// Update the plugin deactivation queue.
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );
		array_merge( $plugins, $retries );
		\update_option( Options::get_option_name( self::$queue_name ), $plugins );

		return true;
	}

	/**
	 * Adds a new PluginDeactivationTask to the Plugin deactivation queue.
	 * The Task will be inserted at an appropriate position in the queue based on it's priority.
	 *
	 * @param PluginDeactivationTask $plugin_deactivation_task The task to be inserted.
	 * @return array|false
	 */
	public static function add_to_queue( PluginDeactivationTask $plugin_deactivation_task ) {
		/*
		Get the plugins queued up to be deactivated, the PluginDeactivationTask gets
		converted to an associative array before storing it in the option.
		*/
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );

		$queue = new PriorityQueue();
		foreach ( $plugins as $queued_plugin ) {
			/*
			Check if there is an already existing PluginDeactivationTask in the queue
			for a given slug.
			*/
			if ( $queued_plugin['slug'] === $plugin_deactivation_task->get_slug() ) {
				return false;
			}
			$queue->insert( $queued_plugin, $queued_plugin['priority'] );
		}

		// Insert a new PluginDeactivationTask at the appropriate position in the queue.
		$queue->insert(
			$plugin_deactivation_task->to_array(),
			$plugin_deactivation_task->get_priority()
		);

		return \update_option( Options::get_option_name( self::$queue_name ), $queue->to_array() );
	}

	/**
	 * Removes a PluginDeactivationTask from the queue.
	 *
	 * @param string $plugin The slug of the task to remove.
	 * @return array
	 */
	public static function remove_from_queue( $plugin ) {
		/*
		Get the plugins queued up to be deactivated, the PluginDeactivationTask task gets
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
