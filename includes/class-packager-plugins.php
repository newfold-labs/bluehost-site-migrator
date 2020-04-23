<?php

/**
 * Class BH_Site_Migrator_Plugins_Packager
 */
class BH_Site_Migrator_Plugins_Packager implements BH_Site_Migrator_Packager {

	/**
	 * Create the plugins package.
	 *
	 * @return string Path to the package file or an empty string on failure.
	 */
	public function create_package() {
		return BH_Site_Migrator_Utilities::zip_directory( WP_PLUGIN_DIR, 'plugins' );
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
		if ( ! function_exists( 'get_plugins' ) ) {
			require ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = array_keys( get_plugins() );

		if ( empty( $plugins ) ) {
			return true;
		}

		foreach ( $plugins as $plugin ) {
			$path     = realpath( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin );
			$modified = filemtime( $path );
			if ( $modified && $modified > $data['timestamp'] ) {
				return false;
			}
		}

		return true;
	}

}
