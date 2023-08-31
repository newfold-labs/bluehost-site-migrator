<?php

namespace BluehostSiteMigrator\Packager;

use BluehostSiteMigrator\Utils\Options;

/**
 * The Packager base class providing some common functions to be used by other packages
 */
class PackagerBase {
	/**
	 * The package name i.e. db, plugins, themes etc.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Generate the archive name for the current package
	 *
	 * @param string $ext The extension for the generated archive
	 */
	public function generate_archive_name( $ext = 'zip' ) {
		$date      = gmdate( 'Y-m-d-His' );
		$site_name = strtolower( preg_replace( '#[^a-zA-Z0-9]#', '-', get_bloginfo( 'name' ) ) );
		$unique_id = uniqid();

		return "backup-{$date}-{$site_name}-{$unique_id}-{$this->name}.{$ext}";
	}

	/**
	 * Persist the file in the files list
	 *
	 * @param string $package_path The path for the generated archive
	 * @param string $package_type The type for package, eg - database, plugins etc.
	 */
	public function persist_archive_path( $package_path, $package_type ) {
		$files_array = Options::get( 'packaged_files', array() );
		$uploads     = wp_get_upload_dir();

		$package_data = array(
			'hash'      => md5_file( $package_path ),
			'path'      => $package_path,
			'size'      => filesize( $package_path ),
			'timestamp' => time(),
			'url'       => str_replace( $uploads['basedir'], $uploads['baseurl'], $package_path ),
			'type'      => $package_type,
		);

		$files_array[ $package_type ] = $package_data;
		// Set the array
		Options::set( 'packaged_files', $files_array );
	}
}
