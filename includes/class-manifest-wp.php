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

		$config_path = BH_Site_Migrator_Utilities::locate_wp_config_file();
		if ( empty( $config_path ) ) {
			return new stdClass();
		}

		$config = file_get_contents( $config_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( ! $config ) {
			return new stdClass();
		}

		$pattern = '#[\'"]([A-Z_]*?)[\'"]#';
		$found   = preg_match_all( $pattern, $config, $results );
		if ( $found && isset( $results[0], $results[1] ) ) {
			$names = array_filter( $results[1] );
			foreach ( $names as $name ) {
				// Ensure that all found names are actually constants.
				if ( ! defined( $name ) ) {
					continue;
				}
				if ( in_array( $name, $blacklist, true ) ) {
					continue;
				}
				$constants[ $name ] = constant( $name );
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
			'isMultisite'          => is_multisite(),
			'requiredMysqlVersion' => $required_mysql_version,
			'requiredPhpVersion'   => $required_php_version,
			'wpDbVersion'          => $wp_db_version,
			'wpVersion'            => $wp_version,
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
			'host'        => DB_HOST,
			'name'        => DB_NAME,
			'size'        => $db_size,
			'tablePrefix' => $wpdb->prefix,
			'tables'      => array_values( $wpdb->tables() ),
			'user'        => DB_USER,
			'version'     => $wpdb->db_version(),
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
			'activePluginCount' => count( get_option( 'active_plugins' ) ),
			'pluginCount'       => count( BH_Site_Migrator_Plugin_Manifest::get_plugins() ),
			'postTypeCount'     => count( $post_types ),
			'postTypes'         => $post_types,
			'taxonomies'        => $taxonomies,
			'taxonomyCount'     => count( $taxonomies ),
			'termCounts'        => $term_counts,
			'themeCount'        => count( wp_get_themes() ),
			'userCount'         => $users['total_users'],
			'userRoleCounts'    => $users['avail_roles'],
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
			'adminEmail' => get_option( 'admin_email' ),
			'homeUrl'    => get_home_url(),
			'siteUrl'    => get_site_url(),
			'timezone'   => wp_timezone_string(),
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
