<?php

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class BH_Site_Migrator_Migration_Checks
 */
class BH_Site_Migrator_Migration_Checks {

	/**
	 * Store the results from the various checks.
	 *
	 * @var array
	 */
	public static $results = array();

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
		add_filter( 'bluehost_site_migrator_can_migrate', array( __CLASS__, 'has_proc_open' ), 5 );
		add_filter( 'bluehost_site_migrator_can_migrate', array( __CLASS__, 'has_disk_free_space' ), 5 );
		add_filter( 'bluehost_site_migrator_can_migrate', array( __CLASS__, 'has_disk_total_space' ), 5 );
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
		$process = new Process( array( 'which', 'mysqldump' ) );
		$process->run();
		$can_mysqldump = ! empty( $process->getOutput() );

		self::$results['can_mysqldump'] = $can_mysqldump;

		return $can_migrate ? $can_mysqldump : $can_migrate;
	}

	/**
	 * Check if migration is still allowed and, if so, send the manifest file to the CanWeMigrate API for a more thorough validation.
	 *
	 * @param bool $can_migrate Whether or not we can migrate the site.
	 *
	 * @return bool
	 */
	public static function can_we_migrate_api( $can_migrate ) {
		if ( $can_migrate ) {
			$cache_key   = 'bluehost_site_migrator_can_migrate';
			$can_migrate = get_transient( $cache_key );
			if ( ! $can_migrate ) {
				$manifest = BH_Site_Migrator_Manifest::create();
				$payload  = wp_json_encode( $manifest, JSON_PRETTY_PRINT );
				$response = wp_remote_post(
					BH_SITE_MIGRATOR_API_BASEURL . '/manifestScan',
					array(
						'headers'   => array(
							'Content-Type' => 'application/json',
						),
						'body'      => $payload,
						'sslverify' => is_ssl(),
					)
				);
				if ( is_wp_error( $response ) ) {
					self::$results['cwm_api'] = $response->get_error_message();

					return $can_migrate;
				}
				$status_code = (int) wp_remote_retrieve_response_code( $response );
				$body        = wp_remote_retrieve_body( $response );
				$data        = json_decode( $body, true );
				if ( 200 === $status_code && isset( $data, $data['feasible'], $data['migrationId'], $data['x-auth-token'] ) ) {
					if ( isset( $data['factors'] ) ) {
						self::$results['cwm_api'] = $data['factors'];
					}
					$can_migrate = (bool) $data['feasible'];
					update_option( 'bh_site_migration_region_urls', bh_site_migrator_data_get( $data, 'regionUrls', array() ) );
					update_option( 'bh_site_migration_id', $data['migrationId'] );
					update_option( 'bh_site_migration_token', $data['x-auth-token'] );
					set_transient( $cache_key, $can_migrate, HOUR_IN_SECONDS );
				}
			}
			if ( ! array_key_exists( 'cwm_api', self::$results ) ) {
				self::$results['cwm_api'] = $can_migrate;
			}
		}

		// Always return the manifest in response for debugging purposes
		if ( ! array_key_exists( 'manifest', self::$results ) ) {
			self::$results['manifest'] = BH_Site_Migrator_Manifest::fetch();
		}

		return $can_migrate;
	}

	/**
	 * Check if the proc_open function is present. We rely on this for generating the site migration package.
	 *
	 * @param bool $can_migrate Whether or not we can migrate the site.
	 *
	 * @return bool
	 */
	public static function has_proc_open( $can_migrate ) {
		$has_proc_open = function_exists( 'proc_open' );

		self::$results['has_proc_open'] = $has_proc_open;

		return $can_migrate ? $has_proc_open : $can_migrate;
	}

	/**
	 * Check if the disk_free_space function is present. We rely on this for generating the site migration package.
	 *
	 * @param bool $can_migrate Whether or not we can migrate the site.
	 *
	 * @return bool
	 */
	public static function has_disk_free_space( $can_migrate ) {
		$has_disk_free_space = function_exists( 'disk_free_space' );

		self::$results['has_disk_free_space'] = $has_disk_free_space;

		return $can_migrate ? $has_disk_free_space : $can_migrate;
	}

	/**
	 * Check if the disk_total_space function is present. We rely on this for generating the site migration package.
	 *
	 * @param bool $can_migrate Whether or not we can migrate the site.
	 *
	 * @return bool
	 */
	public static function has_disk_total_space( $can_migrate ) {
		$has_disk_total_space = function_exists( 'disk_total_space' );

		self::$results['has_disk_total_space'] = $has_disk_total_space;

		return $can_migrate ? $has_disk_total_space : $can_migrate;
	}

	/**
	 * Check if the ZipArchive class is present. We rely on this for generating the site migration package.
	 *
	 * @param bool $can_migrate Whether or not we can migrate the site.
	 *
	 * @return bool
	 */
	public static function has_zip_archive( $can_migrate ) {
		$has_zip_archive                  = class_exists( 'ZipArchive' );
		self::$results['has_zip_archive'] = $has_zip_archive;

		return $can_migrate ? $has_zip_archive : $can_migrate;
	}

	/**
	 * Check if the content directory is writable. If not, we don't have anywhere we can reliably store the site migration package.
	 *
	 * @param bool $can_migrate Whether or not we can migrate the site.
	 *
	 * @return bool
	 */
	public static function is_content_directory_writable( $can_migrate ) {
		$is_writeable                  = wp_is_writable( WP_CONTENT_DIR );
		self::$results['is_writeable'] = $is_writeable;

		return $can_migrate ? $is_writeable : $can_migrate;
	}

	/**
	 * Check if the site is a multisite install. Currently, automated backups only work for standard installs.
	 *
	 * @param bool $can_migrate Whether or not we can migrate the site.
	 *
	 * @return bool
	 */
	public static function is_not_multisite( $can_migrate ) {
		$is_multisite                  = is_multisite();
		self::$results['is_multisite'] = $is_multisite;

		return $can_migrate ? ! $is_multisite : $can_migrate;
	}

}
