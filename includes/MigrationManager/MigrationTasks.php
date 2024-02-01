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
		// Database dump task
		$database_dump_task = new Task();
		$database_dump_task->set_task_name( 'package_database' )
				->set_task_execute( 'BluehostSiteMigrator\\Packager\\DatabaseDumper::execute' )
				->set_num_retries( 1 )
				->set_task_priority( 20 );
		$database_dump_task->queue_task();

		// Database archive task
		$database_archive_task = new Task();
		$database_archive_task->set_task_name( 'archive_database' )
				->set_task_execute( 'BluehostSiteMigrator\\Packager\\DatabaseArchiver::execute' )
				->set_num_retries( 1 )
				->set_task_priority( 18 );
		$database_archive_task->queue_task();

		// Plugins archive task
		$plugins_archive_task = new Task();
		$plugins_archive_task->set_task_name( 'archive_plugins' )
				->set_task_execute( 'BluehostSiteMigrator\\Packager\\PluginsArchiver::execute' )
				->set_num_retries( 1 )
				->set_task_priority( 16 );
		$plugins_archive_task->queue_task();

		// Themes archive task
		$themes_archive_task = new Task();
		$themes_archive_task->set_task_name( 'archive_themes' )
				->set_task_execute( 'BluehostSiteMigrator\\Packager\\ThemesArchiver::execute' )
				->set_num_retries( 1 )
				->set_task_priority( 14 );
		$themes_archive_task->queue_task();

		// Uploads archive task
		$upload_archive_task = new Task();
		$upload_archive_task->set_task_name( 'archive_uploads' )
				->set_task_execute( 'BluehostSiteMigrator\\Packager\\UploadsArchiver::execute' )
				->set_num_retries( 1 )
				->set_task_priority( 12 );
		$upload_archive_task->queue_task();

		// Mu Plugins archive task
		$mu_plugins_archive_task = new Task();
		$mu_plugins_archive_task->set_task_name( 'archive_mu_plugins' )
				->set_task_execute( 'BluehostSiteMigrator\\Packager\\MuPluginsArchiver::execute' )
				->set_num_retries( 1 )
				->set_task_priority( 10 );
		$mu_plugins_archive_task->queue_task();

		// Dropins archiver
		$dropins_archive_task = new Task();
		$dropins_archive_task->set_task_name( 'archive_dropins' )
				->set_task_execute( 'BluehostSiteMigrator\\Packager\\DropinsArchiver::execute' )
				->set_num_retries( 1 )
				->set_task_priority( 8 );
		$dropins_archive_task->queue_task();

		// Root archiver
		$root_archive_task = new Task();
		$root_archive_task->set_task_name( 'archive_root' )
				->set_task_execute( 'BluehostSiteMigrator\\Packager\\RootArchiver::execute' )
				->set_num_retries( 1 )
				->set_task_priority( 6 );
		$root_archive_task->queue_task();
	}
}
