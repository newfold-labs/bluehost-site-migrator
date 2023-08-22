<?php

define( 'BH_SITE_MIGRATOR_VERSION', '1.0.12' );
define( 'BH_SITE_MIGRATOR_CIPHER_NAME', 'AES-256-CBC' );
define( 'BH_SITE_MIGRATOR_DIR', __DIR__ );
define( 'BH_SITE_MIGRATOR_BUILD_DIR', __DIR__ . '/build/' . BH_SITE_MIGRATOR_VERSION );

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
