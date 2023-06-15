<?php

/**
 * Class BH_Site_Migrator_Migration_Package
 */
class BH_Site_Migrator_Migration_Package {

	/**
	 * Create a package of a specific type.
	 *
	 * @param string $package_type Type of migration package (e.g. plugins, themes, uploads, etc.).
	 *
	 * @return array Package data on success or empty array on failure.
	 */
	public static function create( $package_type ) {

		$package_data = array();

		if ( ! self::is_valid_type( $package_type ) ) {
			return $package_data;
		}

		$packager = BH_Site_Migrator_Packager_Factory::create( $package_type );
		$package  = $packager->create_package();

		if ( ! empty( $package ) ) {

			$uploads = wp_get_upload_dir();

			$package_data = array(
				'hash'      => md5_file( $package ),
				'path'      => $package,
				'size'      => filesize( $package ),
				'timestamp' => time(),
				'url'       => str_replace( $uploads['basedir'], $uploads['baseurl'], $package ),
			);

			BH_Site_Migrator_Options::set( $package_type, $package_data );

		}

		return $package_data;
	}

	/**
	 * Fetch a package of a specific type.
	 *
	 * @param string $package_type Type of migration package (e.g. plugins, themes, uploads, etc.).
	 *
	 * @return array Package data on success or empty array on failure.
	 */
	public static function fetch( $package_type ) {

		$package_data = array();

		if ( ! self::is_valid_type( $package_type ) ) {
			return $package_data;
		}

		$package_data = BH_Site_Migrator_Options::get( $package_type, array() );
		if ( ! empty( $package_data ) ) {
			$package_data['type'] = $package_type;
		}

		return $package_data;
	}

	/**
	 * Fetch all packages.
	 *
	 * @return array Collection of package data on success or empty array on failure.
	 */
	public static function fetch_all() {
		$packages      = array();
		$package_types = BH_Site_Migrator_Packager_Factory::get_package_types();
		foreach ( $package_types as $package_type ) {
			$package_data = self::fetch( $package_type );
			if ( 'done' === $package_data['path'] ) {
				continue;
			}
			$packages[ $package_type ] = self::fetch( $package_type );
		}

		return $packages;
	}

	/**
	 * Delete a package of a specific type.
	 *
	 * @param string $package_type Type of migration package (e.g. plugins, themes, uploads, etc.).
	 *
	 * @return array Package data on success or empty array on failure.
	 */
	public static function delete( $package_type ) {

		$is_successful = false;
		$package_data  = self::fetch( $package_type );

		if ( ! empty( $package_data ) && isset( $package_data['path'] ) ) {
			if ( unlink( $package_data['path'] ) ) {
				BH_Site_Migrator_Options::delete( $package_type );
				$is_successful = true;
			}
		}

		return $is_successful ? $package_data : array();
	}

	/**
	 * Delete all packages.
	 *
	 * @return array Collection of package data on success or empty array on failure.
	 */
	public static function delete_all() {
		$packages      = array();
		$package_types = BH_Site_Migrator_Packager_Factory::get_package_types();
		foreach ( $package_types as $package_type ) {
			$packages[ $package_type ] = self::delete( $package_type );
		}

		return $packages;
	}

	/**
	 * Delete orphaned migration packages.
	 */
	public static function delete_orphans() {
		$packages = wp_list_pluck( array_filter( self::fetch_all() ), 'path' );

		$directory          = BH_Site_Migrator_Utilities::get_upload_path();
		$directory_iterator = new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS );
		$files              = new RecursiveIteratorIterator( $directory_iterator, RecursiveIteratorIterator::SELF_FIRST );

		foreach ( $files as $file_name => $file ) {

			// Skip anything that isn't a .zip file
			if ( 'zip' !== $file->getExtension() ) {
				continue;
			}

			// Skip known migration packages
			if ( in_array( $file->getRealPath(), $packages, true ) ) {
				continue;
			}

			unlink( $file->getRealPath() );
		}
	}

	/**
	 * Purge all migration packages from the filesystem.
	 */
	public static function purge() {
		$directory          = BH_Site_Migrator_Utilities::get_upload_path();
		$directory_iterator = new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS );
		$files              = new RecursiveIteratorIterator( $directory_iterator, RecursiveIteratorIterator::SELF_FIRST );

		foreach ( $files as $file_name => $file ) {

			// Skip anything that isn't a .zip file
			if ( 'zip' !== $file->getExtension() ) {
				continue;
			}

			unlink( $file->getRealPath() );
		}
	}

	/**
	 * Check if a package type is valid.
	 *
	 * @param string $package_type Type of migration package (e.g. plugins, themes, uploads, etc.).
	 *
	 * @return bool
	 */
	public static function is_valid_type( $package_type ) {
		return BH_Site_Migrator_Packager_Factory::is_valid_package_type( $package_type );
	}

	/**
	 * Check if a specific migration package is still valid.
	 *
	 * @param string $package_type Type of migration package (e.g. plugins, themes, uploads, etc.).
	 *
	 * @return bool
	 */
	public static function is_valid_package( $package_type ) {
		$data = self::fetch( $package_type );

		// Make sure data exists
		if ( empty( $data ) ) {
			return false;
		}

		if ( 'done' === $data['path'] ) {
			$instance = BH_Site_Migrator_Packager_Factory::create( $package_type );
			return $instance->is_package_valid( $data );
		}

		// Make sure all keys are present
		$keys = array(
			'hash',
			'path',
			'size',
			'timestamp',
			'url',
		);
		foreach ( $keys as $key ) {
			if ( ! array_key_exists( $key, $data ) ) {
				return false;
			}
		}

		// Validate that the package was generated in the last 24 hours
		if ( absint( $data['timestamp'] ) + ( HOUR_IN_SECONDS * 24 ) < time() ) {
			return false;
		}

		// Validate the specific package
		$instance = BH_Site_Migrator_Packager_Factory::create( $package_type );

		return $instance->is_package_valid( $data );
	}

	/**
	 * Generate a name for a package file based on the type.
	 *
	 * @param string $package_type Type of migration package (e.g. plugins, themes, uploads, etc.). The package type.
	 * @param string $ext          The file extension.
	 *
	 * @return string The package file name (e.g. backup-2019-12-17-171507-my-site-5df90d1be9136-db.zip).
	 */
	public static function generate_name( $package_type, $ext = 'zip' ) {
		$date      = gmdate( 'Y-m-d-His' );
		$site_name = strtolower( preg_replace( '#[^a-zA-Z0-9]#', '-', get_bloginfo( 'name' ) ) );
		$unique_id = uniqid();

		return "backup-{$date}-{$site_name}-{$unique_id}-{$package_type}.{$ext}";
	}

}
