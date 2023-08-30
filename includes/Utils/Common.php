<?php

namespace BluehostSiteMigrator\Utils;

use NewfoldLabs\WP\Module\Tasks\Models\Task;

class Common {
	/**
	 * Get the path to the wp-config.php file.
	 *
	 * @return string
	 */
	public static function locate_wp_config_file() {
		$path = '';
		if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
			$path = ABSPATH . 'wp-config.php';
		} elseif ( file_exists( dirname( ABSPATH ) . '/wp-config.php' ) ) {
			$path = dirname( ABSPATH ) . '/wp-config.php';
		}

		return $path;
	}

	/**
	 * Get all the task names
	 *
	 * @return array
	 */
	public static function get_packaging_task_names() {
		return array(
			'package_database',
			'archive_database',
			'archive_plugins',
			'archive_themes',
			'archive_uploads',
			'archive_mu_plugins',
			'archive_dropins',
			'archive_root',
		);
	}

	/**
	 * Delete all the queued tasks
	 */
	public static function purge_tasks() {
		$task_names = self::get_packaging_task_names();
		foreach ( $task_names as $task_name ) {
			$tasks_to_delete = Task::get_tasks_with_name( $task_name );
			foreach ( $tasks_to_delete as $task ) {
				$task_obj = new Task( $task->task_id );
				$task_obj->delete();
			}
		}
	}
}
