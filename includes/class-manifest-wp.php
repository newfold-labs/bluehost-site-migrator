<?php

/**
 * Class BH_Site_Migrator_WP_Manifest
 */
class BH_Site_Migrator_WP_Manifest extends BH_Site_Migrator_Registry {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->set( 'constants', $this->constants() );
		$this->set( 'core', $this->core() );
		$this->set( 'database', $this->database() );
		$this->set( 'meta', $this->meta() );
		$this->set( 'plugins', $this->plugins() );
		$this->set( 'settings', $this->settings() );
		$this->set( 'themes', $this->themes() );
	}

	/**
	 * Get a list of pertinent WP constants.
	 *
	 * @return object
	 */
	protected function constants() {
		$constants = array();

		$blacklist = array(
			'AUTH_KEY',
			'AUTH_SALT',
			'DB_HOST',
			'DB_NAME',
			'DB_PASSWORD',
			'DB_USER',
			'LOGGED_IN_KEY',
			'LOGGED_IN_SALT',
			'NONCE_KEY',
			'NONCE_SALT',
			'SECURE_AUTH_KEY',
			'SECURE_AUTH_SALT',
		);

		$pattern = '#define.+?[\'"](.*?)[\'"]#';
		$found   = preg_match_all( $pattern, file_get_contents( ABSPATH . 'wp-config.php' ), $results );
		if ( $found && isset( $results[0], $results[1] ) ) {
			foreach ( $results[1] as $index => $name ) {
				if ( ! in_array( $name, $blacklist, true ) ) {
					$constants[ $name ] = constant( $name );
				}
			}
		}

		return (object) $constants;
	}

	/**
	 * Get WP core data.
	 *
	 * @return array
	 */
	protected function core() {
		global $wp_version, $wp_db_version, $required_php_version, $required_mysql_version;

		require ABSPATH . WPINC . '/version.php';

		return array(
			'is_multisite'           => is_multisite(),
			'required_mysql_version' => $required_mysql_version,
			'required_php_version'   => $required_php_version,
			'wp_db_version'          => $wp_db_version,
			'wp_version'             => $wp_version,
		);
	}

	/**
	 * Get database information.
	 *
	 * @return array
	 */
	protected function database() {
		global $wpdb;

		$db_size = 0;

		$table_data = $wpdb->get_results( 'SHOW TABLE STATUS' );
		foreach ( $table_data as $table_datum ) {
			if ( preg_match( '#^' . preg_quote( $wpdb->prefix, '#' ) . '#', $table_datum->{'Name'} ) ) {
				$db_size += absint( $table_datum->{'Data_length'} );
			}
		}

		return array(
			'host'         => DB_HOST,
			'name'         => DB_NAME,
			'size'         => $db_size,
			'table_prefix' => $wpdb->prefix,
			'tables'       => array_values( $wpdb->tables() ),
			'user'         => DB_USER,
			'version'      => $wpdb->db_version(),
		);
	}

	/**
	 * Get meta data related to the WordPress site.
	 *
	 * @return array
	 */
	protected function meta() {

		$users      = count_users();
		$post_types = array_keys( get_post_types() );
		$taxonomies = array_keys( get_taxonomies() );

		$term_counts = array();

		foreach ( $taxonomies as $taxonomy ) {
			$term_counts[ $taxonomy ] = absint( wp_count_terms( $taxonomy ) );
		}

		return array(
			'active_plugin_count' => count( get_option( 'active_plugins' ) ),
			'plugin_count'        => count( BH_Site_Migrator_Plugin_Manifest::get_plugins() ),
			'post_type_count'     => count( $post_types ),
			'post_types'          => $post_types,
			'taxonomies'          => $taxonomies,
			'taxonomy_count'      => count( $taxonomies ),
			'term_counts'         => $term_counts,
			'theme_count'         => count( wp_get_themes() ),
			'user_count'          => $users['total_users'],
			'user_role_counts'    => $users['avail_roles'],
		);
	}

	/**
	 * Get a collection of plugin data.
	 *
	 * @return array
	 */
	protected function plugins() {
		$manifest = new BH_Site_Migrator_Plugin_Manifest();

		return array_values( $manifest->to_array() );
	}

	/**
	 * Get important WordPress settings.
	 *
	 * @return array
	 */
	protected function settings() {
		return array(
			'admin_email' => get_option( 'admin_email' ),
			'home_url'    => get_home_url(),
			'site_url'    => get_site_url(),
			'timezone'    => wp_timezone_string(),
		);
	}

	/**
	 * Get a collection of theme data.
	 *
	 * @return array
	 */
	protected function themes() {
		$manifest = new BH_Site_Migrator_Theme_Manifest();

		return array_values( $manifest->to_array() );
	}

}
