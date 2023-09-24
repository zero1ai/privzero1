<?php
namespace NewfoldLabs\WP\Module\Installer\TaskManagers;

use NewfoldLabs\WP\Module\Installer\Data\Plugins;
use NewfoldLabs\WP\Module\Installer\Data\Options;
use NewfoldLabs\WP\Module\Installer\Models\PriorityQueue;
use NewfoldLabs\WP\Module\Installer\Tasks\PluginUninstallTask;
use NewfoldLabs\WP\Module\Installer\Services\PluginUninstaller;

/**
 * Manages the execution of PluginUninstallTasks.
 */
class PluginUninstallTaskManager {

	/**
	 * The number of times a PluginUninstallTask can be retried.
	 *
	 * @var int
	 */
	private static $retry_limit = 2;

	/**
	 * Name of the PluginUninstallTask Queue.
	 *
	 * @var string
	 */
	private static $queue_name = 'plugin_uninstall_queue';

	/**
	 * PluginUninstallTaskManager constructor.
	 */
	public function __construct() {
		// Ensure there is a Ten second option in the cron schedules
		add_filter( 'cron_schedules', array( $this, 'add_ten_seconds_schedule' ) );

		// Ten second cron hook
		add_action( 'nfd_module_installer_plugin_uninstall_cron', array( $this, 'uninstall' ) );

		// Register the cron task
		if ( ! wp_next_scheduled( 'nfd_module_installer_plugin_uninstall_cron' ) ) {
			wp_schedule_event( time(), 'ten_seconds', 'nfd_module_installer_plugin_uninstall_cron' );
		}
	}

	/**
	 * Retrieve the Queue Name for the TaskManager to perform Plugin Uninstalls.
	 *
	 * @return string
	 */
	public static function get_queue_name() {
		return self::$queue_name;
	}

	/**
	 * Adds ten seconds option in the cron schedule.
	 *
	 * @param array $schedules Cron Schedule duration
	 * @return array
	 */
	public function add_ten_seconds_schedule( $schedules ) {
		if ( ! array_key_exists( 'ten_seconds', $schedules ) || 10 !== $schedules['ten_seconds']['interval'] ) {
			$schedules['ten_seconds'] = array(
				'interval' => 10,
				'display'  => __( 'Once Every Ten Seconds' ),
			);
		}

		return $schedules;
	}

	/**
	 * Queue out a PluginUninstallTask with the highest priority in the plugin uninstall queue and execute it.
	 *
	 * @return array|false
	 */
	public function uninstall() {
		/*
		Get the plugins queued up to be uninstalled, the PluginUninstall task gets
		converted to an associative array before storing it in the option.
		*/
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );

		/*
		Conversion of the max heap to an array will always place the PluginUninstallTask with the highest
		priority at the beginning of the array
		*/
		$plugin_to_uninstall = array_shift( $plugins );
		if ( ! $plugin_to_uninstall ) {
			return true;
		}

		// Update the plugin uninstall queue.
		\update_option( Options::get_option_name( self::$queue_name ), $plugins );

		// Recreate the PluginInstall task from the associative array.
		$plugin_uninstall_task = new PluginUninstallTask(
			$plugin_to_uninstall['slug'],
			$plugin_to_uninstall['priority'],
			$plugin_to_uninstall['retries']
		);

		// Execute the PluginUninstall Task.
		$status = $plugin_uninstall_task->execute();
		if ( \is_wp_error( $status ) ) {

			// If there is an error, then increase the retry count for the task.
			$plugin_uninstall_task->increment_retries();

			/*
				If the number of retries have not exceeded the limit
				then re-queue the task at the end of the queue to be retried.
			*/
			if ( $plugin_uninstall_task->get_retries() <= self::$retry_limit ) {
				array_push( $plugins, $plugin_uninstall_task->to_array() );

				// Update the plugin install queue.
				\update_option( Options::get_option_name( self::$queue_name ), $plugins );
			}
		}

		return true;
	}

	/**
	 * Adds a new PluginUninstallTask to the Plugin Uninstall queue.
	 * The Task will be inserted at an appropriate position in the queue based on it's priority.
	 *
	 * @param PluginUninstallTask $plugin_uninstall_task Plugin Task Details
	 * @return array|false
	 */
	public static function add_to_queue( PluginUninstallTask $plugin_uninstall_task ) {
		/*
		Get the plugins queued up to be uninstalled, the PluginUninstall task gets
		converted to an associative array before storing it in the option.
		*/
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );

		$position_in_queue = PluginInstallTaskManager::status( $plugin_uninstall_task->get_slug() );
		if ( false !== $position_in_queue && 0 !== $position_in_queue ) {
			PluginInstallTaskManager::remove_from_queue(
				$plugin_uninstall_task->get_slug()
			);

			return true;
		}

		$plugin_list = Plugins::get_squashed();
		// Gets the specified path for the Plugin from the predefined list
		$plugin_path = $plugin_list[ $plugin_uninstall_task->get_slug() ]['path'];

		if ( ! PluginUninstaller::is_plugin_installed( $plugin_path ) ) {
			return true;
		}

		$queue = new PriorityQueue();
		foreach ( $plugins as $queued_plugin ) {
			/*
			Check if there is an already existing PluginUninstallTask in the queue
			for a given slug.
			*/
			if ( $queued_plugin['slug'] === $plugin_uninstall_task->get_slug() ) {
				return false;
			}
			$queue->insert( $queued_plugin, $queued_plugin['priority'] );
		}

		// Insert a new PluginUninstallTask at the appropriate position in the queue.
		$queue->insert(
			$plugin_uninstall_task->to_array(),
			$plugin_uninstall_task->get_priority()
		);

		return \update_option( Options::get_option_name( self::$queue_name ), $queue->to_array() );
	}

	/**
	 * Returns the status of given plugin slug - uninstalling/completed.
	 *
	 * @param string $plugin Plugin Slug
	 * @return string|false
	 */
	public static function status( $plugin ) {
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );
		return array_search( $plugin, array_column( $plugins, 'slug' ), true );
	}
}
