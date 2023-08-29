<?php

namespace BluehostSiteMigrator\Packager;

use BluehostSiteMigrator\Archiver\Compressor;
use BluehostSiteMigrator\Utils\Options;
use BluehostSiteMigrator\Utils\Status;

/**
 * Zip's the database dump created in the previous task.
 */
class DatabaseArchiver extends PackagerBase {
	/**
	 * Get the parameters for database packaging
	 */
	public static function get_database_params() {
		return Options::get( 'database_task_params' );
	}

	/**
	 * Set the parameters for database packaging
	 *
	 * @param array $params The updated database params
	 */
	public static function set_database_params( $params ) {
		Options::set( 'database_task_params', $params );
	}

	/**
	 * Create the archive
	 */
	public static function execute() {
		$params                 = self::get_database_params();
		$database_bytes_written = 0;

		// Set archive bytes offset
		if ( isset( $params['archive_bytes_offset'] ) ) {
			$archive_bytes_offset = (int) $params['archive_bytes_offset'];
		} else {
			$archive_bytes_offset = 0;
		}

		// Set database bytes offset
		if ( isset( $params['database_bytes_offset'] ) ) {
			$database_bytes_offset = (int) $params['database_bytes_offset'];
		} else {
			$database_bytes_offset = 0;
		}

		// Get the database dump path
		if ( isset( $params['database_dump_file_path'] ) ) {
			$database_dump_path = $params['database_dump_file_path'];
		} else {
			throw new \Exception( 'Unable to find the database dump' );
		}

		// Get total database size
		if ( isset( $params['total_database_size'] ) ) {
			$total_database_size = (int) $params['total_database_size'];
		} else {
			$total_database_size = filesize( $database_dump_path );
		}

		// Get the database archive path
		if ( isset( $params['database_archive_path'] ) ) {
			$database_archive_path = $params['database_archive_path'];
		} else {
			$database_archive_path           = nfd_bhsm_get_hashed_file_path( 'database', 'backup', 'zip' );
			$params['database_archive_path'] = $database_archive_path;
		}

		// What percent of database have we processed?
		$progress = (int) min( ( $database_bytes_offset / $total_database_size ) * 100, 100 );
		Status::set_status( sprintf( 'Archiving database ... %d%% complete', $progress ), 15, 'database' );

		// Open the archive file for writing
		$archive = new Compressor( $database_archive_path );

		// Set the file pointer to the one we have saved
		$archive->set_file_pointer( $archive_bytes_offset );

		// Add the sql file to this archive
		$completed = $archive->add_file( $database_dump_path, 'database.sql', $database_bytes_written, $database_bytes_offset );

		if ( $completed ) {
			Status::set_status( 'Done archiving the database', 25, 'database' );

			// Unset archive bytes offset
			unset( $params['archive_bytes_offset'] );

			// Unset database bytes offset
			unset( $params['database_bytes_offset'] );

			// Unset total database size
			unset( $params['total_database_size'] );

			// Unset completed flag
			unset( $params['completed'] );

			// Persist the params and try again after some time
			self::set_database_params( $params );

			// Return the archive path to be stored in a global option
			parent::persist_archive_path( $database_archive_path, 'database' );
		} else {
			// Get archive bytes offset
			$archive_bytes_offset = $archive->get_file_pointer();

			// What percent of database have we processed?
			$progress = (int) min( ( $database_bytes_offset / $total_database_size ) * 100, 100 );

			// Set progress
			Status::set_status( sprintf( 'Archiving database...<br />%d%% complete', $progress ), 18, 'database' );

			// Set archive bytes offset
			$params['archive_bytes_offset'] = $archive_bytes_offset;

			// Set database bytes offset
			$params['database_bytes_offset'] = $database_bytes_offset;

			// Set total database size
			$params['total_database_size'] = $total_database_size;

			// Set completed flag
			$params['completed'] = false;

			// Truncate the archive file
			$archive->truncate();

			// Close the archive file
			$archive->close();

			// Persist the params and try again after some time
			self::set_database_params( $params );
			self::execute();
		}
	}
}
