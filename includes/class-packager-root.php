<?php

/**
 * Class BH_Site_Migrator_Root_Packager
 */
class BH_Site_Migrator_Root_Packager implements BH_Site_Migrator_Packager {

	/**
	 * Create the others package.
	 *
	 * @return string Path to the package file or an empty string on failure.
	 */
	public function create_package() {

		$package = '';

		add_filter( 'bh_site_migrator_filter_files', 'bh_site_migrator_filter_directories', 10, 2 );
		add_filter( 'bh_site_migrator_filter_by_path', 'bh_site_migrator_filter_wp_root_files' );

		$zip = BH_Site_Migrator_Utilities::zip_directory( ABSPATH, 'root' );

		remove_filter( 'bh_site_migrator_filter_files', 'bh_site_migrator_filter_directories' );
		remove_filter( 'bh_site_migrator_filter_by_path', 'bh_site_migrator_filter_wp_root_files' );

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

		add_filter( 'bh_site_migrator_filter_files', 'bh_site_migrator_filter_directories', 10, 2 );
		add_filter( 'bh_site_migrator_filter_by_path', 'bh_site_migrator_filter_wp_root_files' );

		$dir_iterator    = new RecursiveDirectoryIterator( ABSPATH, RecursiveDirectoryIterator::SKIP_DOTS );
		$filter_iterator = new BH_Site_Migrator_Filter_Iterator( $dir_iterator );
		$files           = new RecursiveIteratorIterator( $filter_iterator, RecursiveIteratorIterator::SELF_FIRST );

		foreach ( $files as $file ) {
			/**
			 * File instance.
			 *
			 * @var SplFileInfo $file
			 */
			$modified = filemtime( $file->getRealPath() );
			if ( $modified && $modified > $data['timestamp'] ) {
				return false;
			}
		}

		remove_filter( 'bh_site_migrator_filter_files', 'bh_site_migrator_filter_directories' );
		remove_filter( 'bh_site_migrator_filter_by_path', 'bh_site_migrator_filter_wp_root_files' );

		return true;
	}

}
