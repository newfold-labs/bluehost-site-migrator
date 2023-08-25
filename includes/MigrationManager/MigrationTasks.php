<?php

namespace BluehostSiteMigrator\MigrationManager;

use NewfoldLabs\WP\Module\Tasks\Models\Task;

/**
 * The class managing queuing and execution of tasks and other utilities
 */
class MigrationTasks {
	/**
	 * It is essential for the wp-cron to be enabled, so enable that here
	 */
	public function __construct() {
		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		// Initialize the wp file system
		WP_Filesystem();

		// Path to wp-config.php
		$config_file_path = ABSPATH . 'wp-config.php';
		$config_content   = $wp_filesystem->get_contents( $config_file_path );
		$config_content   = preg_replace(
			'/define\(\s*\'DISABLE_WP_CRON\'\s*,\s*true\s*\);/',
			"define('DISABLE_WP_CRON', false);",
			$config_content
		);
		$wp_filesystem->put_contents( $config_file_path, $config_content );
	}

	/**
	 * Queue the tasks
	 */
	public function queue_tasks() {
		// Set the tasks, set priorities to wait for one to complete retries before moving on.
		$database_task = new Task();
		$database_task->set_task_name( 'package_database' )
				->set_task_execute( 'BluehostSiteMigrator\\Packager\\Database::execute' )
				->set_num_retries( 2 )
				->set_task_priority( 50 );
		$database_task->queue_task();
	}
}
