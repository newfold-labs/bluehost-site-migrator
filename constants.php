<?php

define( 'BH_SITE_MIGRATOR_VERSION', '1.0.12' );
define( 'BH_SITE_MIGRATOR_CIPHER_NAME', 'AES-256-CBC' );
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

if ( ! defined( 'BH_SITE_MIGRATOR_API_BASEURL' ) ) {
	define( 'BH_SITE_MIGRATOR_API_BASEURL', 'http://localhost:4040/api/v1' );
}
