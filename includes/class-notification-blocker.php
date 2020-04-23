<?php

/**
 * Class BH_Site_Migrator_Notification_Blocker
 */
class BH_Site_Migrator_Notification_Blocker {

	/**
	 * Target notices using highly-specific CSS selectors to avoid collisions.
	 */
	public static function block_notifications() {
		if ( ! isset( $_GET['page'] ) || false === stripos( filter_input( INPUT_GET, 'page' ), 'bluehost' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		?>
		<style type="text/css" data-bluehost-hide-notifications="1">
			#wpbody-content > div.error,
			#wpbody-content > div.notice,
			#wpbody-content > .update-nag {
				display: none !important;
			}
		</style>
		<?php
	}

}
