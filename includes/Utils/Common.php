<?php

namespace BluehostSiteMigrator\Utils;

class Common {
    /**
	 * Get the path to the wp-config.php file.
	 *
	 * @return string
	 */
	public static function locate_wp_config_file() {
		$path = '';
		if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
			$path = ABSPATH . 'wp-config.php';
		} elseif ( file_exists( dirname( ABSPATH ) . '/wp-config.php' ) ) {
			$path = dirname( ABSPATH ) . '/wp-config.php';
		}

		return $path;
	}
}
