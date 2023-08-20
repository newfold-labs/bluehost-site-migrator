<?php

namespace BluehostSiteMigrator\Utils\Database;

use BluehostSiteMigrator\Utils\DatabaseUtility;

/**
 * The base database class for the db interactions
 */
abstract class DatabaseBase {
	/**
	 * WordPress database handler
	 *
	 * @var object
	 */
	protected $wpdb = null;

	/**
	 * WordPress database base tables
	 *
	 * @var array
	 */
	protected $base_tables = null;

	/**
	 * WordPress database views
	 *
	 * @var array
	 */
	protected $views = null;

	/**
	 * WordPress database tables
	 *
	 * @var array
	 */
	protected $tables = null;

	/**
	 * Table where query
	 *
	 * @var array
	 */
	protected $table_where_query = array();

	/**
	 * Table select columns
	 *
	 * @var array
	 */
	protected $table_select_columns = array();

	/**
	 * Table prefix columns
	 *
	 * @var array
	 */
	protected $table_prefix_columns = array();

	/**
	 * Table prefix filters
	 *
	 * @var array
	 */
	protected $table_prefix_filters = array();

	/**
	 * List all tables that should not be affected by the timeout of the current request
	 *
	 * @var array
	 */
	protected $atomic_tables = array();

	/**
	 * Visual Composer
	 *
	 * @var boolean
	 */
	protected $visual_composer = false;

	/**
	 * Oxygen Builder
	 *
	 * @var boolean
	 */
	protected $oxygen_builder = false;

	/**
	 * BeTheme Responsive
	 *
	 * @var boolean
	 */
	protected $betheme_responsive = false;

	/**
	 * Optimize Press
	 *
	 * @var boolean
	 */
	protected $optimize_press = false;

	/**
	 * Avada Fusion Builder
	 *
	 * @var boolean
	 */
	protected $avada_fusion_builder = false;

	/**
	 * Constructor
	 *
	 * @param object $wpdb WPDB instance
	 */
	public function __construct( $wpdb ) {
		$this->wpdb = $wpdb;

		// Check Microsoft SQL Server support
		if ( is_resource( $this->wpdb->dbh ) ) {
			if ( get_resource_type( $this->wpdb->dbh ) === 'SQL Server Connection' ) {
				throw new \Exception(
					(
						'Your WordPress installation uses Microsoft SQL Server. ' .
						'To use Bluehost Site Migrator, please change your installation to MySQL and try again. ' .
						'<a href="https://help.servmask.com/knowledgebase/microsoft-sql-server/" target="_blank">Technical details</a>'
					)
				);
			}
		}

		// Set database host (HyberDB)
		if ( empty( $this->wpdb->dbhost ) ) {
			if ( isset( $this->wpdb->last_used_server['host'] ) ) {
				$this->wpdb->dbhost = $this->wpdb->last_used_server['host'];
			}
		}

		// Set database name (HyperDB)
		if ( empty( $this->wpdb->dbname ) ) {
			if ( isset( $this->wpdb->last_used_server['name'] ) ) {
				$this->wpdb->dbname = $this->wpdb->last_used_server['name'];
			}
		}
	}

	/**
	 * Set table where query
	 *
	 * @param  string $table_name   Table name
	 * @param  array  $where_$query Table query
	 * @return object
	 */
	public function set_table_where_query( $table_name, $where_query ) {
		$this->table_where_query[ strtolower( $table_name ) ] = $where_query;

		return $this;
	}

	/**
	 * Get table where query
	 *
	 * @param  string $table_name Table name
	 * @return string
	 */
	public function get_table_where_query( $table_name ) {
		if ( isset( $this->table_where_query[ strtolower( $table_name ) ] ) ) {
			return $this->table_where_query[ strtolower( $table_name ) ];
		}
	}

	/**
	 * Set table select columns
	 *
	 * @param  string $table_name   Table name
	 * @param  array  $column_names Column names
	 * @return object
	 */
	public function set_table_select_columns( $table_name, $column_names ) {
		foreach ( $column_names as $column_name => $column_expression ) {
			$this->table_select_columns[ strtolower( $table_name ) ][ strtolower( $column_name ) ] = $column_expression;
		}

		return $this;
	}

	/**
	 * Get table select columns
	 *
	 * @param  string $table_name Table name
	 * @return array
	 */
	public function get_table_select_columns( $table_name ) {
		if ( isset( $this->table_select_columns[ strtolower( $table_name ) ] ) ) {
			return $this->table_select_columns[ strtolower( $table_name ) ];
		}
	}

	/**
	 * Set table prefix columns
	 *
	 * @param  string $table_name   Table name
	 * @param  array  $column_names Column names
	 * @return object
	 */
	public function set_table_prefix_columns( $table_name, $column_names ) {
		foreach ( $column_names as $column_name ) {
			$this->table_prefix_columns[ strtolower( $table_name ) ][ strtolower( $column_name ) ] = true;
		}

		return $this;
	}

	/**
	 * Get table prefix columns
	 *
	 * @param  string $table_name Table name
	 * @return array
	 */
	public function get_table_prefix_columns( $table_name ) {
		if ( isset( $this->table_prefix_columns[ strtolower( $table_name ) ] ) ) {
			return $this->table_prefix_columns[ strtolower( $table_name ) ];
		}
	}

	/**
	 * Add table prefix filter
	 *
	 * @param  string $table_prefix   Table prefix
	 * @param  string $exclude_prefix Exclude prefix
	 * @return object
	 */

	public function add_table_prefix_filter( $table_prefix, $exclude_prefix = null ) {
		$this->table_prefix_filters[] = array( $table_prefix, $exclude_prefix );

		return $this;
	}

	/**
	 * Get table prefix filter
	 *
	 * @return array
	 */
	public function get_table_prefix_filters() {
		return $this->table_prefix_filters;
	}

	/**
	 * Set atomic tables
	 *
	 * @param  array $tables List of tables
	 * @return object
	 */
	public function set_atomic_tables( $tables ) {
		$this->atomic_tables = $tables;

		return $this;
	}

	/**
	 * Get atomic tables
	 *
	 * @return array
	 */
	public function get_atomic_tables() {
		return $this->atomic_tables;
	}

	/**
	 * Set Visual Composer
	 *
	 * @param  boolean $active Is Visual Composer Active?
	 * @return object
	 */
	public function set_visual_composer( $active ) {
		$this->visual_composer = $active;

		return $this;
	}

	/**
	 * Get Visual Composer
	 *
	 * @return boolean
	 */
	public function get_visual_composer() {
		return $this->visual_composer;
	}

	/**
	 * Set Oxygen Builder
	 *
	 * @param  boolean $active Is Oxygen Builder Active?
	 * @return object
	 */
	public function set_oxygen_builder( $active ) {
		$this->oxygen_builder = $active;

		return $this;
	}

	/**
	 * Get Oxygen Builder
	 *
	 * @return boolean
	 */
	public function get_oxygen_builder() {
		return $this->oxygen_builder;
	}

	/**
	 * Set BeTheme Responsive
	 *
	 * @param  boolean $active Is BeTheme Responsive Active?
	 * @return object
	 */
	public function set_betheme_responsive( $active ) {
		$this->betheme_responsive = $active;

		return $this;
	}

	/**
	 * Get BeTheme Responsive
	 *
	 * @return boolean
	 */
	public function get_betheme_responsive() {
		return $this->betheme_responsive;
	}

	/**
	 * Set Optimize Press
	 *
	 * @param  boolean $active Is Optimize Press Active?
	 * @return object
	 */
	public function set_optimize_press( $active ) {
		$this->optimize_press = $active;

		return $this;
	}

	/**
	 * Get Optimize Press
	 *
	 * @return boolean
	 */
	public function get_optimize_press() {
		return $this->optimize_press;
	}

	/**
	 * Set Avada Fusion Builder
	 *
	 * @param  boolean $active Is Avada Fusion Builder Active?
	 * @return object
	 */
	public function set_avada_fusion_builder( $active ) {
		$this->avada_fusion_builder = $active;

		return $this;
	}

	/**
	 * Get Avada Fusion Builder
	 *
	 * @return boolean
	 */
	public function get_avada_fusion_builder() {
		return $this->avada_fusion_builder;
	}

	/**
	 * Get views
	 *
	 * @return array
	 */
	protected function get_views() {
		if ( is_null( $this->views ) ) {
			$where_query = array();

			// Get lower case table names
			$lower_case_table_names = $this->get_lower_case_table_names();

			// Loop over table prefixes
			if ( $this->get_table_prefix_filters() ) {
				foreach ( $this->get_table_prefix_filters() as $prefix_filter ) {
					if ( isset( $prefix_filter[0], $prefix_filter[1] ) ) {
						if ( $lower_case_table_names ) {
							$where_query[] = sprintf(
								"(`Tables_in_%s` REGEXP '^%s' AND `Tables_in_%s` NOT REGEXP '^%s')",
								$this->wpdb->dbname,
								$prefix_filter[0],
								$this->wpdb->dbname,
								$prefix_filter[1]
							);
						} else {
							$where_query[] = sprintf(
								"(CAST(`Tables_in_%s` AS BINARY) REGEXP BINARY '^%s' AND CAST(`Tables_in_%s` AS BINARY) NOT REGEXP BINARY '^%s')",
								$this->wpdb->dbname,
								$prefix_filter[0],
								$this->wpdb->dbname,
								$prefix_filter[1]
							);
						}
					} else {
						if ( $lower_case_table_names ) {
							$where_query[] = sprintf(
								"`Tables_in_%s` REGEXP '^%s'",
								$this->wpdb->dbname,
								$prefix_filter[0]
							);
						} else {
							$where_query[] = sprintf(
								"CAST(`Tables_in_%s` AS BINARY) REGEXP BINARY '^%s'",
								$this->wpdb->dbname,
								$prefix_filter[0]
							);
						}
					}
				}
			} else {
				$where_query[] = 1;
			}

			$this->views = array();

			// Loop over views
			$result = $this->query(
				sprintf(
					"SHOW FULL TABLES FROM `%s` WHERE `Table_type` = 'VIEW' AND (%s)",
					$this->wpdb->dbname,
					implode( ' OR ', $where_query )
				)
			);
			while ( $row = $this->fetch_row( $result ) ) {
				if ( isset( $row[0] ) ) {
					$this->views[] = $row[0];
				}
			}

			// Close result cursor
			$this->free_result( $result );
		}

		return $this->views;
	}

	/**
	 * Get base tables
	 *
	 * @return array
	 */
	protected function get_base_tables() {
		if ( is_null( $this->base_tables ) ) {
			$where_query = array();

			// Get lower case table names
			$lower_case_table_names = $this->get_lower_case_table_names();

			// Loop over table prefixes
			if ( $this->get_table_prefix_filters() ) {
				foreach ( $this->get_table_prefix_filters() as $prefix_filter ) {
					if ( isset( $prefix_filter[0], $prefix_filter[1] ) ) {
						if ( $lower_case_table_names ) {
							$where_query[] = sprintf(
								"(`Tables_in_%s` REGEXP '^%s' AND `Tables_in_%s` NOT REGEXP '^%s')",
								$this->wpdb->dbname,
								$prefix_filter[0],
								$this->wpdb->dbname,
								$prefix_filter[1]
							);
						} else {
							$where_query[] = sprintf(
								"(CAST(`Tables_in_%s` AS BINARY) REGEXP BINARY '^%s' AND CAST(`Tables_in_%s` AS BINARY) NOT REGEXP BINARY '^%s')",
								$this->wpdb->dbname,
								$prefix_filter[0],
								$this->wpdb->dbname,
								$prefix_filter[1]
							);
						}
					} else {
						if ( $lower_case_table_names ) {
							$where_query[] = sprintf(
								"`Tables_in_%s` REGEXP '^%s'",
								$this->wpdb->dbname,
								$prefix_filter[0]
							);
						} else {
							$where_query[] = sprintf(
								"CAST(`Tables_in_%s` AS BINARY) REGEXP BINARY '^%s'",
								$this->wpdb->dbname,
								$prefix_filter[0]
							);
						}
					}
				}
			} else {
				$where_query[] = 1;
			}

			$this->base_tables = array();

			// Loop over base tables
			$result = $this->query(
				sprintf(
					"SHOW FULL TABLES FROM `%s` WHERE `Table_type` = 'BASE TABLE' AND (%s)",
					$this->wpdb->dbname,
					implode( ' OR ', $where_query )
				)
			);
			while ( $row = $this->fetch_row( $result ) ) {
				if ( isset( $row[0] ) ) {
					$this->base_tables[] = $row[0];
				}
			}

			// Close result cursor
			$this->free_result( $result );
		}

		return $this->base_tables;
	}

	/**
	 * Set tables
	 *
	 * @param  array $tables List of tables
	 * @return object
	 */
	public function set_tables( $tables ) {
		$this->tables = $tables;

		return $this;
	}

	/**
	 * Get tables
	 *
	 * @return array
	 */
	public function get_tables() {
		if ( is_null( $this->tables ) ) {
			return array_merge( $this->get_base_tables(), $this->get_views() );
		}

		return $this->tables;
	}

	/**
	 * Export database into a file
	 *
	 * @param  string  $file_name    File name
	 * @param  integer $query_offset Query offset
	 * @param  integer $table_index  Table index
	 * @param  integer $table_offset Table offset
	 * @param  integer $table_rows   Table rows
	 * @return boolean
	 */
	public function export( $file_name, &$query_offset = 0, &$table_index = 0, &$table_offset = 0, &$table_rows = 0 ) {
		// Set file handler
		$file_handler = nfd_bhsm_open( $file_name, 'cb' );

		// Start time
		$start = microtime( true );

		// Flag to hold if all tables have been processed
		$completed = true;

		// Set SQL mode
		$this->query( "SET SESSION sql_mode = ''" );

		// Get tables
		$tables = $this->get_tables();

		// Get views
		$views = $this->get_views();

		// Set file pointer at the query offset
		if ( fseek( $file_handler, $query_offset ) !== -1 ) {

			// Write headers
			if ( $query_offset === 0 ) {
				nfd_bhsm_write( $file_handler, $this->get_header() );
			}

			$tables_count = count( $tables );

			// Export tables
			while ( $table_index < $tables_count ) {

				// Get table name
				$table_name = $tables[ $table_index ];

				// Loop over tables and views
				if ( in_array( $table_name, $views, true ) ) {

					// Get create view statement
					if ( 0 === $table_offset ) {

						// Write view drop statement
						$drop_view = "\nDROP VIEW IF EXISTS `{$table_name}`;\n";

						// Write drop view statement
						nfd_bhsm_write( $file_handler, $drop_view );

						// Get create view statement
						$create_view = $this->get_create_view( $table_name );

						// Replace create view name
						$create_view = $this->replace_view_name( $create_view, $table_name, $table_name );

						// Replace create view options
						$create_view = $this->replace_view_options( $create_view );

						// Write create view statement
						nfd_bhsm_write( $file_handler, $create_view );

						// Write end of statement
						nfd_bhsm_write( $file_handler, ";\n\n" );
					}

					// Set curent table index
					$table_index++;

					// Set current table offset
					$table_offset = 0;

				} else {

					// Get create table statement
					if ( 0 === $table_offset ) {

						// Write table drop statement
						$drop_table = "\nDROP TABLE IF EXISTS `{$table_name}`;\n";

						// Write table statement
						nfd_bhsm_write( $file_handler, $drop_table );

						// Get create table statement
						$create_table = $this->get_create_table( $table_name );

						// Replace create table name
						$create_table = $this->replace_table_name( $create_table, $table_name, $table_name );

						// Replace create table options
						$create_table = $this->replace_table_options( $create_table );

						// Write create table statement
						nfd_bhsm_write( $file_handler, $create_table );

						// Write end of statement
						nfd_bhsm_write( $file_handler, ";\n\n" );
					}

					// Get primary keys
					$primary_keys = $this->get_primary_keys( $table_name );

					// Get column types
					$column_types = $this->get_column_types( $table_name );

					// Get prefix columns
					$prefix_columns = $this->get_table_prefix_columns( $table_name );

					do {

						// Set query
						if ( $primary_keys ) {

							// Set table keys
							$table_keys = array();
							foreach ( $primary_keys as $key ) {
								$table_keys[] = sprintf( '`%s`', $key );
							}

							$table_keys = implode( ', ', $table_keys );

							// Set table where query
							if ( ! ( $table_where = $this->get_table_where_query( $table_name ) ) ) {
								$table_where = 1;
							}

							// Set table select columns
							if ( ! ( $select_columns = $this->get_table_select_columns( $table_name ) ) ) {
								$select_columns = array( 't1.*' );
							}

							$select_columns = implode( ', ', $select_columns );

							// Set query with offset and rows count
							$query = sprintf( 'SELECT %s FROM `%s` AS t1 JOIN (SELECT %s FROM `%s` WHERE %s ORDER BY %s LIMIT %d, %d) AS t2 USING (%s)', $select_columns, $table_name, $table_keys, $table_name, $table_where, $table_keys, $table_offset, BH_SITE_MIGRATOR_SELECT_RECORDS, $table_keys );

						} else {

							$table_keys = 1;

							// Set table where query
							if ( ! ( $table_where = $this->get_table_where_query( $table_name ) ) ) {
								$table_where = 1;
							}

							// Set table select columns
							if ( ! ( $select_columns = $this->get_table_select_columns( $table_name ) ) ) {
								$select_columns = array( '*' );
							}

							$select_columns = implode( ', ', $select_columns );

							// Set query with offset and rows count
							$query = sprintf( 'SELECT %s FROM `%s` WHERE %s ORDER BY %s LIMIT %d, %d', $select_columns, $table_name, $table_where, $table_keys, $table_offset, BH_SITE_MIGRATOR_SELECT_RECORDS );
						}

						// Run SQL query
						$result = $this->query( $query );

						// Repair table data
						if ( $this->errno() === 1194 ) {

							// Current table is marked as crashed and should be repaired
							$this->repair_table( $table_name );

							// Run SQL query
							$result = $this->query( $query );
						}

						// Generate insert statements
						$num_rows = $this->num_rows( $result );
						if ( $num_rows ) {

							// Loop over table rows
							$row = $this->fetch_assoc( $result );
							while ( $row ) {

								// Write start transaction
								if ( $table_offset % BH_SITE_MIGRATOR_MAX_TRANSACTION_QUERIES === 0 ) {
									nfd_bhsm_write( $file_handler, "START TRANSACTION;\n" );
								}

								$items = array();
								foreach ( $row as $key => $value ) {
									$items[] = $this->prepare_table_values( $value, $column_types[ strtolower( $key ) ] );
								}

								// Set table values
								$table_values = implode( ',', $items );

								// Set insert statement
								$table_insert = "INSERT INTO `{$table_name}` VALUES ({$table_values});\n";

								// Write insert statement
								nfd_bhsm_write( $file_handler, $table_insert );

								// Set current table offset
								$table_offset++;

								// Set current table rows
								$table_rows++;

								// Write end of transaction
								if ( $table_offset % BH_SITE_MIGRATOR_MAX_TRANSACTION_QUERIES === 0 ) {
									nfd_bhsm_write( $file_handler, "COMMIT;\n" );
								}

								$row = $this->fetch_assoc( $result );
							}
						} else {

							// Write end of transaction
							if ( $table_offset % BH_SITE_MIGRATOR_MAX_TRANSACTION_QUERIES !== 0 ) {
								nfd_bhsm_write( $file_handler, "COMMIT;\n" );
							}

							// Set curent table index
							$table_index++;

							// Set current table offset
							$table_offset = 0;
						}

						// Close result cursor
						$this->free_result( $result );

						// Time elapsed
						$timeout = apply_filters( 'nfd_bhsm_completed_timeout', 10 );
						if ( $timeout ) {
							if ( ( microtime( true ) - $start ) > $timeout ) {
								$completed = false;
								break 2;
							}
						}
					} while ( $num_rows > 0 );
				}
			}
		}

		// Set query offset
		$query_offset = ftell( $file_handler );

		// Close file handler
		fclose( $file_handler );

		return $completed;
	}

	/**
	 * Get MySQL version
	 *
	 * @return string
	 */
	protected function get_version() {
		$result = $this->query( "SHOW VARIABLES LIKE 'version'" );
		$row    = $this->fetch_assoc( $result );

		// Close result cursor
		$this->free_result( $result );

		// Get version
		if ( isset( $row['Value'] ) ) {
			return $row['Value'];
		}
	}

	/**
	 * Get MySQL max allowed packet
	 *
	 * @return integer
	 */
	protected function get_max_allowed_packet() {
		$result = $this->query( "SHOW VARIABLES LIKE 'max_allowed_packet'" );
		$row    = $this->fetch_assoc( $result );

		// Close result cursor
		$this->free_result( $result );

		// Get max allowed packet
		if ( isset( $row['Value'] ) ) {
			return $row['Value'];
		}
	}

	/**
	 * Get MySQL lower case table names
	 *
	 * @return integer
	 */
	protected function get_lower_case_table_names() {
		$result = $this->query( "SHOW VARIABLES LIKE 'lower_case_table_names'" );
		$row    = $this->fetch_assoc( $result );

		// Close result cursor
		$this->free_result( $result );

		// Get lower case table names
		if ( isset( $row['Value'] ) ) {
			return $row['Value'];
		}
	}

	/**
	 * Get MySQL collation name
	 *
	 * @param  string $collation_name Collation name
	 * @return string
	 */
	protected function get_collation( $collation_name ) {
		$result = $this->query( "SHOW COLLATION LIKE '{$collation_name}'" );
		$row    = $this->fetch_assoc( $result );

		// Close result cursor
		$this->free_result( $result );

		// Get collation name
		if ( isset( $row['Collation'] ) ) {
			return $row['Collation'];
		}
	}

	/**
	 * Get MySQL create view
	 *
	 * @param  string $view_name View name
	 * @return string
	 */
	protected function get_create_view( $view_name ) {
		$result = $this->query( "SHOW CREATE VIEW `{$view_name}`" );
		$row    = $this->fetch_assoc( $result );

		// Close result cursor
		$this->free_result( $result );

		// Get create view
		if ( isset( $row['Create View'] ) ) {
			return $row['Create View'];
		}
	}

	/**
	 * Get MySQL create table
	 *
	 * @param  string $table_name Table name
	 * @return string
	 */
	protected function get_create_table( $table_name ) {
		$result = $this->query( "SHOW CREATE TABLE `{$table_name}`" );
		$row    = $this->fetch_assoc( $result );

		// Close result cursor
		$this->free_result( $result );

		// Get create table
		if ( isset( $row['Create Table'] ) ) {
			return $row['Create Table'];
		}
	}

	/**
	 * Repair MySQL table
	 *
	 * @param  string $table_name Table name
	 * @return void
	 */
	protected function repair_table( $table_name ) {
		$this->query( "REPAIR TABLE `{$table_name}`" );
	}

	/**
	 * Get MySQL primary keys
	 *
	 * @param  string $table_name Table name
	 * @return array
	 */
	protected function get_primary_keys( $table_name ) {
		$primary_keys = array();

		// Get primary keys
		$result = $this->query( "SHOW KEYS FROM `{$table_name}` WHERE `Key_name` = 'PRIMARY'" );
		$row    = $this->fetch_assoc( $result );
		while ( $row ) {
			if ( isset( $row['Column_name'] ) ) {
				$primary_keys[] = $row['Column_name'];
			}
			$row = $this->fetch_assoc( $result );
		}

		// Close result cursor
		$this->free_result( $result );

		return $primary_keys;
	}

	/**
	 * Get MySQL unique keys
	 *
	 * @param  string $table_name Table name
	 * @return array
	 */
	protected function get_unique_keys( $table_name ) {
		$unique_keys = array();

		// Get unique keys
		$result = $this->query( "SHOW KEYS FROM `{$table_name}` WHERE `Non_unique` = 0" );
		$row    = $this->fetch_assoc( $result );
		while ( $row ) {
			if ( isset( $row['Column_name'] ) ) {
				$unique_keys[] = $row['Column_name'];
			}
			$row = $this->fetch_assoc( $result );
		}

		// Close result cursor
		$this->free_result( $result );

		return $unique_keys;
	}

	/**
	 * Get MySQL column types
	 *
	 * @param  string $table_name Table name
	 * @return array
	 */
	protected function get_column_types( $table_name ) {
		$column_types = array();

		// Get column types
		$result = $this->query( "SHOW COLUMNS FROM `{$table_name}`" );
		$row    = $this->fetch_assoc( $result );
		while ( $row ) {
			if ( isset( $row['Field'] ) ) {
				$column_types[ strtolower( $row['Field'] ) ] = $row['Type'];
			}
			$row = $this->fetch_assoc( $result );
		}

		// Close result cursor
		$this->free_result( $result );

		return $column_types;
	}

	/**
	 * Get MySQL column names
	 *
	 * @param  string $table_name Table name
	 * @return array
	 */
	public function get_column_names( $table_name ) {
		$column_names = array();

		// Get column types
		$result = $this->query( "SHOW COLUMNS FROM `{$table_name}`" );
		$row    = $this->fetch_assoc( $result );
		while ( $row  ) {
			if ( isset( $row['Field'] ) ) {
				$column_names[ strtolower( $row['Field'] ) ] = $row['Field'];
			}
			$row = $this->fetch_assoc( $result );
		}

		// Close result cursor
		$this->free_result( $result );

		return $column_names;
	}

	/**
	 * Replace table name
	 *
	 * @param  string $input          Table value
	 * @param  string $old_table_name Old table name
	 * @param  string $new_table_name New table name
	 * @return string
	 */
	protected function replace_table_name( $input, $old_table_name, $new_table_name ) {
		$position = stripos( $input, "`$old_table_name`" );
		if ( false !== $position ) {
			$input = substr_replace( $input, "`$new_table_name`", $position, strlen( "`$old_table_name`" ) );
		}

		return $input;
	}

	/**
	 * Replace view name
	 *
	 * @param  string $input         View value
	 * @param  string $old_view_name Old view name
	 * @param  string $new_view_name New view name
	 * @return string
	 */
	protected function replace_view_name( $input, $old_view_name, $new_view_name ) {
		$position = stripos( $input, "`$old_view_name`" );
		if ( false !== $position ) {
			$input = substr_replace( $input, "`$new_view_name`", $position, strlen( "`$old_view_name`" ) );
		}

		return $input;
	}

	/**
	 * Replace view options
	 *
	 * @param  string $input Table value
	 * @return string
	 */
	protected function replace_view_options( $input ) {
		return preg_replace( '/CREATE(.+?)VIEW/i', 'CREATE VIEW', $input );
	}

	/**
	 * Replace table values
	 *
	 * @param  string $input Table value
	 * @return string
	 */
	protected function replace_table_values( $input ) {
		// Replace base64 encoded values (Visual Composer)
		if ( $this->get_visual_composer() ) {
			$input = preg_replace_callback( '/\[vc_raw_html\]([a-zA-Z0-9\/+]+={0,2})\[\/vc_raw_html\]/S', array( $this, 'replace_visual_composer_values_callback' ), $input );
		}

		// Replace base64 encoded values (Oxygen Builder)
		if ( $this->get_oxygen_builder() ) {
			$input = preg_replace_callback( '/\\\\"(code-php|code-css|code-js)\\\\":\\\\"([a-zA-Z0-9\/+]+={0,2})\\\\"/S', array( $this, 'replace_oxygen_builder_values_callback' ), $input );
		}

		// Replace base64 encoded values (BeTheme Responsive, Optimize Press and Avada Fusion Builder)
		if ( $this->get_betheme_responsive() || $this->get_optimize_press() || $this->get_avada_fusion_builder() ) {
			$input = preg_replace_callback( "/'([a-zA-Z0-9\/+]+={0,2})'/S", array( $this, 'replace_base64_values_callback' ), $input );
		}

		return $input;
	}

	/**
	 * Replace base64 values callback (Visual Composer)
	 *
	 * @param  array  $matches List of matches
	 * @return string
	 */
	protected function replace_visual_composer_values_callback( $matches ) {
		// Validate base64 data
		if ( DatabaseUtility::base64_validate( $matches[1] ) ) {

			// Decode base64 characters
			$matches[1] = DatabaseUtility::base64_decode( $matches[1] );

			// Encode base64 characters
			$matches[1] = DatabaseUtility::base64_encode( $matches[1] );
		}

		return '[vc_raw_html]' . $matches[1] . '[/vc_raw_html]';
	}

	/**
	 * Replace base64 values callback (Oxygen Builder)
	 *
	 * @param  array  $matches List of matches
	 * @return string
	 */
	protected function replace_oxygen_builder_values_callback( $matches ) {
		// Validate base64 data
		if ( DatabaseUtility::base64_validate( $matches[2] ) ) {

			// Decode base64 characters
			$matches[2] = DatabaseUtility::base64_decode( $matches[2] );

			// Encode base64 characters
			$matches[2] = DatabaseUtility::base64_encode( $matches[2] );
		}

		return '\"' . $matches[1] . '\":\"' . $matches[2] . '\"';
	}

	/**
	 * Replace base64 values callback (BeTheme Responsive and Optimize Press)
	 *
	 * @param  array  $matches List of matches
	 * @return string
	 */
	protected function replace_base64_values_callback( $matches ) {
		// Validate base64 data
		if ( DatabaseUtility::base64_validate( $matches[1] ) ) {

			// Decode base64 characters
			$matches[1] = DatabaseUtility::base64_decode( $matches[1] );

			// Encode base64 characters
			$matches[1] = DatabaseUtility::base64_encode( $matches[1] );
		}

		return "'" . $matches[1] . "'";
	}

	/**
	 * Replace table collations
	 *
	 * @param  string $input SQL statement
	 * @return string
	 */
	protected function replace_table_collations( $input ) {
		static $search  = array();
		static $replace = array();

		// Replace table collations
		if ( empty( $search ) || empty( $replace ) ) {
			if ( ! $this->wpdb->has_cap( 'utf8mb4_520' ) ) {
				if ( ! $this->wpdb->has_cap( 'utf8mb4' ) ) {
					$search  = array( 'utf8mb4_0900_ai_ci', 'utf8mb4_unicode_520_ci', 'utf8mb4' );
					$replace = array( 'utf8_unicode_ci', 'utf8_unicode_ci', 'utf8' );
				} else {
					$search  = array( 'utf8mb4_0900_ai_ci', 'utf8mb4_unicode_520_ci' );
					$replace = array( 'utf8mb4_unicode_ci', 'utf8mb4_unicode_ci' );
				}
			} else {
				$search  = array( 'utf8mb4_0900_ai_ci' );
				$replace = array( 'utf8mb4_unicode_520_ci' );
			}
		}

		return str_replace( $search, $replace, $input );
	}

	/**
	 * Check whether input is transient query
	 *
	 * @param  string  $input SQL statement
	 * @return boolean
	 */
	protected function is_transient_query( $input ) {
		return strpos( $input, "'_transient_" ) !== false;
	}

	/**
	 * Check whether input is site transient query
	 *
	 * @param  string  $input SQL statement
	 * @return boolean
	 */
	protected function is_site_transient_query( $input ) {
		return strpos( $input, "'_site_transient_" ) !== false;
	}

	/**
	 * Check whether input is WooCommerce session query
	 *
	 * @param  string  $input SQL statement
	 * @return boolean
	 */
	protected function is_wc_session_query( $input ) {
		return strpos( $input, "'_wc_session_" ) !== false;
	}

	/**
	 * Check whether input is START TRANSACTION query
	 *
	 * @param  string  $input SQL statement
	 * @return boolean
	 */
	protected function is_start_transaction_query( $input ) {
		return strpos( $input, 'START TRANSACTION' ) === 0;
	}

	/**
	 * Check whether input is COMMIT query
	 *
	 * @param  string  $input SQL statement
	 * @return boolean
	 */
	protected function is_commit_query( $input ) {
		return strpos( $input, 'COMMIT' ) === 0;
	}

	/**
	 * Check whether input is DROP TABLE query
	 *
	 * @param  string  $input SQL statement
	 * @return boolean
	 */
	protected function is_drop_table_query( $input ) {
		return strpos( $input, 'DROP TABLE' ) === 0;
	}

	/**
	 * Check whether input is CREATE TABLE query
	 *
	 * @param  string  $input SQL statement
	 * @return boolean
	 */
	protected function is_create_table_query( $input ) {
		return strpos( $input, 'CREATE TABLE' ) === 0;
	}

	/**
	 * Check whether input is INSERT INTO query
	 *
	 * @param  string  $input      SQL statement
	 * @param  string  $table_name Table name (case insensitive)
	 * @return boolean
	 */
	protected function is_insert_into_query( $input, $table_name ) {
		return stripos( $input, sprintf( 'INSERT INTO `%s`', $table_name ) ) === 0;
	}

	/**
	 * Check whether input is cache query
	 *
	 * @param  string  $input SQL statement
	 * @return boolean
	 */
	public function is_cache_query( $input ) {
		$cache = false;

		// Skip cache based on table query
		switch ( true ) {
			case $this->is_transient_query( $input ):
			case $this->is_site_transient_query( $input ):
			case $this->is_wc_session_query( $input ):
				$cache = true;
				break;
		}

		return $cache;
	}

	/**
	 * Check whether input is atomic query
	 *
	 * @param  string  $input SQL statement
	 * @return boolean
	 */
	protected function is_atomic_query( $input ) {
		$atomic = false;

		// Skip timeout based on table query
		switch ( true ) {
			case $this->is_drop_table_query( $input ):
			case $this->is_create_table_query( $input ):
			case $this->is_start_transaction_query( $input ):
			case $this->is_commit_query( $input ):
				$atomic = true;
				break;

			default:
				// Skip timeout based on table query and table name
				foreach ( $this->get_atomic_tables() as $table_name ) {
					if ( $this->is_insert_into_query( $input, $table_name ) ) {
						$atomic = true;
						break;
					}
				}
		}

		return $atomic;
	}

	/**
	 * Replace table options
	 *
	 * @param  string $input SQL statement
	 * @return string
	 */
	protected function replace_table_options( $input ) {
		$search  = array(
			'TYPE=InnoDB',
			'TYPE=MyISAM',
			'ENGINE=Aria',
			'TRANSACTIONAL=0',
			'TRANSACTIONAL=1',
			'PAGE_CHECKSUM=0',
			'PAGE_CHECKSUM=1',
			'TABLE_CHECKSUM=0',
			'TABLE_CHECKSUM=1',
			'ROW_FORMAT=PAGE',
			'ROW_FORMAT=FIXED',
			'ROW_FORMAT=DYNAMIC',
		);
		$replace = array(
			'ENGINE=InnoDB',
			'ENGINE=MyISAM',
			'ENGINE=MyISAM',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
		);

		return str_ireplace( $search, $replace, $input );
	}

	/**
	 * Replace table engines
	 *
	 * @param  string $input SQL statement
	 * @return string
	 */
	protected function replace_table_engines( $input ) {
		$search  = array(
			'ENGINE=MyISAM',
			'ENGINE=Aria',
		);
		$replace = array(
			'ENGINE=InnoDB',
			'ENGINE=InnoDB',
		);

		return str_ireplace( $search, $replace, $input );
	}

	/**
	 * Replace table row format
	 *
	 * @param  string $input SQL statement
	 * @return string
	 */
	protected function replace_table_row_format( $input ) {
		$search  = array(
			'ENGINE=InnoDB',
			'ENGINE=MyISAM',
		);
		$replace = array(
			'ENGINE=InnoDB ROW_FORMAT=DYNAMIC',
			'ENGINE=MyISAM ROW_FORMAT=DYNAMIC',
		);

		return str_ireplace( $search, $replace, $input );
	}
	/**
	 * Replace table full-text indexes (MySQL <= 5.5)
	 *
	 * @param  string $input SQL statement
	 * @return string
	 */
	protected function replace_table_fulltext_indexes( $input ) {
		$pattern = array(
			'/\s+FULLTEXT KEY(.+),/i',
			'/,\s+FULLTEXT KEY(.+)/i',
		);

		return preg_replace( $pattern, '', $input );
	}

	/**
	 * Returns header for dump file
	 *
	 * @return string
	 */
	protected function get_header() {
		// Some info about software, source and time
		$header = sprintf(
			"-- Bluehost Site MIgrator SQL Dump\n" .
			"--\n" .
			"-- Host: %s\n" .
			"-- Database: %s\n" .
			"-- Class: %s\n" .
			"--\n",
			$this->wpdb->dbhost,
			$this->wpdb->dbname,
			get_class( $this )
		);

		return $header;
	}

	/**
	 * Prepare table values
	 *
	 * @param  string  $input       Table value
	 * @param  integer $column_type Column type
	 * @return string
	 */
	protected function prepare_table_values( $input, $column_type ) {
		switch ( true ) {
			case is_null( $input ):
				return 'NULL';

			case stripos( $column_type, 'tinyint' ) === 0:
			case stripos( $column_type, 'smallint' ) === 0:
			case stripos( $column_type, 'mediumint' ) === 0:
			case stripos( $column_type, 'int' ) === 0:
			case stripos( $column_type, 'bigint' ) === 0:
			case stripos( $column_type, 'float' ) === 0:
			case stripos( $column_type, 'double' ) === 0:
			case stripos( $column_type, 'decimal' ) === 0:
			case stripos( $column_type, 'bit' ) === 0:
				return $input;

			case stripos( $column_type, 'binary' ) === 0:
			case stripos( $column_type, 'varbinary' ) === 0:
			case stripos( $column_type, 'tinyblob' ) === 0:
			case stripos( $column_type, 'mediumblob' ) === 0:
			case stripos( $column_type, 'longblob' ) === 0:
			case stripos( $column_type, 'blob' ) === 0:
				return '0x' . bin2hex( $input );

			default:
				return "'" . $this->escape( $input ) . "'";
		}
	}

	/**
	 * Run MySQL query
	 *
	 * @param  string   $input SQL query
	 * @return resource
	 */
	abstract public function query( $input );

	/**
	 * Escape string input for mysql query
	 *
	 * @param  string $input String to escape
	 * @return string
	 */
	abstract public function escape( $input );

	/**
	 * Return the error code for the most recent function call
	 *
	 * @return integer
	 */
	abstract public function errno();

	/**
	 * Return a string description of the last error
	 *
	 * @return string
	 */
	abstract public function error();

	/**
	 * Return server version
	 *
	 * @return string
	 */
	abstract public function version();

	/**
	 * Return the result from MySQL query as associative array
	 *
	 * @param  resource $result MySQL resource
	 * @return array
	 */
	abstract public function fetch_assoc( $result );

	/**
	 * Return the result from MySQL query as row
	 *
	 * @param  resource $result MySQL resource
	 * @return array
	 */
	abstract public function fetch_row( $result );

	/**
	 * Return the number for rows from MySQL results
	 *
	 * @param  resource $result MySQL resource
	 * @return integer
	 */
	abstract public function num_rows( $result );

	/**
	 * Free MySQL result memory
	 *
	 * @param  resource $result MySQL resource
	 * @return boolean
	 */
	abstract public function free_result( $result );
}
