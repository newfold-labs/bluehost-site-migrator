<?php

/**
 * Class BH_Site_Migrator_Dropins_Packager
 */
class BH_Site_Migrator_Dropins_Packager implements BH_Site_Migrator_Packager {

	/**
	 * Create the dropins package.
	 *
	 * @return string Path to the package file or an empty string on failure.
	 */
	public function create_package() {

		$package = '';

		if ( ! function_exists( 'get_dropins' ) ) {
			require ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$dropins = array_keys( get_dropins() );

		if ( $dropins ) {

			$filename = BH_Site_Migrator_Migration_Package::generate_name( 'dropins' );
			$zip_path = BH_Site_Migrator_Utilities::get_upload_path( $filename );

			$zip = new ZipArchive();
			if ( true === $zip->open( $zip_path, ZipArchive::CREATE ) ) {

				foreach ( $dropins as $dropin ) {
					$zip->addFile( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $dropin, $dropin );
				}

				$success = $zip->close();

				if ( $success ) {
					$package = $zip_path;
				}
			}
		}

		if ( empty( $package ) ) {
			return 'done';
		}

		return $package;
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
		if ( ! function_exists( 'get_dropins' ) ) {
			require ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$dropins = array_keys( get_dropins() );

		if ( ! $dropins ) {
			return true;
		}

		foreach ( $dropins as $dropin ) {
			$modified = filemtime( realpath( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $dropin ) );
			if ( $modified && $modified > $data['timestamp'] ) {
				return false;
			}
		}

		return true;
	}

}
