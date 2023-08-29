<?php

namespace BluehostSiteMigrator\Packager;

use BluehostSiteMigrator\Archiver\Compressor;
use BluehostSiteMigrator\Utils\Options;
use BluehostSiteMigrator\Utils\Status;

/**
 * Archive the dropins directory
 */
class DropinsArchiver extends PackagerBase {
	/**
	 * Get the parameters for database packaging
	 */
	public static function get_dropins_params() {
		return Options::get( 'dropins_task_params' );
	}

	/**
	 * Set the parameters for database packaging
	 *
	 * @param array $params The updated database params
	 */
	public static function set_dropins_params( $params ) {
		Options::set( 'dropins_task_params', $params );
	}

	/**
	 * Prepare the packaging, populate parameters
	 */
	public static function prepare() {
		$dropin_task_params = self::get_dropins_params();

		// Get total dropins files count
		if ( isset( $dropin_task_params['total_dropins_files_count'] ) ) {
			$total_dropins_files_count = (int) $dropin_task_params['total_dropins_files_count'];
		} else {
			$total_dropins_files_count = 1;
		}

		// Get total dropins files size
		if ( isset( $dropin_task_params['total_dropins_files_size'] ) ) {
			$total_dropins_files_size = (int) $dropin_task_params['total_dropins_files_size'];
		} else {
			$total_dropins_files_size = 1;
		}

		if ( isset( $dropin_task_params['dropins_list_path'] ) ) {
			$dropins_list_file_path = $dropin_task_params['dropins_list_path'];
		} else {
			$dropins_list_file_path                  = nfd_bhsm_get_hashed_file_path( 'dropins', 'config', 'list' );
			$dropin_task_params['dropins_list_path'] = $dropins_list_file_path;
		}

		// Set the progress
		Status::set_status( 'Retrieving a list of WordPress dropin files ...', 80, 'dropins' );

		// Create the dropin details file
		$dropins_list = nfd_bhsm_open( $dropins_list_file_path, 'w' );

		if ( ! function_exists( 'get_dropins' ) ) {
			require ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$dropins = array_keys( get_dropins() );

		if ( $dropins ) {
			foreach ( $dropins as $dropin ) {
				$file_path = nfd_bhsm_replace_forward_slash_with_directory_separator( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $dropin );
				$written   = nfd_bhsm_putcsv(
					$dropins_list,
					array(
						$file_path,
						$dropin,
						filesize( $file_path ),
						filemtime( $file_path ),
					),
				);
				if ( $written ) {
					++$total_dropins_files_count;
					// $total_dropins_files_size += $iterator->getSize();
				}
			}
		}

		Status::set_status( 'Done retrieving a list of WordPress dropin files.', 83, 'dropins' );

		$dropin_task_params['total_dropins_files_count'] = $total_dropins_files_count;

		// Set total dropins files size
		$dropin_task_params['total_dropins_files_size'] = $total_dropins_files_size;

		fclose( $dropins_list );

		self::set_dropins_params( $dropin_task_params );
	}

	/**
	 * Make the dropins package
	 */
	public static function execute() {
		$params = self::get_dropins_params();

		// If the dropin list is not prepared yet, do that.
		if ( ! isset( $params['dropins_list_path'] ) ) {
			self::prepare();
			// Repopulate params
			$params = self::get_dropins_params();
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

		// Set dropins bytes offset
		if ( isset( $params['dropins_bytes_offset'] ) ) {
			$dropins_bytes_offset = (int) $params['dropins_bytes_offset'];
		} else {
			$dropins_bytes_offset = 0;
		}

		// Get processed files size
		if ( isset( $params['processed_files_size'] ) ) {
			$processed_files_size = (int) $params['processed_files_size'];
		} else {
			$processed_files_size = 0;
		}

		// Get total dropins files size
		if ( isset( $params['total_dropins_files_size'] ) ) {
			$total_dropins_files_size = (int) $params['total_dropins_files_size'];
		} else {
			$total_dropins_files_size = 1;
		}

		// Get total dropins files count
		if ( isset( $params['total_dropins_files_count'] ) ) {
			$total_dropins_files_count = (int) $params['total_dropins_files_count'];
		} else {
			$total_dropins_files_count = 1;
		}

		// Set the dropin list file path
		if ( isset( $params['dropins_list_path'] ) ) {
			$dropins_list_path = $params['dropins_list_path'];
		} else {
			$dropins_list_path = '';
		}

		// Get the dropins archive path
		if ( isset( $params['dropins_archive_path'] ) ) {
			$dropins_archive_path = $params['dropins_archive_path'];
		} else {
			$dropins_archive_path = nfd_bhsm_get_hashed_file_path( 'dropins', 'backup', 'zip' );
			// Set the archiver path in params
			$params['dropins_archive_path'] = $dropins_archive_path;
		}

		// What percent of files have we processed?
		$progress = (int) min( ( $processed_files_size / $total_dropins_files_size ) * 100, 100 );

		Status::set_status(
			sprintf(
				'Archiving %d dropin files ... %d%% complete',
				$total_dropins_files_count,
				$progress !== 0 ? $progress : 5
			),
			85,
			'dropins'
		);

		// Flag to hold if data has been processed
		$completed = true;

		// Start time
		$start = microtime( true );

		// Get dropins list file
		$dropins_list = nfd_bhsm_open( $dropins_list_path, 'r' );

		// Set the file pointer at the current
		if ( fseek( $dropins_list, $dropins_bytes_offset ) !== -1 ) {

			// Open the archive file for writing
			$archive = new Compressor( $dropins_archive_path );

			// Set the file pointer to the one that we have saved
			$archive->set_file_pointer( $archive_bytes_offset );

			// Loop over the files
			while ( list($file_abspath, $file_relpath, $file_size, $file_mtime) = fgetcsv( $dropins_list ) ) { // phpcs:ignore
				$file_bytes_written = 0;

				$completed = $archive->add_file( $file_abspath, $file_relpath, $file_bytes_written, $file_bytes_offset );

				// Add file to archive
				if ( $completed ) {
					$file_bytes_offset = 0;

					// Get dropins bytes offset
					$dropins_bytes_offset = ftell( $dropins_list );
				}

				// Increment processed files size
				$processed_files_size += $file_bytes_written;

				// What percent of files have we processed?
				$progress = (int) min( ( $processed_files_size / $total_dropins_files_size ) * 100, 100 );

				// Set progress
				Status::set_status(
					sprintf(
						'Archiving %d dropin files...%d%% complete',
						$total_dropins_files_count,
						$progress
					),
					85,
					'dropins'
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
		// End of the dropins list?
		if ( feof( $dropins_list ) ) {

			// Unset archive bytes offset
			unset( $params['archive_bytes_offset'] );

			// Unset file bytes offset
			unset( $params['file_bytes_offset'] );

			// Unset dropins bytes offset
			unset( $params['dropins_bytes_offset'] );

			// Unset processed files size
			unset( $params['processed_files_size'] );

			// Unset total dropins files size
			unset( $params['total_dropins_files_size'] );

			// Unset total dropins files count
			unset( $params['total_dropins_files_count'] );

			// Unset completed flag
			unset( $params['completed'] );

			Status::set_status( 'Done archiving dropins ', 87, 'dropins' );

			self::set_dropins_params( $params );

			// Return the archive path to be used in the global file options.
			parent::persist_archive_path( $dropins_archive_path, 'dropins' );
		} else {

			// Set archive bytes offset
			$params['archive_bytes_offset'] = $archive_bytes_offset;

			// Set file bytes offset
			$params['file_bytes_offset'] = $file_bytes_offset;

			// Set dropins bytes offset
			$params['dropins_bytes_offset'] = $dropins_bytes_offset;

			// Set processed files size
			$params['processed_files_size'] = $processed_files_size;

			// Set total dropins files size
			$params['total_dropins_files_size'] = $total_dropins_files_size;

			// Set total dropins files count
			$params['total_dropins_files_count'] = $total_dropins_files_count;

			// Set completed flag
			$params['completed'] = $completed;

			// Close the dropins list file
			fclose( $dropins_list );

			// Set progress
			Status::set_status(
				sprintf(
					'Archiving %d dropin files...%d%% complete',
					$total_dropins_files_count,
					$progress
				),
				85,
				'dropins'
			);

			// Save the file and retry
			self::set_dropins_params( $params );
			self::execute();
		}
	}
}
