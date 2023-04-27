<?php

use NewfoldLabs\WP\Module\Tasks\Models\Task;

/**
 * Class BH_Site_Migrator_Utilities
 */
class BH_Site_Migrator_Utilities {

	/**
	 * Get path relative to this plugin's custom upload directory.
	 *
	 * @param string $path Path relative to this plugin's custom upload directory.
	 *
	 * @return string
	 */
	public static function get_upload_path( $path = '' ) {
		$uploads   = wp_get_upload_dir();
		$directory = $uploads['basedir'] . DIRECTORY_SEPARATOR . 'bluehost-site-migrator' . DIRECTORY_SEPARATOR;
		wp_mkdir_p( $directory );

		if ( ! file_exists( $directory . '/index.php' ) ) {
			file_put_contents( $directory . '/index.php', '<?php // Silence is golden.' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
		}

		return $directory . ltrim( $path, DIRECTORY_SEPARATOR );
	}

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
	 * Initialize REST API endpoints.
	 */
	public static function rest_api_init() {
		$controllers = array(
			'BH_Site_Migrator_REST_Can_We_Migrate_Controller',
			'BH_Site_Migrator_REST_Manifest_Controller',
			'BH_Site_Migrator_REST_Migration_Id_Controller',
			'BH_Site_Migrator_REST_Migration_Regions_Controller',
			'BH_Site_Migrator_REST_Migration_Package_Controller',
		);

		foreach ( $controllers as $controller ) {
			/**
			 * Get an instance of the WP_REST_Controller
			 *
			 * @var $instance WP_REST_Controller
			 */
			$instance = new $controller();
			$instance->register_routes();
		}
	}

	/**
	 * Zip up an entire directory recursively.
	 *
	 * @param string $directory Absolute path to directory.
	 * @param string $name      Name to be appended to filename.
	 *
	 * @return string The path to the zip file on success or an empty string on failure.
	 */
	public static function zip_directory( $directory, $name ) {
		$filename = BH_Site_Migrator_Migration_Package::generate_name( $name );
		$zip_path = self::get_upload_path( $filename );

		$zip = new ZipArchive();
		if ( true === $zip->open( $zip_path, ZipArchive::CREATE ) ) {

			$dir_iterator    = new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS );
			$filter_iterator = new BH_Site_Migrator_Filter_Iterator( $dir_iterator );
			$files           = new RecursiveIteratorIterator( $filter_iterator, RecursiveIteratorIterator::SELF_FIRST );

			foreach ( $files as $file_name => $file ) {
				// Get real and relative path for current file
				$absolute_path = $file->getRealPath();
				$relative_path = ltrim( substr( $absolute_path, strlen( $directory ) ), DIRECTORY_SEPARATOR );

				if ( $file->isDir() ) {
					$zip->addEmptyDir( $relative_path );
				} else {
					$zip->addFile( $absolute_path, $relative_path );
				}
			}

			$success = $zip->close();

			if ( $success ) {
				return $zip_path;
			}
		}

		return '';
	}

	/**
	 * A task to package and record results in the db
	 *
	 * @param array $args the package type in form of an array with package type as key
	 */
	public static function create_package_task( $args ) {
		$package_type = $args['package_type'];
		$package_data = array();

		if ( ! BH_Site_Migrator_Migration_Package::is_valid_type( $package_type ) ) {
			return $package_data;
		}

		$packager = BH_Site_Migrator_Packager_Factory::create( $package_type );
		$package  = $packager->create_package();

		if ( ! empty( $package ) ) {

			$uploads = wp_get_upload_dir();

			$package_data = array(
				'hash'      => md5_file( $package ),
				'path'      => $package,
				'size'      => filesize( $package ),
				'timestamp' => time(),
				'url'       => str_replace( $uploads['basedir'], $uploads['baseurl'], $package ),
			);

			BH_Site_Migrator_Options::set( $package_type, $package_data );

		}
	}

	/**
	 * A function to queue in all the required tasks for packaging
	 */
	public static function queue_packaging_tasks() {
		$package_types = BH_Site_Migrator_Packager_Factory::get_package_types();
		foreach ( $package_types as $package_type ) {
			// Add a task to package that type with 2 num retries
			$task = new Task();
			$task->set_args( array( 'package_type' => $package_type ) )
				->set_task_name( 'package_' . $package_type )
				->set_task_execute( 'BH_Site_Migrator_Utilities::create_package_task' )
				->set_num_retries( 2 )
				->set_task_priority( 10 );
			$task->queue_task();
		}
	}

	/**
	 * A function to get all the packaging task names
	 */
	public static function get_packaging_task_names() {
		$task_names    = array();
		$package_types = BH_Site_Migrator_Packager_Factory::get_package_types();
		foreach ( $package_types as $package_type ) {
			array_push( $task_names, 'package_' . $package_type );
		}
		return $task_names;
	}
}
