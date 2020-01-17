<?php

/**
 * Interface BH_Move_Packager
 */
interface BH_Move_Packager {

	/**
	 * Create a package.
	 *
	 * @return string Path to the package file or an empty string on failure.
	 */
	public function create_package();

}
