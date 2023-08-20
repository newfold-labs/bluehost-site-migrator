<?php

namespace BluehostSiteMigrator\Packager;

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
}
