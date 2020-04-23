<?php

/**
 * Class BH_Site_Migrator_Themes_Packager
 */
class BH_Site_Migrator_Themes_Packager implements BH_Site_Migrator_Packager {

	/**
	 * Create the themes package.
	 *
	 * @return string Path to the package file or an empty string on failure.
	 */
	public function create_package() {
		return BH_Site_Migrator_Utilities::zip_directory( get_theme_root(), 'themes' );
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
		$themes = array_keys( wp_get_themes() );

		$slash = DIRECTORY_SEPARATOR;

		foreach ( $themes as $theme ) {
			$path     = realpath( WP_CONTENT_DIR . $slash . 'themes' . $slash . $theme );
			$modified = filemtime( $path );
			if ( $modified && $modified > $data['timestamp'] ) {
				return false;
			}
		}

		return true;
	}

}
