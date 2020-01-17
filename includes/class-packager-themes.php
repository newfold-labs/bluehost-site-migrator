<?php

/**
 * Class BH_Move_Themes_Packager
 */
class BH_Move_Themes_Packager implements BH_Move_Packager {

	/**
	 * Create the themes package.
	 *
	 * @return string Path to the package file or an empty string on failure.
	 */
	public function create_package() {
		return BH_Move_Utilities::zip_directory( get_theme_root(), 'themes' );
	}

}
