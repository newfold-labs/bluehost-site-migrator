<?php

/**
 * Class BH_Site_Migrator_Migration_Checks
 */
class BH_Site_Migrator_Migration_Checks {

	/**
	 * Run migration checks.
	 *
	 * @return bool True if migration is possible, false otherwise.
	 */
	public static function run() {
		$can_we_migrate = apply_filters( 'bluehost_site_migrator_can_migrate', true );
		BH_Site_Migrator_Options::set( 'isCompatible', $can_we_migrate );

		return $can_we_migrate;
	}

	/**
	 * Register migration checks.
	 */
	public static function register() {
		add_filter( 'bluehost_site_migrator_can_migrate', array( __CLASS__, 'can_mysqldump' ), 5 );
		add_filter( 'bluehost_site_migrator_can_migrate', array( __CLASS__, 'can_we_migrate_api' ), 10 );
		add_filter( 'bluehost_site_migrator_can_migrate', array( __CLASS__, 'has_zip_archive' ), 5 );
		add_filter( 'bluehost_site_migrator_can_migrate', array( __CLASS__, 'is_content_directory_writable' ), 5 );
		add_filter( 'bluehost_site_migrator_can_migrate', array( __CLASS__, 'is_not_multisite' ), 5 );
	}

	/**
	 * Check if mysqldump command is available. If not, we won't be able to backup the database.
	 *
	 * @param bool $can_migrate Whether or not we can migrate the site.
	 *
	 * @return bool
	 */
	public static function can_mysqldump( $can_migrate ) {
		return $can_migrate ? ! empty( shell_exec( 'which mysqldump' ) ) : $can_migrate; // phpcs:ignore
	}

	/**
	 * Check if migration is still allowed and, if so, send the manifest file to the CanWeMigrate API for a more thorough validation.
	 *
	 * TODO: Integrate with CanWeMigrate API (cache response for x amount of time)
	 *
	 * @param bool $can_migrate Whether or not we can migrate the site.
	 *
	 * @return bool
	 */
	public static function can_we_migrate_api( $can_migrate ) {
		// phpcs:disable
		if ( $can_migrate ) {
			/*
			$cache_key   = 'bluehost_site_migrator_can_migrate';
			$can_migrate = get_transient( $cache_key );
			if ( ! $can_migrate ) {
				$manifest    = BH_Site_Migrator_Manifest::create();
				$response    = wp_remote_post( 'https://', array(
					'body' => $manifest,
				) );
				$status_code = (int) wp_remote_retrieve_response_code( $response );
				$body        = wp_remote_retrieve_body( $response );
				$data        = json_decode( $body );
				if ( $status_code === 200 && isset( $data, $data['can_migrate'] ) ) {
					$can_migrate = $data['can_migrate'];
					set_transient( $cache_key, $can_migrate, HOUR_IN_SECONDS );
				}
			}
			*/
		}
		// phpcs:enable

		return $can_migrate;
	}

	/**
	 * Check if the ZipArchive class is present. We rely on this for generating the site migration package.
	 *
	 * @param bool $can_migrate Whether or not we can migrate the site.
	 *
	 * @return bool
	 */
	public static function has_zip_archive( $can_migrate ) {
		return $can_migrate ? class_exists( 'ZipArchive' ) : $can_migrate;
	}

	/**
	 * Check if the content directory is writable. If not, we don't have anywhere we can reliably store the site migration package.
	 *
	 * @param bool $can_migrate Whether or not we can migrate the site.
	 *
	 * @return bool
	 */
	public static function is_content_directory_writable( $can_migrate ) {
		return $can_migrate ? wp_is_writable( WP_CONTENT_DIR ) : $can_migrate;
	}

	/**
	 * Check if the site is a multisite install. Currently, automated backups only work for standard installs.
	 *
	 * @param bool $can_migrate Whether or not we can migrate the site.
	 *
	 * @return bool
	 */
	public static function is_not_multisite( $can_migrate ) {
		return $can_migrate ? ! is_multisite() : $can_migrate;
	}

}
