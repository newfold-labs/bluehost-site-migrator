<?php

/**
 * Class BH_Site_Migrator_Manifest
 */
class BH_Site_Migrator_Manifest extends BH_Site_Migrator_Registry {

	/**
	 * Generate manifest.
	 *
	 * @return array Manifest data array on success, empty array on failure.
	 */
	public static function create() {
		$manifest      = new self();
		$manifest_data = $manifest->to_array();
		BH_Site_Migrator_Options::set( 'manifest', $manifest_data );

		return $manifest_data;
	}

	/**
	 * Check if a manifest already exists.
	 *
	 * @return bool
	 */
	public static function exists() {
		return BH_Site_Migrator_Options::has( 'manifest' );
	}

	/**
	 * Fetch manifest.
	 *
	 * @return array Manifest data array on success, empty array on failure.
	 */
	public static function fetch() {
		if ( ! self::exists() ) {
			return self::create();
		}

		return BH_Site_Migrator_Options::get( 'manifest', array() );
	}

	/**
	 * Delete manifest.
	 *
	 * @param bool $delete_packages Whether or not to delete all migration packages as well.
	 */
	public static function delete( $delete_packages = false ) {
		BH_Site_Migrator_Options::delete( 'manifest' );
		if ( $delete_packages ) {
			BH_Site_Migrator_Migration_Package::delete_all();
		}
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->set( 'domain', wp_parse_url( get_home_url(), PHP_URL_HOST ) );
		$this->set( 'timestamp', time() );
		$this->set( 'env', $this->env() );
		$this->set( 'geo', $this->geo() );
		$this->set( 'wp', $this->wp() );
	}

	/**
	 * Geo manifest.
	 *
	 * @return array
	 */
	protected function geo() {
		return get_option( 'bh_site_migration_geo_data', array() );
	}

	/**
	 * Environment manifest.
	 *
	 * @return array
	 */
	protected function env() {
		$uploads = wp_get_upload_dir();

		return array(
			'filesystem'      => array(
				'documentRoot'   => $_SERVER['DOCUMENT_ROOT'],
				'freeSpace'      => disk_free_space( ABSPATH ),
				'totalSpace'     => disk_total_space( ABSPATH ),
				'uploadsDirSize' => bh_site_migrator_get_dir_size( $uploads['basedir'] ),
			),
			'ipAddress'       => $_SERVER['SERVER_ADDR'],
			'operatingSystem' => PHP_OS,
			'php'             => array(
				'executionTimeout' => absint( ini_get( 'max_execution_time' ) ),
				'extensions'       => get_loaded_extensions(),
				'memoryLimit'      => ini_get( 'memory_limit' ),
				'version'          => phpversion(),
			),
			'serverHostname'  => gethostname(),
		);
	}

	/**
	 * WordPress manifest.
	 *
	 * @return array
	 */
	protected function wp() {
		$wp = new BH_Site_Migrator_WP_Manifest();

		return $wp->to_array();
	}

}
