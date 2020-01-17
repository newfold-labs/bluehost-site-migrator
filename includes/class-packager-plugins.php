<?php

/**
 * Class BH_Move_Plugins_Packager
 */
class BH_Move_Plugins_Packager implements BH_Move_Packager {

	/**
	 * Create the plugins package.
	 *
	 * @return string Path to the package file or an empty string on failure.
	 */
	public function create_package() {
		return BH_Move_Utilities::zip_directory( WP_PLUGIN_DIR, 'plugins' );
	}

}
