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
		return Options::get( 'status', array() );
	}

	/**
	 * Set the current status
	 *
	 * @param string $message  The user friendly message
	 * @param int    $progress The progress made with the current task in %
	 * @param string $stage    The stage packaging is in, db, plugins etc.
	 */
	public static function set_status( $message, $progress, $stage ) {
		Options::set(
			'status',
			array(
				'message'  => $message,
				'progress' => $progress,
				'stage'    => $stage,
			)
		);
	}
}
