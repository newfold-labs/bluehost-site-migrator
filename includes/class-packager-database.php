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
			$exists = $zip->addFromString( 'database.sql', self::get_sql_dump() ) && $zip->close();

			if ( $exists ) {
				$package = $zip_path;
			}
		}

		return $package;
	}

	 /**
         * Raw PHP MySQL dump of the database. Does not rely on mysqldump or Symfony Process Component
         *
         * @return string
         */
	public static function get_sql_dump_legacy(){
       		global $wpdb;
	        $output="";
	        $tables=$wpdb->get_col("show tables");
	        foreach ($tables as $table) {
	        	$result = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_N);
			$row2 = $wpdb->get_row('SHOW CREATE TABLE ' . $table, ARRAY_N);
	        	$output .= "\n\n" . $row2[1] . ";\n\n";
			for ($i = 0; $i < count($result); $i++) {
	           		$row = $result[$i];
	           		$output .= 'insert into ' . $table . ' VALUES(';
	           		for ($j = 0; $j < count($result[0]); $j++) {
	           			$row[$j] = $wpdb->_real_escape($row[$j]);
	           			$output .= (isset($row[$j])) ? '"' . $row[$j] . '"' : '""';
	                		if ($j < (count($result[0]) - 1)) {
	                 			$output .= ',';
	                		}
	            		}

	            		$output .= ");\n";
	        	}

	        	$output .= "\n";
		}
	            
	    return $output;       
	}

	/**
	 * Create a MySQL dump of the database.
	 *
	 * If can_mysqldump is false or fails use Legacy sql dump
	 *
	 * @return string
	 */
	public static function get_sql_dump() {
		if(BH_Site_Migrator_Migration_Checks::can_mysqldump(true)){
			$db_name  = DB_NAME;
			$password = DB_PASSWORD;
			$host     = DB_HOST;
			$user     = DB_USER;

			$process = new Process( "mysqldump {$db_name} --user={$user} --password='{$password}' --host={$host}" );
			$process->run();

			if ( $process->isSuccessful() ) {
				$output=$process->getOutput();
			}
        		else {
		                $output=BH_Site_Migrator_Database_Packager::get_sql_dump_legacy();
		        }
		}

		else{
                $output=BH_Site_Migrator_Database_Packager::get_sql_dump_legacy();
             }
		return $output;
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
