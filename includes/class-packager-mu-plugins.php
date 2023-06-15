<?php

/**
 * Class BH_Site_Migrator_MU_Plugins_Packager
 */
class BH_Site_Migrator_MU_Plugins_Packager implements BH_Site_Migrator_Packager {

	/**
	 * Create the mu-plugins package.
	 *
	 * @return string Path to the package file or an empty string on failure.
	 */
	public function create_package() {
		if ( ! file_exists( WPMU_PLUGIN_DIR ) ) {
			return 'done';
		}

		return BH_Site_Migrator_Utilities::zip_directory( WPMU_PLUGIN_DIR, 'mu-plugins' );
	}

	/**
	 * Validate whether or not the generated package is still valid.
	 *
	 * @param array $data Package data (e.g. hash, path, size, timestamp, url)
	 *
	 * @return bool
	 */
	public function is_package_valid( array $data ) {

		// Check if files have been updated
		if ( ! function_exists( 'get_mu_plugins' ) ) {
			require ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$mu_plugins = array_keys( get_mu_plugins() );

		if ( empty( $mu_plugins ) ) {
			return true;
		}

		foreach ( $mu_plugins as $mu_plugin ) {
			$path     = realpath( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $mu_plugin );
			$modified = filemtime( $path );
			if ( $modified && $modified > $data['timestamp'] ) {
				return false;
			}
		}

		return true;
	}

}
