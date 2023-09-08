<?php

namespace BluehostSiteMigrator\Packager;

use BluehostSiteMigrator\Archiver\Compressor;
use BluehostSiteMigrator\Utils\Options;
use BluehostSiteMigrator\Utils\Status;

/**
 * Archive the mu_plugins directory
 */
class MuPluginsArchiver extends PackagerBase {
	/**
	 * Get the parameters for database packaging
	 */
	public static function get_mu_plugins_params() {
		return Options::get( 'mu_plugins_task_params' );
	}

	/**
	 * Set the parameters for database packaging
	 *
	 * @param array $params The updated database params
	 */
	public static function set_mu_plugins_params( $params ) {
		Options::set( 'mu_plugins_task_params', $params );
	}

	/**
	 * Prepare the packaging, populate parameters
	 */
	public static function prepare() {
		$mu_plugin_task_params = self::get_mu_plugins_params();

		// Get total mu_plugins files count
		if ( isset( $mu_plugin_task_params['total_mu_plugins_files_count'] ) ) {
			$total_mu_plugins_files_count = (int) $mu_plugin_task_params['total_mu_plugins_files_count'];
		} else {
			$total_mu_plugins_files_count = 1;
		}

		// Get total mu_plugins files size
		if ( isset( $mu_plugin_task_params['total_mu_plugins_files_size'] ) ) {
			$total_mu_plugins_files_size = (int) $mu_plugin_task_params['total_mu_plugins_files_size'];
		} else {
			$total_mu_plugins_files_size = 1;
		}

		if ( isset( $mu_plugin_task_params['mu_plugins_list_path'] ) ) {
			$mu_plugins_list_file_path = $mu_plugin_task_params['mu_plugins_list_path'];
		} else {
			$mu_plugins_list_file_path                     = nfd_bhsm_get_hashed_file_path( 'mu_plugins', 'config', 'list' );
			$mu_plugin_task_params['mu_plugins_list_path'] = $mu_plugins_list_file_path;
		}

		// Set the progress
		Status::set_status( __( 'Retrieving a list of WordPress mu_plugin files ...', 'bluehost-site-migrator' ), 66, 'mu_plugins' );

		// Create the mu_plugin details file
		$mu_plugins_list = nfd_bhsm_open( $mu_plugins_list_file_path, 'w' );

		$mu_plugins_dir = nfd_bhsm_mu_plugins_dir();

		if ( is_dir( $mu_plugins_dir ) ) {
			// Enumerate over mu_plugins directory
			$iterator = new \RecursiveDirectoryIterator( $mu_plugins_dir, \FilesystemIterator::SKIP_DOTS );

			$iterator = new \RecursiveIteratorIterator( $iterator );

			// Write path line
			foreach ( $iterator as $item ) {
				if ( $item->isFile() ) {
					$written = nfd_bhsm_putcsv(
						$mu_plugins_list,
						array(
							$iterator->getPathname(),
							$iterator->getSubPathname(),
							$iterator->getSize(),
							$iterator->getMTime(),
						)
					);
					if ( $written ) {
						++$total_mu_plugins_files_count;
						$total_mu_plugins_files_size += $iterator->getSize();
					}
				}
			}
		}

		Status::set_status(
			__( 'Done retrieving a list of WordPress mu_plugin files.', 'bluehost-site-migrator' ),
			69,
			'mu_plugins'
		);

		$mu_plugin_task_params['total_mu_plugins_files_count'] = $total_mu_plugins_files_count;

		// Set total mu_plugins files size
		$mu_plugin_task_params['total_mu_plugins_files_size'] = $total_mu_plugins_files_size;

		fclose( $mu_plugins_list );

		self::set_mu_plugins_params( $mu_plugin_task_params );
	}

	/**
	 * Make the mu_plugins package
	 */
	public static function execute() {
		$params = self::get_mu_plugins_params();

		// If the mu_plugin list is not prepared yet, do that.
		if ( ! isset( $params['mu_plugins_list_path'] ) ) {
			self::prepare();
			// Repopulate params
			$params = self::get_mu_plugins_params();
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

		// Set mu_plugins bytes offset
		if ( isset( $params['mu_plugins_bytes_offset'] ) ) {
			$mu_plugins_bytes_offset = (int) $params['mu_plugins_bytes_offset'];
		} else {
			$mu_plugins_bytes_offset = 0;
		}

		// Get processed files size
		if ( isset( $params['processed_files_size'] ) ) {
			$processed_files_size = (int) $params['processed_files_size'];
		} else {
			$processed_files_size = 0;
		}

		// Get total mu_plugins files size
		if ( isset( $params['total_mu_plugins_files_size'] ) ) {
			$total_mu_plugins_files_size = (int) $params['total_mu_plugins_files_size'];
		} else {
			$total_mu_plugins_files_size = 1;
		}

		// Get total mu_plugins files count
		if ( isset( $params['total_mu_plugins_files_count'] ) ) {
			$total_mu_plugins_files_count = (int) $params['total_mu_plugins_files_count'];
		} else {
			$total_mu_plugins_files_count = 1;
		}

		// Set the mu_plugin list file path
		if ( isset( $params['mu_plugins_list_path'] ) ) {
			$mu_plugins_list_path = $params['mu_plugins_list_path'];
		} else {
			$mu_plugins_list_path = '';
		}

		// Get the mu_plugins archive path
		if ( isset( $params['mu_plugins_archive_path'] ) ) {
			$mu_plugins_archive_path = $params['mu_plugins_archive_path'];
		} else {
			$mu_plugins_archive_path = nfd_bhsm_get_hashed_file_path( 'mu_plugins', 'backup', 'zip' );
			// Set the archiver path in params
			$params['mu_plugins_archive_path'] = $mu_plugins_archive_path;
		}

		// What percent of files have we processed?
		$progress = (int) min( ( $processed_files_size / $total_mu_plugins_files_size ) * 100, 100 );

		Status::set_status(
			sprintf(
				// translators: %d: total mu plugins file count
				esc_html__( 'Archiving %d mu_plugin files ...', 'bluehost-site-migrator' ),
				esc_xml( $total_mu_plugins_files_count )
			),
			72,
			'mu_plugins'
		);

		// Flag to hold if data has been processed
		$completed = true;

		// Start time
		$start = microtime( true );

		// Get mu_plugins list file
		$mu_plugins_list = nfd_bhsm_open( $mu_plugins_list_path, 'r' );

		// Set the file pointer at the current
		if ( fseek( $mu_plugins_list, $mu_plugins_bytes_offset ) !== -1 ) {

			// Open the archive file for writing
			$archive = new Compressor( $mu_plugins_archive_path );

			// Set the file pointer to the one that we have saved
			$archive->set_file_pointer( $archive_bytes_offset );

			// Loop over the files
			while ( list($file_abspath, $file_relpath, $file_size, $file_mtime) = fgetcsv( $mu_plugins_list ) ) { // phpcs:ignore
				$file_bytes_written = 0;

				$completed = $archive->add_file( $file_abspath, $file_relpath, $file_bytes_written, $file_bytes_offset );

				// Add file to archive
				if ( $completed ) {
					$file_bytes_offset = 0;

					// Get mu_plugins bytes offset
					$mu_plugins_bytes_offset = ftell( $mu_plugins_list );
				}

				// Increment processed files size
				$processed_files_size += $file_bytes_written;

				// What percent of files have we processed?
				$progress = (int) min( ( $processed_files_size / $total_mu_plugins_files_size ) * 100, 100 );

				// Set progress
				Status::set_status(
					sprintf(
					// translators: %d: total mu plugins file count
						esc_html__( 'Archiving %d mu_plugin files ...', 'bluehost-site-migrator' ),
						esc_xml( $total_mu_plugins_files_count )
					),
					72,
					'mu_plugins'
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
		// End of the mu_plugins list?
		if ( feof( $mu_plugins_list ) ) {

			// Unset archive bytes offset
			unset( $params['archive_bytes_offset'] );

			// Unset file bytes offset
			unset( $params['file_bytes_offset'] );

			// Unset mu_plugins bytes offset
			unset( $params['mu_plugins_bytes_offset'] );

			// Unset processed files size
			unset( $params['processed_files_size'] );

			// Unset total mu_plugins files size
			unset( $params['total_mu_plugins_files_size'] );

			// Unset total mu_plugins files count
			unset( $params['total_mu_plugins_files_count'] );

			// Unset completed flag
			unset( $params['completed'] );

			Status::set_status( __( 'Done archiving mu_plugins ', 'bluehost-site-migrator' ), 75, 'mu_plugins' );

			self::set_mu_plugins_params( $params );

			// Return the archive path to be used in the global file options.
			parent::persist_archive_path( $mu_plugins_archive_path, 'mu-plugins' );
		} else {

			// Set archive bytes offset
			$params['archive_bytes_offset'] = $archive_bytes_offset;

			// Set file bytes offset
			$params['file_bytes_offset'] = $file_bytes_offset;

			// Set mu_plugins bytes offset
			$params['mu_plugins_bytes_offset'] = $mu_plugins_bytes_offset;

			// Set processed files size
			$params['processed_files_size'] = $processed_files_size;

			// Set total mu_plugins files size
			$params['total_mu_plugins_files_size'] = $total_mu_plugins_files_size;

			// Set total mu_plugins files count
			$params['total_mu_plugins_files_count'] = $total_mu_plugins_files_count;

			// Set completed flag
			$params['completed'] = $completed;

			// Close the mu_plugins list file
			fclose( $mu_plugins_list );

			// Set progress
			Status::set_status(
				sprintf(
				// translators: %d: total mu plugins file count
					esc_html__( 'Archiving %d mu_plugin files ...', 'bluehost-site-migrator' ),
					esc_xml( $total_mu_plugins_files_count )
				),
				72,
				'mu_plugins'
			);

			// Save the file and retry
			self::set_mu_plugins_params( $params );
			self::execute();
		}
	}
}
