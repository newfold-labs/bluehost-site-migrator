<?php

/**
 * Class BH_Move_Deactivate
 */
class BH_Move_Deactivate {

	/**
	 * BH_Move_Deactivate constructor.
	 */
	public static function register_listener() {
		register_deactivation_hook( BH_MOVE_FILE, array( __CLASS__, 'on_deactivation' ) );
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
		$filesystem->rmdir( BH_Move_Utilities::get_upload_path(), true );

		// Delete all data
		BH_Move_Options::purge();
	}

}
