<?php

namespace BluehostSiteMigrator\Packager;

use BluehostSiteMigrator\Archiver\Compressor;
use BluehostSiteMigrator\Utils\Options;
use BluehostSiteMigrator\Utils\Status;

/**
 * Archive the themes directory
 */
class ThemesArchiver extends PackagerBase {
	/**
	 * Get the parameters for database packaging
	 */
	public static function get_themes_params() {
		return Options::get( 'themes_task_params' );
	}

	/**
	 * Set the parameters for database packaging
	 *
	 * @param array $params The updated database params
	 */
	public static function set_themes_params( $params ) {
		Options::set( 'themes_task_params', $params );
	}

	/**
	 * Prepare the packaging, populate parameters
	 */
	public static function prepare() {
		$theme_task_params = self::get_themes_params();

		// Get total themes files count
		if ( isset( $theme_task_params['total_themes_files_count'] ) ) {
			$total_themes_files_count = (int) $theme_task_params['total_themes_files_count'];
		} else {
			$total_themes_files_count = 1;
		}

		// Get total themes files size
		if ( isset( $theme_task_params['total_themes_files_size'] ) ) {
			$total_themes_files_size = (int) $theme_task_params['total_themes_files_size'];
		} else {
			$total_themes_files_size = 1;
		}

		if ( isset( $theme_task_params['themes_list_path'] ) ) {
			$themes_list_file_path = $theme_task_params['themes_list_path'];
		} else {
			$themes_list_file_path                 = nfd_bhsm_get_hashed_file_path( 'themes', 'config', 'list' );
			$theme_task_params['themes_list_path'] = $themes_list_file_path;
		}

		// Set the progress
		Status::set_status(
			__( 'Retrieving a list of WordPress theme files ...', 'bluehost-site-migrator' ),
			40,
			'themes'
		);

		// Create the theme details file
		$themes_list = nfd_bhsm_open( $themes_list_file_path, 'w' );

		$themes_dirs = nfd_bhsm_themes_dir();

		foreach ( $themes_dirs as $theme_dir ) {
			if ( is_dir( $theme_dir ) ) {
				// Enumerate over themes directory
				$iterator = new \RecursiveDirectoryIterator( $theme_dir, \FilesystemIterator::SKIP_DOTS );

				$iterator = new \RecursiveIteratorIterator( $iterator );

				// Write path line
				foreach ( $iterator as $item ) {
					if ( $item->isFile() ) {
						$written = nfd_bhsm_putcsv(
							$themes_list,
							array(
								$iterator->getPathname(),
								$iterator->getSubPathname(),
								$iterator->getSize(),
								$iterator->getMTime(),
							)
						);
						if ( $written ) {
							++$total_themes_files_count;
							$total_themes_files_size += $iterator->getSize();
						}
					}
				}
			}
		}

		Status::set_status(
			__( 'Done retrieving a list of WordPress theme files.', 'bluehost-site-migrator' ),
			42,
			'themes'
		);

		$theme_task_params['total_themes_files_count'] = $total_themes_files_count;

		// Set total themes files size
		$theme_task_params['total_themes_files_size'] = $total_themes_files_size;

		fclose( $themes_list );

		self::set_themes_params( $theme_task_params );
	}

	/**
	 * Make the themes package
	 */
	public static function execute() {
		$params = self::get_themes_params();

		// If the theme list is not prepared yet, do that.
		if ( ! isset( $params['themes_list_path'] ) ) {
			self::prepare();
			// Repopulate params
			$params = self::get_themes_params();
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

		// Set themes bytes offset
		if ( isset( $params['themes_bytes_offset'] ) ) {
			$themes_bytes_offset = (int) $params['themes_bytes_offset'];
		} else {
			$themes_bytes_offset = 0;
		}

		// Get processed files size
		if ( isset( $params['processed_files_size'] ) ) {
			$processed_files_size = (int) $params['processed_files_size'];
		} else {
			$processed_files_size = 0;
		}

		// Get total themes files size
		if ( isset( $params['total_themes_files_size'] ) ) {
			$total_themes_files_size = (int) $params['total_themes_files_size'];
		} else {
			$total_themes_files_size = 1;
		}

		// Get total themes files count
		if ( isset( $params['total_themes_files_count'] ) ) {
			$total_themes_files_count = (int) $params['total_themes_files_count'];
		} else {
			$total_themes_files_count = 1;
		}

		// Set the theme list file path
		if ( isset( $params['themes_list_path'] ) ) {
			$themes_list_path = $params['themes_list_path'];
		} else {
			$themes_list_path = '';
		}

		// Get the themes archive path
		if ( isset( $params['themes_archive_path'] ) ) {
			$themes_archive_path = $params['themes_archive_path'];
		} else {
			$themes_archive_path = nfd_bhsm_get_hashed_file_path( 'themes', 'backup', 'zip' );
			// Set the archiver path in params
			$params['themes_archive_path'] = $themes_archive_path;
		}

		// What percent of files have we processed?
		$progress = (int) min( ( $processed_files_size / $total_themes_files_size ) * 100, 100 );

		Status::set_status(
			sprintf(
				// translators: %d: total mu plugins file count
				esc_html__( 'Archiving %d theme files ...', 'bluehost-site-migrator' ),
				esc_xml( $total_themes_files_count )
			),
			45,
			'themes'
		);

		// Flag to hold if data has been processed
		$completed = true;

		// Start time
		$start = microtime( true );

		// Get themes list file
		$themes_list = nfd_bhsm_open( $themes_list_path, 'r' );

		// Set the file pointer at the current
		if ( fseek( $themes_list, $themes_bytes_offset ) !== -1 ) {

			// Open the archive file for writing
			$archive = new Compressor( $themes_archive_path );

			// Set the file pointer to the one that we have saved
			$archive->set_file_pointer( $archive_bytes_offset );

			// Loop over the files
			while ( list($file_abspath, $file_relpath, $file_size, $file_mtime) = fgetcsv( $themes_list ) ) { // phpcs:ignore
				$file_bytes_written = 0;

				$completed = $archive->add_file( $file_abspath, $file_relpath, $file_bytes_written, $file_bytes_offset );

				// Add file to archive
				if ( $completed ) {
					$file_bytes_offset = 0;

					// Get themes bytes offset
					$themes_bytes_offset = ftell( $themes_list );
				}

				// Increment processed files size
				$processed_files_size += $file_bytes_written;

				// What percent of files have we processed?
				$progress = (int) min( ( $processed_files_size / $total_themes_files_size ) * 100, 100 );

				// Set progress
				Status::set_status(
					sprintf(
					// translators: %d: total mu plugins file count
						esc_html__( 'Archiving %d theme files ...', 'bluehost-site-migrator' ),
						esc_xml( $total_themes_files_count )
					),
					45,
					'themes'
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
		// End of the themes list?
		if ( feof( $themes_list ) ) {

			// Unset archive bytes offset
			unset( $params['archive_bytes_offset'] );

			// Unset file bytes offset
			unset( $params['file_bytes_offset'] );

			// Unset themes bytes offset
			unset( $params['themes_bytes_offset'] );

			// Unset processed files size
			unset( $params['processed_files_size'] );

			// Unset total themes files size
			unset( $params['total_themes_files_size'] );

			// Unset total themes files count
			unset( $params['total_themes_files_count'] );

			// Unset completed flag
			unset( $params['completed'] );

			Status::set_status( __( 'Done archiving themes ', 'bluehost-site-migrator' ), 50, 'themes' );

			self::set_themes_params( $params );

			// Return the archive path to be used in the global file options.
			parent::persist_archive_path( $themes_archive_path, 'themes' );
		} else {

			// Set archive bytes offset
			$params['archive_bytes_offset'] = $archive_bytes_offset;

			// Set file bytes offset
			$params['file_bytes_offset'] = $file_bytes_offset;

			// Set themes bytes offset
			$params['themes_bytes_offset'] = $themes_bytes_offset;

			// Set processed files size
			$params['processed_files_size'] = $processed_files_size;

			// Set total themes files size
			$params['total_themes_files_size'] = $total_themes_files_size;

			// Set total themes files count
			$params['total_themes_files_count'] = $total_themes_files_count;

			// Set completed flag
			$params['completed'] = $completed;

			// Close the themes list file
			fclose( $themes_list );

			// Set progress
			Status::set_status(
				sprintf(
				// translators: %d: total mu plugins file count
					esc_html__( 'Archiving %d theme files ...', 'bluehost-site-migrator' ),
					esc_xml( $total_themes_files_count )
				),
				45,
				'themes'
			);

			// Save the file and retry
			self::set_themes_params( $params );
			self::execute();
		}
	}
}
