<?php

namespace BluehostSiteMigrator\Utils;

/**
 * A class to easily get and persist the current status of entire migration task
 */
class Status {
	/**
	 * Get the current status
	 */
	public static function get_status() {
		return get_option( BH_SITE_MIGRATOR_PACKAGING_STATUS_OPTION, array() );
	}

	/**
	 * Set the current status
	 *
	 * @param string $message  The user friendly message
	 * @param int    $progress The progress made with the current task in %
	 * @param string $stage    The stage packaging is in, db, plugins etc.
	 */
	public static function set_status( $message, $progress, $stage ) {
		update_option(
			BH_SITE_MIGRATOR_PACKAGING_STATUS_OPTION,
			array(
				'message'  => $message,
				'progress' => $progress,
				'stage'    => $stage,
			)
		);
	}

	/**
	 * Mark the migration as successful or failed
	 *
	 * @param boolean $success true if successful false if failed
	 */
	public static function set_packaging_success( $success ) {
		if ( $success ) {
			update_option( BH_SITE_MIGRATOR_PACKAGING_SUCCESS_OPTION, true );
			return;
		}
		update_option( BH_SITE_MIGRATOR_PACKAGING_FAILED_OPTION, true );
	}

	/**
	 * Get the success option
	 */
	public static function get_packaging_status() {
		return array(
			'success' => get_option( BH_SITE_MIGRATOR_PACKAGING_SUCCESS_OPTION, false ),
			'failed'  => get_option( BH_SITE_MIGRATOR_PACKAGING_FAILED_OPTION, false ),
		);
	}
}
