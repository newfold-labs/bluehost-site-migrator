<?php

/**
 * Class BH_Site_Migrator_Deactivate
 */
class BH_Site_Migrator_Deactivate {

	/**
	 * BH_Site_Migrator_Deactivate constructor.
	 */
	public static function register_listener() {
		register_deactivation_hook( BH_SITE_MIGRATOR_FILE, array( __CLASS__, 'on_deactivation' ) );
	}

	/**
	 * Code to run when the plugin is deactivated.
	 */
	public static function on_deactivation() {

		if ( ! class_exists( 'WP_Filesystem_Base' ) ) {
			require ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		}

		if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
			require ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
		}

		// Delete all files
		$filesystem = new WP_Filesystem_Direct( false );
		$filesystem->rmdir( BH_Site_Migrator_Utilities::get_upload_path(), true );

		// Delete all data
		BH_Site_Migrator_Options::purge();
		delete_option( 'bh_site_migration_country_code' );
		delete_option( 'bh_site_migration_geo_data' );
		delete_option( 'bh_site_migration_region_urls' );
		delete_option( 'bh_site_migration_id' );
		delete_option( 'bh_site_migration_token' );
		delete_transient( 'bluehost_site_migrator_can_migrate' );
	}

}
