<?php

namespace BluehostSiteMigrator\Packager;

/**
 * Class to package the database for a site
 */
class Database extends PackagerBase {

	/**
	 * The path for generated archive.
	 *
	 * @var string
	 */
	protected $archive_path;

	/**
	 * Constructor to specify the package name, and priority
	 */
	public function __construct() {
		$this->name         = 'db';
		$this->archive_path = $this->generate_archive_name();
	}

	/**
	 * The packager function
	 *
	 * @param mixed $params The parameters containing status, offset and configs for packaging the db
	 */
	public function execute( $params ) {
		global $wpdb;

		// Set query offset
		if ( isset( $params['query_offset'] ) ) {
			$query_offset = (int) $params['query_offset'];
		} else {
			$query_offset = 0;
		}

		// Set table index
		if ( isset( $params['table_index'] ) ) {
			$table_index = (int) $params['table_index'];
		} else {
			$table_index = 0;
		}

		// Set table offset
		if ( isset( $params['table_offset'] ) ) {
			$table_offset = (int) $params['table_offset'];
		} else {
			$table_offset = 0;
		}

		// Set table rows
		if ( isset( $params['table_rows'] ) ) {
			$table_rows = (int) $params['table_rows'];
		} else {
			$table_rows = 0;
		}

		// Set total tables count
		if ( isset( $params['total_tables_count'] ) ) {
			$total_tables_count = (int) $params['total_tables_count'];
		} else {
			$total_tables_count = 1;
		}

		// What percent of tables have we processed?
		$progress = (int) ( ( $table_index / $total_tables_count ) * 100 );

	}
}
