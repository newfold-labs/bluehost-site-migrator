<?php

namespace BluehostSiteMigrator\Packager;

use BluehostSiteMigrator\Archiver\Compressor;
use BluehostSiteMigrator\Utils\Options;
use BluehostSiteMigrator\Utils\Status;

/**
 * Archive the uploads directory
 */
class UploadsArchiver extends PackagerBase {
	/**
	 * Get the parameters for database packaging
	 */
	public static function get_uploads_params() {
		return Options::get( 'uploads_task_params' );
	}

	/**
	 * Set the parameters for database packaging
	 *
	 * @param array $params The updated database params
	 */
	public static function set_uploads_params( $params ) {
		Options::set( 'uploads_task_params', $params );
	}

	/**
	 * Prepare the packaging, populate parameters
	 */
	public static function prepare() {
		$upload_task_params = self::get_uploads_params();

		// Get total uploads files count
		if ( isset( $upload_task_params['total_uploads_files_count'] ) ) {
			$total_uploads_files_count = (int) $upload_task_params['total_uploads_files_count'];
		} else {
			$total_uploads_files_count = 1;
		}

		// Get total uploads files size
		if ( isset( $upload_task_params['total_uploads_files_size'] ) ) {
			$total_uploads_files_size = (int) $upload_task_params['total_uploads_files_size'];
		} else {
			$total_uploads_files_size = 1;
		}

		if ( isset( $upload_task_params['uploads_list_path'] ) ) {
			$uploads_list_file_path = $upload_task_params['uploads_list_path'];
		} else {
			$uploads_list_file_path                  = nfd_bhsm_get_hashed_file_path( 'uploads', 'config', 'list' );
			$upload_task_params['uploads_list_path'] = $uploads_list_file_path;
		}

		// Exclude the main files from root and only copy anything "extra"
		$exclude_filters = array( BH_SITE_MIGRATOR_PLUGIN_NAME );

		// Set the progress
		Status::set_status( 'Retrieving a list of WordPress upload files ...', 55, 'uploads' );

		// Create the upload details file
		$uploads_list = nfd_bhsm_open( $uploads_list_file_path, 'w' );

		$uploads_dir = nfd_bhsm_uploads_dir();

		if ( is_dir( $uploads_dir ) ) {
			// Enumerate over uploads directory
			$iterator = new \RecursiveDirectoryIterator( $uploads_dir, \FilesystemIterator::SKIP_DOTS );

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
			// Exclude the files that are needed to be ignored
			$iterator = new \RecursiveCallbackFilterIterator( $iterator, $filter );

			$iterator = new \RecursiveIteratorIterator( $iterator );

			// Write path line
			foreach ( $iterator as $item ) {
				if ( $item->isFile() ) {
					$written = nfd_bhsm_putcsv(
						$uploads_list,
						array(
							$iterator->getPathname(),
							$iterator->getSubPathname(),
							$iterator->getSize(),
							$iterator->getMTime(),
						),
					);
					if ( $written ) {
						++$total_uploads_files_count;
						$total_uploads_files_size += $iterator->getSize();
					}
				}
			}
		}

		Status::set_status( 'Done retrieving a list of WordPress upload files.', 58, 'uploads' );

		$upload_task_params['total_uploads_files_count'] = $total_uploads_files_count;

		// Set total uploads files size
		$upload_task_params['total_uploads_files_size'] = $total_uploads_files_size;

		fclose( $uploads_list );

		self::set_uploads_params( $upload_task_params );
	}

	/**
	 * Make the uploads package
	 */
	public static function execute() {
		$params = self::get_uploads_params();

		// If the upload list is not prepared yet, do that.
		if ( ! isset( $params['uploads_list_path'] ) ) {
			self::prepare();
			// Repopulate params
			$params = self::get_uploads_params();
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

		// Set uploads bytes offset
		if ( isset( $params['uploads_bytes_offset'] ) ) {
			$uploads_bytes_offset = (int) $params['uploads_bytes_offset'];
		} else {
			$uploads_bytes_offset = 0;
		}

		// Get processed files size
		if ( isset( $params['processed_files_size'] ) ) {
			$processed_files_size = (int) $params['processed_files_size'];
		} else {
			$processed_files_size = 0;
		}

		// Get total uploads files size
		if ( isset( $params['total_uploads_files_size'] ) ) {
			$total_uploads_files_size = (int) $params['total_uploads_files_size'];
		} else {
			$total_uploads_files_size = 1;
		}

		// Get total uploads files count
		if ( isset( $params['total_uploads_files_count'] ) ) {
			$total_uploads_files_count = (int) $params['total_uploads_files_count'];
		} else {
			$total_uploads_files_count = 1;
		}

		// Set the upload list file path
		if ( isset( $params['uploads_list_path'] ) ) {
			$uploads_list_path = $params['uploads_list_path'];
		} else {
			$uploads_list_path = '';
		}

		// Get the uploads archive path
		if ( isset( $params['uploads_archive_path'] ) ) {
			$uploads_archive_path = $params['uploads_archive_path'];
		} else {
			$uploads_archive_path = nfd_bhsm_get_hashed_file_path( 'uploads', 'backup', 'zip' );
			// Set the archiver path in params
			$params['uploads_archive_path'] = $uploads_archive_path;
		}

		// What percent of files have we processed?
		$progress = (int) min( ( $processed_files_size / $total_uploads_files_size ) * 100, 100 );

		Status::set_status(
			sprintf(
				'Archiving %d upload files ... %d%% complete',
				$total_uploads_files_count,
				$progress !== 0 ? $progress : 5
			),
			60,
			'uploads'
		);

		// Flag to hold if data has been processed
		$completed = true;

		// Start time
		$start = microtime( true );

		// Get uploads list file
		$uploads_list = nfd_bhsm_open( $uploads_list_path, 'r' );

		// Set the file pointer at the current
		if ( fseek( $uploads_list, $uploads_bytes_offset ) !== -1 ) {

			// Open the archive file for writing
			$archive = new Compressor( $uploads_archive_path );

			// Set the file pointer to the one that we have saved
			$archive->set_file_pointer( $archive_bytes_offset );

			// Loop over the files
			while ( list($file_abspath, $file_relpath, $file_size, $file_mtime) = fgetcsv( $uploads_list ) ) { // phpcs:ignore
				$file_bytes_written = 0;

				$completed = $archive->add_file( $file_abspath, $file_relpath, $file_bytes_written, $file_bytes_offset );

				// Add file to archive
				if ( $completed ) {
					$file_bytes_offset = 0;

					// Get uploads bytes offset
					$uploads_bytes_offset = ftell( $uploads_list );
				}

				// Increment processed files size
				$processed_files_size += $file_bytes_written;

				// What percent of files have we processed?
				$progress = (int) min( ( $processed_files_size / $total_uploads_files_size ) * 100, 100 );

				// Set progress
				Status::set_status(
					sprintf(
						'Archiving %d upload files...',
						$total_uploads_files_count,
					),
					60,
					'uploads'
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
		// End of the uploads list?
		if ( feof( $uploads_list ) ) {

			// Unset archive bytes offset
			unset( $params['archive_bytes_offset'] );

			// Unset file bytes offset
			unset( $params['file_bytes_offset'] );

			// Unset uploads bytes offset
			unset( $params['uploads_bytes_offset'] );

			// Unset processed files size
			unset( $params['processed_files_size'] );

			// Unset total uploads files size
			unset( $params['total_uploads_files_size'] );

			// Unset total uploads files count
			unset( $params['total_uploads_files_count'] );

			// Unset completed flag
			unset( $params['completed'] );

			Status::set_status( 'Done archiving uploads ', 62, 'uploads' );

			self::set_uploads_params( $params );

			// Return the archive path to be used in the global file options.
			parent::persist_archive_path( $uploads_archive_path, 'uploads' );
		} else {

			// Set archive bytes offset
			$params['archive_bytes_offset'] = $archive_bytes_offset;

			// Set file bytes offset
			$params['file_bytes_offset'] = $file_bytes_offset;

			// Set uploads bytes offset
			$params['uploads_bytes_offset'] = $uploads_bytes_offset;

			// Set processed files size
			$params['processed_files_size'] = $processed_files_size;

			// Set total uploads files size
			$params['total_uploads_files_size'] = $total_uploads_files_size;

			// Set total uploads files count
			$params['total_uploads_files_count'] = $total_uploads_files_count;

			// Set completed flag
			$params['completed'] = $completed;

			// Close the uploads list file
			fclose( $uploads_list );

			// Set progress
			Status::set_status(
				sprintf(
					'Archiving %d upload files...',
					$total_uploads_files_count,
				),
				60,
				'uploads'
			);

			// Save the file and retry
			self::set_uploads_params( $params );
			self::execute();
		}
	}
}
