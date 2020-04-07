<?php

/**
 * Class BH_Move_Manifest
 */
class BH_Move_Manifest extends BH_Move_Registry {

	/**
	 * Generate manifest.
	 *
	 * @return array Manifest data array on success, empty array on failure.
	 */
	public static function create() {
		$manifest      = new self();
		$manifest_data = $manifest->to_array();
		BH_Move_Options::set( 'manifest', $manifest_data );

		return $manifest_data;
	}

	/**
	 * Check if a manifest already exists.
	 *
	 * @return bool
	 */
	public static function exists() {
		return BH_Move_Options::has( 'manifest' );
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

		return BH_Move_Options::get( 'manifest', array() );
	}

	/**
	 * Delete manifest.
	 *
	 * @param bool $delete_packages Whether or not to delete all migration packages as well.
	 */
	public static function delete( $delete_packages = false ) {
		BH_Move_Options::delete( 'manifest' );
		if ( $delete_packages ) {
			BH_Move_Migration_Package::delete_all();
		}
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->set( 'domain', wp_parse_url( get_home_url(), PHP_URL_HOST ) );
		$this->set( 'timestamp', time() );
		$this->set( 'env', $this->env() );
		$this->set( 'wp', $this->wp() );
	}

	/**
	 * Environment manifest.
	 *
	 * @return array
	 */
	protected function env() {
		$uploads = wp_get_upload_dir();

		return array(
			'filesystem'       => array(
				'document_root'    => $_SERVER['DOCUMENT_ROOT'],
				'free_space'       => disk_free_space( ABSPATH ),
				'total_space'      => disk_total_space( ABSPATH ),
				'uploads_dir_size' => recurse_dirsize( $uploads['basedir'] ),
			),
			'ip_address'       => $_SERVER['SERVER_ADDR'],
			'operating_system' => PHP_OS,
			'php'              => array(
				'execution_timeout' => absint( ini_get( 'max_execution_time' ) ),
				'extensions'        => get_loaded_extensions(),
				'memory_limit'      => ini_get( 'memory_limit' ),
				'version'           => phpversion(),
			),
			'server_hostname'  => gethostname(),
		);
	}

	/**
	 * WordPress manifest.
	 *
	 * @return array
	 */
	protected function wp() {
		$wp = new BH_Move_WP_Manifest();

		return $wp->to_array();
	}

}