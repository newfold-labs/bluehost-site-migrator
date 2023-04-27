<?php

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class BH_Site_Migrator_Database_Packager
 */
class BH_Site_Migrator_Database_Packager implements BH_Site_Migrator_Packager {

	/**
	 * Create the database package.
	 *
	 * @return string Path to the package file or an empty string on failure.
	 */
	public function create_package() {

		$package = '';

		$filename = BH_Site_Migrator_Migration_Package::generate_name( 'db' );
		$zip_path = BH_Site_Migrator_Utilities::get_upload_path( $filename );

		$zip = new ZipArchive();
		if ( true === $zip->open( $zip_path, ZipArchive::CREATE ) ) {
			self::get_sql_dump();
			$exists = $zip->addFile( 'database.sql' ) && $zip->close();

			if ( $exists ) {
				$package = $zip_path;
			}
		}

		return $package;
	}

	/**
	 * Create a MySQL dump of the database.
	 *
	 * @throws ProcessFailedException If process fails.
	 */
	public static function get_sql_dump() {
		global $wpdb;

		$db_name  = DB_NAME;
		$password = DB_PASSWORD;
		$host     = DB_HOST;
		$user     = DB_USER;

		$process_command_array = array(
			'mysqldump',
			$db_name,
			'--user=' . $user,
			'--password=' . $password,
			'--host=' . $host,
			'--max_allowed_packet=512M',
			'--result-file=database.sql',
		);

		$required_tables = $wpdb->get_results( 'SHOW TABLE STATUS' );
		foreach ( $required_tables as $table_datum ) {
			if ( ! preg_match( '#^' . preg_quote( $wpdb->prefix, '#' ) . '#', $table_datum->{'Name'} ) ) {
				array_push( $process_command_array, '--ignore_table=' . $db_name . '.' . $table_datum->{'Name'} );
			}
		}

		$process = new Process(
			$process_command_array,
			null,
			null,
			null,
			300
		);
		$process->run();

		if ( ! $process->isSuccessful() ) {
			throw new ProcessFailedException( $process );
		}
	}

	/**
	 * Validate whether or not the generated package is still valid.
	 *
	 * @param array $data Package data (e.g. hash, path, size, timestamp, url)
	 *
	 * @return bool
	 */
	public function is_package_valid( array $data ) {

		// Check if database has modified posts
		$query = new WP_Query(
			array(
				'post_type'      => 'any',
				'post_status'    => 'any',
				'date_query'     => array(
					'column' => 'post_modified_gmt',
					'after'  => array(
						'year'  => gmdate( 'Y', $data['timestamp'] ),
						'month' => gmdate( 'n', $data['timestamp'] ),
						'day'   => gmdate( 'j', $data['timestamp'] ),
					),
				),
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return ! boolval( $query->post_count );
	}

}
