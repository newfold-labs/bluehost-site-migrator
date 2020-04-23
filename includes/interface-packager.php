<?php

/**
 * Interface BH_Site_Migrator_Packager
 */
interface BH_Site_Migrator_Packager {

	/**
	 * Create a package.
	 *
	 * @return string Path to the package file or an empty string on failure.
	 */
	public function create_package();

	/**
	 * Validate whether or not the generated package is still valid.
	 *
	 * @param array $data Package data (e.g. hash, path, size, timestamp, url)
	 *
	 * @return bool
	 */
	public function is_package_valid( array $data );

}
