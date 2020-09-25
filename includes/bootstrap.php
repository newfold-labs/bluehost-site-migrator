<?php

require BH_SITE_MIGRATOR_DIR . 'includes/functions.php';
require BH_SITE_MIGRATOR_DIR . 'includes/wp-compatibility.php';

if ( ! defined( 'BH_SITE_MIGRATOR_API_BASEURL' ) ) {
	define( 'BH_SITE_MIGRATOR_API_BASEURL', 'https://cwm.eigproserve.com/api/v1' );
}

// Initialize options
BH_Site_Migrator_Options::fetch();
add_action( 'shutdown', array( 'BH_Site_Migrator_Options', 'maybe_persist' ) );

// Load translations
add_action( 'plugins_loaded', 'bh_site_migrator_load_plugin_textdomain' );

// Register deactivation hook
add_action( 'plugins_loaded', array( 'BH_Site_Migrator_Deactivate', 'register_listener' ) );

// Register pre-migration checks
add_action( 'plugins_loaded', array( 'BH_Site_Migrator_Migration_Checks', 'register' ) );

// Setup scheduled events
add_action( 'plugins_loaded', array( 'BH_Site_Migrator_Scheduled_Events', 'initialize' ) );

// Setup REST API endpoints
add_action( 'rest_api_init', array( 'BH_Site_Migrator_Utilities', 'rest_api_init' ) );

// Add admin menu page
add_action( 'admin_menu', array( 'BH_Site_Migrator_Admin_Page', 'add_menu_page' ) );

// Disable notifications on our page(s)
add_action( 'admin_print_styles', array( 'BH_Site_Migrator_Notification_Blocker', 'block_notifications' ) );

// Setup filters for migration packages
add_filter( 'bh_site_migrator_filter_files', 'bh_site_migrator_filter_files', 10, 2 );
add_filter( 'bh_site_migrator_filter_by_extension', 'bh_site_migrator_filter_by_extension' );
add_filter( 'bh_site_migrator_filter_by_name', 'bh_site_migrator_filter_by_name' );
add_filter( 'bh_site_migrator_filter_by_path', 'bh_site_migrator_filter_by_path', 99 );
