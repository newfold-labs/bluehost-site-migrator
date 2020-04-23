<?php

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
			$exists = $zip->addFromString( 'database.sql', self::get_sql_dump() ) && $zip->close();

			if ( $exists ) {
				$package = $zip_path;
			}
		}

		return $package;
	}

	/**
	 * Create a MySQL dump of the database.
	 *
	 * @return string
	 */
	public static function get_sql_dump() {
		global $wpdb;

		$db_name    = DB_NAME;
		$table_list = implode( ' ', $wpdb->tables() );

		return `mysqldump {$db_name} {$table_list}`; // phpcs:ignore
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
