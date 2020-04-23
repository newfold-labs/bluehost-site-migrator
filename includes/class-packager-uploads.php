<?php

/**
 * Class BH_Site_Migrator_Uploads_Packager
 */
class BH_Site_Migrator_Uploads_Packager implements BH_Site_Migrator_Packager {

	/**
	 * Create the uploads package.
	 *
	 * @return string Path to the package file or an empty string on failure.
	 */
	public function create_package() {

		$package = '';

		$uploads = wp_upload_dir();

		$zip = BH_Site_Migrator_Utilities::zip_directory( $uploads['basedir'], 'uploads' );

		if ( $zip ) {

			$package = $zip;
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

		// Check if database has modified posts
		$query = new WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'any',
				'date_query'     => array(
					'column' => 'post_modified_gmt',
					'after'  => array(
						'year'  => gmdate( 'Y', $data['timestamp'] ),
						'month' => gmdate( 'n', $data['timestamp'] ),
						'day'   => gmdate( 'j', $data['timestamp'] ),
					),
				),
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return ! boolval( $query->post_count );
	}

}
