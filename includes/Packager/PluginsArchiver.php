<?php

namespace BluehostSiteMigrator\Packager;

use BluehostSiteMigrator\Archiver\Compressor;
use BluehostSiteMigrator\Utils\Options;
use BluehostSiteMigrator\Utils\Status;

/**
 * Archive the plugins directory
 */
class PluginsArchiver extends PackagerBase {
	/**
	 * Get the parameters for database packaging
	 */
	public static function get_plugins_params() {
		return Options::get( 'plugins_task_params' );
	}

	/**
	 * Set the parameters for database packaging
	 *
	 * @param array $params The updated database params
	 */
	public static function set_plugins_params( $params ) {
		Options::set( 'plugins_task_params', $params );
	}

	/**
	 * Prepare the packaging, populate parameters
	 */
	public static function prepare() {
		$plugin_task_params = self::get_plugins_params();

		// Get total plugins files count
		if ( isset( $plugin_task_params['total_plugins_files_count'] ) ) {
			$total_plugins_files_count = (int) $plugin_task_params['total_plugins_files_count'];
		} else {
			$total_plugins_files_count = 1;
		}

		// Get total plugins files size
		if ( isset( $plugin_task_params['total_plugins_files_size'] ) ) {
			$total_plugins_files_size = (int) $plugin_task_params['total_plugins_files_size'];
		} else {
			$total_plugins_files_size = 1;
		}

		if ( isset( $plugin_task_params['plugins_list_path'] ) ) {
			$plugins_list_file_path = $plugin_task_params['plugins_list_path'];
		} else {
			$plugins_list_file_path                  = nfd_bhsm_get_hashed_file_path( 'plugins', 'config', 'list' );
			$plugin_task_params['plugins_list_path'] = $plugins_list_file_path;
		}

		// Set the progress
		Status::set_status( 'Retrieving a list of WordPress plugin files ...', 30, 'plugins' );

		// Add the current plugin to the excluded list
		$exclude_filters = array( BH_SITE_MIGRATOR_PLUGIN_NAME );

		// Create the plugin details file

		$plugins_list = nfd_bhsm_open( $plugins_list_file_path, 'w' );

		// Enumerate over plugins directory
		$iterator = new \RecursiveDirectoryIterator( nfd_bhsm_plugins_dir(), \FilesystemIterator::SKIP_DOTS );

		// Define the directory filter
		$filter = function ( $file, $key, $iterator ) use ( $exclude_filters ) {
			if ( $iterator->hasChildren() ) {
				if ( ! in_array(
					$file->getFilename(),
					$exclude_filters
				) ) {
					return true;
				}
			}
			return $file->isFile();
		};

		// Exclude the plugin files
		$iterator = new \RecursiveCallbackFilterIterator( $iterator, $filter );
		$iterator = new \RecursiveIteratorIterator( $iterator );

		// Write path line
		foreach ( $iterator as $item ) {
			if ( $item->isFile() ) {
				$written = nfd_bhsm_putcsv(
					$plugins_list,
					array(
						$iterator->getPathname(),
						$iterator->getSubPathname(),
						$iterator->getSize(),
						$iterator->getMTime(),
					),
				);
				if ( $written ) {
					++$total_plugins_files_count;
					$total_plugins_files_size += $iterator->getSize();
				}
			}
		}

		Status::set_status( 'Done retrieving a list of WordPress plugin files.', 32, 'plugins' );

		$plugin_task_params['total_plugins_files_count'] = $total_plugins_files_count;

		// Set total plugins files size
		$plugin_task_params['total_plugins_files_size'] = $total_plugins_files_size;

		fclose( $plugins_list );

		self::set_plugins_params( $plugin_task_params );
	}

	/**
	 * Make the plugins package
	 */
	public static function execute() {
		$params = self::get_plugins_params();

		// If the plugin list is not prepared yet, do that.
		if ( ! isset( $params['plugins_list_path'] ) ) {
			self::prepare();
			// Repopulate params
			$params = self::get_plugins_params();
		}

		// Set archive bytes offset
		if ( isset( $params['archive_bytes_offset'] ) ) {
			$archive_bytes_offset = (int) $params['archive_bytes_offset'];
		} else {
			$archive_bytes_offset = 0;
		}

		// Set file bytes offset
		if ( isset( $params['file_bytes_offset'] ) ) {
			$file_bytes_offset = (int) $params['file_bytes_offset'];
		} else {
			$file_bytes_offset = 0;
		}

		// Set plugins bytes offset
		if ( isset( $params['plugins_bytes_offset'] ) ) {
			$plugins_bytes_offset = (int) $params['plugins_bytes_offset'];
		} else {
			$plugins_bytes_offset = 0;
		}

		// Get processed files size
		if ( isset( $params['processed_files_size'] ) ) {
			$processed_files_size = (int) $params['processed_files_size'];
		} else {
			$processed_files_size = 0;
		}

		// Get total plugins files size
		if ( isset( $params['total_plugins_files_size'] ) ) {
			$total_plugins_files_size = (int) $params['total_plugins_files_size'];
		} else {
			$total_plugins_files_size = 1;
		}

		// Get total plugins files count
		if ( isset( $params['total_plugins_files_count'] ) ) {
			$total_plugins_files_count = (int) $params['total_plugins_files_count'];
		} else {
			$total_plugins_files_count = 1;
		}

		// Set the plugin list file path
		if ( isset( $params['plugins_list_path'] ) ) {
			$plugins_list_path = $params['plugins_list_path'];
		} else {
			$plugins_list_path = '';
		}

		// What percent of files have we processed?
		$progress = (int) min( ( $processed_files_size / $total_plugins_files_size ) * 100, 100 );

		Status::set_status(
			sprintf(
				'Archiving %d plugin files ... ',
				$total_plugins_files_count,
			),
			35,
			'plugins'
		);

		// Flag to hold if data has been processed
		$completed = true;

		// Start time
		$start = microtime( true );

		// Get plugins list file
		$plugins_list = nfd_bhsm_open( $plugins_list_path, 'r' );

		// Get the database archive path
		if ( isset( $params['plugins_archive_path'] ) ) {
			$plugins_archive_path = $params['plugins_archive_path'];
		} else {
			$plugins_archive_path = nfd_bhsm_get_hashed_file_path( 'plugins', 'backup', 'zip' );
			// Set the archiver path in params
			$params['plugins_archive_path'] = $plugins_archive_path;
		}

		// Set the file pointer at the current
		if ( fseek( $plugins_list, $plugins_bytes_offset ) !== -1 ) {

			// Open the archive file for writing
			$archive = new Compressor( $plugins_archive_path );

			// Set the file pointer to the one that we have saved
			$archive->set_file_pointer( $archive_bytes_offset );

			// Loop over the files
			while ( list($file_abspath, $file_relpath, $file_size, $file_mtime) = fgetcsv( $plugins_list ) ) { // phpcs:ignore
				$file_bytes_written = 0;

				$completed = $archive->add_file( $file_abspath, $file_relpath, $file_bytes_written, $file_bytes_offset );

				// Add file to archive
				if ( $completed ) {
					$file_bytes_offset = 0;

					// Get plugins bytes offset
					$plugins_bytes_offset = ftell( $plugins_list );
				}

				// Increment processed files size
				$processed_files_size += $file_bytes_written;

				// What percent of files have we processed?
				$progress = (int) min( ( $processed_files_size / $total_plugins_files_size ) * 100, 100 );

				// Set progress
				Status::set_status(
					sprintf(
						'Archiving %d plugin files...',
						$total_plugins_files_count,
					),
					35,
					'plugins'
				);

				// More than 10 seconds have passed, break and do another request
				if ( ( $timeout = apply_filters( 'nfd_bhsm_completed_timeout', 10 ) ) ) {
					if ( ( microtime( true ) - $start ) > $timeout ) {
						$completed = false;
						break;
					}
				}
			}

			// Get archive bytes offset
			$archive_bytes_offset = $archive->get_file_pointer();

			// Truncate the archive file
			$archive->truncate();

			// Close the archive file
			$archive->close();

		}
		// End of the plugins list?
		if ( feof( $plugins_list ) ) {

			// Unset archive bytes offset
			unset( $params['archive_bytes_offset'] );

			// Unset file bytes offset
			unset( $params['file_bytes_offset'] );

			// Unset plugins bytes offset
			unset( $params['plugins_bytes_offset'] );

			// Unset processed files size
			unset( $params['processed_files_size'] );

			// Unset total plugins files size
			unset( $params['total_plugins_files_size'] );

			// Unset total plugins files count
			unset( $params['total_plugins_files_count'] );

			// Unset completed flag
			unset( $params['completed'] );

			Status::set_status( 'Done archiving plugins ', 37, 'plugins' );

			self::set_plugins_params( $params );
		} else {

			// Set archive bytes offset
			$params['archive_bytes_offset'] = $archive_bytes_offset;

			// Set file bytes offset
			$params['file_bytes_offset'] = $file_bytes_offset;

			// Set plugins bytes offset
			$params['plugins_bytes_offset'] = $plugins_bytes_offset;

			// Set processed files size
			$params['processed_files_size'] = $processed_files_size;

			// Set total plugins files size
			$params['total_plugins_files_size'] = $total_plugins_files_size;

			// Set total plugins files count
			$params['total_plugins_files_count'] = $total_plugins_files_count;

			// Set completed flag
			$params['completed'] = $completed;

			// Close the plugins list file
			fclose( $plugins_list );

			// Save the file and retry
			self::set_plugins_params( $params );
			self::execute();
		}
	}
}
