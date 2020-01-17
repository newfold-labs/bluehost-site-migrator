<?php

/**
 * Class BH_Move_Uploads_Packager
 */
class BH_Move_Uploads_Packager implements BH_Move_Packager {

	/**
	 * Create the uploads package.
	 *
	 * @return string Path to the package file or an empty string on failure.
	 */
	public function create_package() {

		$package = '';

		$uploads = wp_upload_dir();

		$zip = BH_Move_Utilities::zip_directory( $uploads['basedir'], 'uploads' );

		if ( $zip ) {

			$package = $zip;
		}

		return $package;
	}

}
