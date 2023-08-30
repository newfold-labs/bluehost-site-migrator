<?php

define( 'BH_SITE_MIGRATOR_VERSION', '1.0.12' );
define( 'BH_SITE_MIGRATOR_CIPHER_NAME', 'AES-256-CBC' );
define( 'BH_SITE_MIGRATOR_PLUGIN_NAME', 'bluehost-site-migrator' );
define( 'BH_SITE_MIGRATOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BH_SITE_MIGRATOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BH_SITE_MIGRATOR_PLUGIN_BUILD_DIR', plugin_dir_path( __FILE__ ) . 'build/' . BH_SITE_MIGRATOR_VERSION );
define( 'BH_SITE_MIGRATOR_PLUGIN_BUILD_URL', plugin_dir_url( __FILE__ ) . 'build/' . BH_SITE_MIGRATOR_VERSION );

if ( ! defined( 'BH_SITE_MIGRATOR_MAX_TRANSACTION_QUERIES' ) ) {
	define( 'BH_SITE_MIGRATOR_MAX_TRANSACTION_QUERIES', 1000 );
}

if ( ! defined( 'BH_SITE_MIGRATOR_SELECT_RECORDS' ) ) {
	define( 'BH_SITE_MIGRATOR_SELECT_RECORDS', 1000 );
}

if ( ! defined( 'BH_SITE_MIGRATOR_STAGE_DATABASE' ) ) {
	define( 'BH_SITE_MIGRATOR_STAGE_DATABASE', 'database' );
}

if ( ! defined( 'BH_SITE_MIGRATOR_OPTION_NAME' ) ) {
	define( 'BH_SITE_MIGRATOR_OPTION_NAME', 'bluehost_site_migrator' );
}

if ( ! defined( 'BH_SITE_MIGRATOR_REGIONS_OPTION' ) ) {
	define( 'BH_SITE_MIGRATOR_REGIONS_OPTION', 'bh_site_migration_region_urls' );
}

if ( ! defined( 'BH_SITE_MIGRATOR_MIGRATION_ID_OPTION' ) ) {
	define( 'BH_SITE_MIGRATOR_MIGRATION_ID_OPTION', 'bh_site_migration_id' );
}

if ( ! defined( 'BH_SITE_MIGRATOR_TOKEN_OPTION' ) ) {
	define( 'BH_SITE_MIGRATOR_TOKEN_OPTION', 'bh_site_migration_token' );
}

if ( ! defined( 'BH_SITE_MIGRATOR_GEO_DATA_OPTION' ) ) {
	define( 'BH_SITE_MIGRATOR_GEO_DATA_OPTION', 'bh_site_migration_geo_data' );
}

if ( ! defined( 'BH_SITE_MIGRATOR_COUNTRY_CODE_OPTION' ) ) {
	define( 'BH_SITE_MIGRATOR_COUNTRY_CODE_OPTION', 'bh_site_migration_country_code' );
}

if ( ! defined( 'BH_SITE_MIGRATOR_PACKAGING_STATUS_OPTION' ) ) {
	define( 'BH_SITE_MIGRATOR_PACKAGING_STATUS_OPTION', 'bh_site_migrator_packaging_status' );
}

if ( ! defined( 'BH_SITE_MIGRATOR_PACKAGING_SUCCESS_OPTION' ) ) {
	define( 'BH_SITE_MIGRATOR_PACKAGING_SUCCESS_OPTION', 'bh_site_migrator_packaged_success' );
}

if ( ! defined( 'BH_SITE_MIGRATOR_PACKAGING_FAILED_OPTION' ) ) {
	define( 'BH_SITE_MIGRATOR_PACKAGING_FAILED_OPTION', 'bh_site_migrator_packaged_failed' );
}

if ( ! defined( 'BH_SITE_MIGRATOR_API_BASEURL' ) ) {
	define( 'BH_SITE_MIGRATOR_API_BASEURL', 'https://cwm.eigproserve.com/api/v1' );
}

if ( ! defined( 'BH_SITE_MIGRATOR_OPTIONS_LIST' ) ) {
	define(
		'BH_SITE_MIGRATOR_OPTIONS_LIST',
		array(
			BH_SITE_MIGRATOR_OPTION_NAME,
			BH_SITE_MIGRATOR_REGIONS_OPTION,
			BH_SITE_MIGRATOR_GEO_DATA_OPTION,
			BH_SITE_MIGRATOR_TOKEN_OPTION,
			BH_SITE_MIGRATOR_MIGRATION_ID_OPTION,
			BH_SITE_MIGRATOR_COUNTRY_CODE_OPTION,
			BH_SITE_MIGRATOR_PACKAGING_STATUS_OPTION,
			BH_SITE_MIGRATOR_PACKAGING_FAILED_OPTION,
			BH_SITE_MIGRATOR_PACKAGING_SUCCESS_OPTION,
		)
	);
}

if ( ! defined( 'BH_SITE_MIGRATOR_CAN_MIGRATE_TRANSIENT' ) ) {
	define( 'BH_SITE_MIGRATOR_CAN_MIGRATE_TRANSIENT', 'bluehost_site_migrator_can_migrate' );
}
