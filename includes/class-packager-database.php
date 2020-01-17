<?php

/**
 * Class BH_Move_Database_Packager
 */
class BH_Move_Database_Packager implements BH_Move_Packager {

	/**
	 * Create the database package.
	 *
	 * @return string Path to the package file or an empty string on failure.
	 */
	public function create_package() {

		$package = '';

		$filename = BH_Move_Migration_Package::generate_name( 'db' );
		$zip_path = BH_Move_Utilities::get_upload_path( $filename );

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


}
