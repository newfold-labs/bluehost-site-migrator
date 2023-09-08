<?php

namespace BluehostSiteMigrator\Packager;

use BluehostSiteMigrator\Archiver\Compressor;
use BluehostSiteMigrator\Utils\Options;
use BluehostSiteMigrator\Utils\Status;

/**
 * Archive the root directory
 */
class RootArchiver extends PackagerBase {
	/**
	 * Get the parameters for database packaging
	 */
	public static function get_root_params() {
		return Options::get( 'root_task_params' );
	}

	/**
	 * Set the parameters for database packaging
	 *
	 * @param array $params The updated database params
	 */
	public static function set_root_params( $params ) {
		Options::set( 'root_task_params', $params );
	}

	/**
	 * Prepare the packaging, populate parameters
	 */
	public static function prepare() {
		$root_task_params = self::get_root_params();

		// Get total root files count
		if ( isset( $root_task_params['total_root_files_count'] ) ) {
			$total_root_files_count = (int) $root_task_params['total_root_files_count'];
		} else {
			$total_root_files_count = 1;
		}

		// Get total root files size
		if ( isset( $root_task_params['total_root_files_size'] ) ) {
			$total_root_files_size = (int) $root_task_params['total_root_files_size'];
		} else {
			$total_root_files_size = 1;
		}

		if ( isset( $root_task_params['root_list_path'] ) ) {
			$root_list_file_path = $root_task_params['root_list_path'];
		} else {
			$root_list_file_path                = nfd_bhsm_get_hashed_file_path( 'root', 'config', 'list' );
			$root_task_params['root_list_path'] = $root_list_file_path;
		}

		// Exclude the main files from root and only copy anything "extra"
		$exclude_filters = array(
			ABSPATH . 'index.php',
			ABSPATH . 'license.txt',
			ABSPATH . 'readme.html',
			ABSPATH . 'wp-activate.php',
			ABSPATH . 'wp-blog-header.php',
			ABSPATH . 'wp-comments-post.php',
			ABSPATH . 'wp-config-sample.php',
			ABSPATH . 'wp-config.php',
			ABSPATH . 'wp-cron.php',
			ABSPATH . 'wp-links-opml.php',
			ABSPATH . 'wp-load.php',
			ABSPATH . 'wp-login.php',
			ABSPATH . 'wp-mail.php',
			ABSPATH . 'wp-settings.php',
			ABSPATH . 'wp-signup.php',
			ABSPATH . 'wp-trackback.php',
			ABSPATH . 'xmlrpc.php',
		);

		// Set the progress
		Status::set_status(
			__( 'Retrieving a list of WordPress root files ...', 'bluehost-site-migrator' ),
			90,
			'root'
		);

		// Create the root details file
		$root_list = nfd_bhsm_open( $root_list_file_path, 'w' );

		$root_dir = nfd_bhsm_root_dir();

		if ( is_dir( $root_dir ) ) {
			// Enumerate over root directory
			$iterator = new \RecursiveDirectoryIterator( $root_dir, \FilesystemIterator::SKIP_DOTS );

			// Define the directory filter
			$filter = function ( $file, $key, $iterator ) use ( $exclude_filters ) {
				if ( $iterator->hasChildren() ) {
					return false;
				}
				if ( in_array( $file->getFilename(), $exclude_filters, true ) ) {
					return false;
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
						$root_list,
						array(
							$iterator->getPathname(),
							$iterator->getSubPathname(),
							$iterator->getSize(),
							$iterator->getMTime(),
						)
					);
					if ( $written ) {
						++$total_root_files_count;
						$total_root_files_size += $iterator->getSize();
					}
				}
			}
		}

		Status::set_status( 'Done retrieving a list of WordPress root files.', 92, 'root' );

		$root_task_params['total_root_files_count'] = $total_root_files_count;

		// Set total root files size
		$root_task_params['total_root_files_size'] = $total_root_files_size;

		fclose( $root_list );

		self::set_root_params( $root_task_params );
	}

	/**
	 * Make the root package
	 */
	public static function execute() {
		$params = self::get_root_params();

		// If the root list is not prepared yet, do that.
		if ( ! isset( $params['root_list_path'] ) ) {
			self::prepare();
			// Repopulate params
			$params = self::get_root_params();
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

		// Set root bytes offset
		if ( isset( $params['root_bytes_offset'] ) ) {
			$root_bytes_offset = (int) $params['root_bytes_offset'];
		} else {
			$root_bytes_offset = 0;
		}

		// Get processed files size
		if ( isset( $params['processed_files_size'] ) ) {
			$processed_files_size = (int) $params['processed_files_size'];
		} else {
			$processed_files_size = 0;
		}

		// Get total root files size
		if ( isset( $params['total_root_files_size'] ) ) {
			$total_root_files_size = (int) $params['total_root_files_size'];
		} else {
			$total_root_files_size = 1;
		}

		// Get total root files count
		if ( isset( $params['total_root_files_count'] ) ) {
			$total_root_files_count = (int) $params['total_root_files_count'];
		} else {
			$total_root_files_count = 1;
		}

		// Set the root list file path
		if ( isset( $params['root_list_path'] ) ) {
			$root_list_path = $params['root_list_path'];
		} else {
			$root_list_path = '';
		}

		// Get the root archive path
		if ( isset( $params['root_archive_path'] ) ) {
			$root_archive_path = $params['root_archive_path'];
		} else {
			$root_archive_path = nfd_bhsm_get_hashed_file_path( 'root', 'backup', 'zip' );
			// Set the archiver path in params
			$params['root_archive_path'] = $root_archive_path;
		}

		// What percent of files have we processed?
		$progress = (int) min( ( $processed_files_size / $total_root_files_size ) * 100, 100 );

		// Set progress
		Status::set_status(
			sprintf(
				// translators: %d: total mu plugins file count
				esc_html__( 'Archiving %d root files ... ', 'bluehost-site-migrator' ),
				esc_xml( $total_root_files_count )
			),
			95,
			'root'
		);

		// Flag to hold if data has been processed
		$completed = true;

		// Start time
		$start = microtime( true );

		// Get root list file
		$root_list = nfd_bhsm_open( $root_list_path, 'r' );

		// Set the file pointer at the current
		if ( fseek( $root_list, $root_bytes_offset ) !== -1 ) {

			// Open the archive file for writing
			$archive = new Compressor( $root_archive_path );

			// Set the file pointer to the one that we have saved
			$archive->set_file_pointer( $archive_bytes_offset );

			// Loop over the files
			while ( list($file_abspath, $file_relpath, $file_size, $file_mtime) = fgetcsv( $root_list ) ) { // phpcs:ignore
				$file_bytes_written = 0;

				$completed = $archive->add_file( $file_abspath, $file_relpath, $file_bytes_written, $file_bytes_offset );

				// Add file to archive
				if ( $completed ) {
					$file_bytes_offset = 0;

					// Get root bytes offset
					$root_bytes_offset = ftell( $root_list );
				}

				// Increment processed files size
				$processed_files_size += $file_bytes_written;

				// What percent of files have we processed?
				$progress = (int) min( ( $processed_files_size / $total_root_files_size ) * 100, 100 );

				// Set progress
				Status::set_status(
					sprintf(
					// translators: %d: total mu plugins file count
						esc_html__( 'Archiving %d root files ... ', 'bluehost-site-migrator' ),
						esc_xml( $total_root_files_count )
					),
					95,
					'root'
				);

				// More than 10 seconds have passed, break and do another request
				$timeout = apply_filters( 'nfd_bhsm_completed_timeout', 10 );
				if ( $timeout ) {
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
		// End of the root list?
		if ( feof( $root_list ) ) {

			// Unset archive bytes offset
			unset( $params['archive_bytes_offset'] );

			// Unset file bytes offset
			unset( $params['file_bytes_offset'] );

			// Unset root bytes offset
			unset( $params['root_bytes_offset'] );

			// Unset processed files size
			unset( $params['processed_files_size'] );

			// Unset total root files size
			unset( $params['total_root_files_size'] );

			// Unset total root files count
			unset( $params['total_root_files_count'] );

			// Unset completed flag
			unset( $params['completed'] );

			Status::set_status( __( 'Done archiving root ', 'bluehost-site-migrator' ), 99, 'root' );

			// Set the next stage of things
			Status::set_packaging_success( true );

			self::set_root_params( $params );

			// Return the archive path to be used in the global file options.
			parent::persist_archive_path( $root_archive_path, 'root' );
		} else {

			// Set archive bytes offset
			$params['archive_bytes_offset'] = $archive_bytes_offset;

			// Set file bytes offset
			$params['file_bytes_offset'] = $file_bytes_offset;

			// Set root bytes offset
			$params['root_bytes_offset'] = $root_bytes_offset;

			// Set processed files size
			$params['processed_files_size'] = $processed_files_size;

			// Set total root files size
			$params['total_root_files_size'] = $total_root_files_size;

			// Set total root files count
			$params['total_root_files_count'] = $total_root_files_count;

			// Set completed flag
			$params['completed'] = $completed;

			// Close the root list file
			fclose( $root_list );

			// Set progress
			Status::set_status(
				sprintf(
				// translators: %d: total mu plugins file count
					esc_html__( 'Archiving %d root files ... ', 'bluehost-site-migrator' ),
					esc_xml( $total_root_files_count )
				),
				95,
				'root'
			);

			// Save the file and retry
			self::set_root_params( $params );
			self::execute();
		}
	}
}
