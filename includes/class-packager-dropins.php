<?php

/**
 * Class BH_Move_Dropins_Packager
 */
class BH_Move_Dropins_Packager implements BH_Move_Packager {

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

			$filename = BH_Move_Migration_Package::generate_name( 'dropins' );
			$zip_path = BH_Move_Utilities::get_upload_path( $filename );

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

		return $package;
	}

}
