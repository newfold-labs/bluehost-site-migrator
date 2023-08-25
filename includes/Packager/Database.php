<?php

namespace BluehostSiteMigrator\Packager;

use BluehostSiteMigrator\Database\DatabaseMysqli;
use BluehostSiteMigrator\Utils\Options;
use BluehostSiteMigrator\Utils\Status;

/**
 * Class to package the database for a site
 */
class Database extends PackagerBase {

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
	 * Get the table list file path
	 */
	public static function get_table_list_file_path() {
		return nfd_bhsm_get_hashed_file_path( 'tables', 'config', 'list' );
	}

	/**
	 * Prepare the packaging, populate parameters
	 */
	public static function prepare() {
		global $wpdb;

		$database_task_params = self::get_database_params();

		// Get the total tables count or initialize
		if ( isset( $database_task_params['total_tables_count'] ) ) {
			$total_tables_count = (int) $database_task_params['total_tables_count'];
		} else {
			$total_tables_count = 1;
		}

		// Set the status message and stage
		Status::set_status( 'Retrieving a list of WordPress database tables ', 5, BH_SITE_MIGRATOR_STAGE_DATABASE );

		// Get the database client
		$mysql = new DatabaseMysqli( $wpdb );

		// Include table prefixes
		$table_prefixes = nfd_bhsm_table_prefix();

		if ( $table_prefixes ) {
			$mysql->add_table_prefix_filter( $table_prefixes );

			// Include table prefixes (Webba Booking)
			foreach (
			array(
				'wbk_services',
				'wbk_days_on_off',
				'wbk_locked_time_slots',
				'wbk_appointments',
				'wbk_cancelled_appointments',
				'wbk_email_templates',
				'wbk_service_categories',
				'wbk_gg_calendars',
				'wbk_coupons',
			) as $table_name
			) {
				$mysql->add_table_prefix_filter( $table_name );
			}
		}

		// Dump the tables list in a file
		$table_list_file_path                    = self::get_table_list_file_path();
		$tables_list                             = nfd_bhsm_open( $table_list_file_path, 'w' );
		$database_task_params['table_list_path'] = $table_list_file_path;

		// Write table line
		foreach ( $mysql->get_tables() as $table_name ) {
			if ( nfd_bhsm_putcsv( $tables_list, array( $table_name ) ) ) {
				++$total_tables_count;
			}
		}

		Status::set_status( 'Done retrieving the WordPress database tables', 8, BH_SITE_MIGRATOR_STAGE_DATABASE );

		$database_task_params['total_tables_count'] = $total_tables_count;
		self::set_database_params( $database_task_params );
	}

	/**
	 * The packager function
	 */
	public static function execute() {
		global $wpdb;

		// Prepare the params for this task
		self::prepare();

		$params = self::get_database_params();

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

		// Set the table list file path
		if ( isset( $params['table_list_path'] ) ) {
			$table_list_file_path = $params['table_list_path'];
		} else {
			$table_list_file_path = '';
		}

		// What percent of tables have we processed?
		$progress = (int) ( ( $table_index / $total_tables_count ) * 100 );
		Status::set_status(
			'Exporting database ... ' . strval( $progress ) . '%',
			10,
			BH_SITE_MIGRATOR_STAGE_DATABASE
		);

		$tables_list = nfd_bhsm_open( $table_list_file_path, 'r' );

		// Loop over the tables
		$tables = array();
		while ( list( $table_name ) = fgetcsv( $tables_list ) ) { // phpcs:ignore
			$tables[] = $table_name;
		}

		fclose( $tables_list );

		$mysql = new DatabaseMysqli( $wpdb );
		$mysql->set_tables( $tables );

		// Exclude site options
		$mysql->set_table_where_query(
			nfd_bhsm_table_prefix() . 'options',
			sprintf(
				"`option_name` NOT IN ('%s', '%s', '%s', '%s', '%s', '%s')",
				BH_SITE_MIGRATOR_OPTION_NAME,
				BH_SITE_MIGRATOR_REGIONS_OPTION,
				BH_SITE_MIGRATOR_GEO_DATA_OPTION,
				BH_SITE_MIGRATOR_TOKEN_OPTION,
				BH_SITE_MIGRATOR_MIGRATION_ID_OPTION,
				BH_SITE_MIGRATOR_COUNTRY_CODE_OPTION,
			)
		);

		// Try exporting the database
		$completed = $mysql->export(
			nfd_bhsm_get_hashed_file_path( 'db', 'backup', 'sql' ),
			$query_offset,
			$table_index,
			$table_offset,
			$table_rows
		);

		if ( $completed ) {
			Status::set_status( 'Done creating the database dump', 13, BH_SITE_MIGRATOR_STAGE_DATABASE );

			// Unset query offset
			unset( $params['query_offset'] );

			// Unset table index
			unset( $params['table_index'] );

			// Unset table offset
			unset( $params['table_offset'] );

			// Unset table rows
			unset( $params['table_rows'] );

			// Unset total tables count
			unset( $params['total_tables_count'] );

			// Unset completed flag
			unset( $params['completed'] );

			// Persist the options in the database
			self::set_database_params( $params );
		} else {
			// What percent of tables have we processed?
			$progress = (int) ( ( $table_index / $total_tables_count ) * 100 );

			// Set progress
			Status::set_status( 'Exporting database ', $progress, BH_SITE_MIGRATOR_STAGE_DATABASE );

			// Set query offset
			$params['query_offset'] = $query_offset;

			// Set table index
			$params['table_index'] = $table_index;

			// Set table offset
			$params['table_offset'] = $table_offset;

			// Set table rows
			$params['table_rows'] = $table_rows;

			// Set total tables count
			$params['total_tables_count'] = $total_tables_count;

			// Set completed flag
			$params['completed'] = false;

			// Persist the params and try again after some time
			self::set_database_params( $params );
			self::execute();
		}
	}
}
