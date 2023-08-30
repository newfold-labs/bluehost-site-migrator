<?php
/**
 * Bluehost Site Migrator
 *
 * @package           BluehostSiteMigrator
 * @author            Bluehost
 * @copyright         Copyright 2020 by Bluehost - All rights reserved.
 * @license           GPL2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Bluehost Site Migrator
 * Plugin URI:        https://wordpress.org/plugins/bluehost-site-migrator
 * Description:       Quickly and easily migrate your website to Bluehost.
 * Version:           1.0.12
 * Requires PHP:      5.6
 * Requires at least: 4.7
 * Author:            Bluehost
 * Author URI:        https://www.bluehost.com/
 * Text Domain:       bluehost-site-migrator
 * Domain Path:       /languages
 * License:           GPL V2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/constants.php';

// Check plugin requirements
global $pagenow;
if ( 'plugins.php' === $pagenow ) {
	$plugin_check = new WP_Forge_Plugin_Check( __FILE__ );

	$plugin_check->min_php_version    = '5.6';
	$plugin_check->min_wp_version     = '4.7';
	$plugin_check->req_php_extensions = array( 'json', 'zlib' );

	$plugin_check->check_plugin_requirements();
}


// Include functions
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';

register_activation_hook( __FILE__, 'nfd_tasks_setup_tables' );
register_activation_hook( __FILE__, 'nfd_bhsm_set_redirect' );
register_deactivation_hook( __FILE__, 'nfd_tasks_purge_tables' );
register_deactivation_hook( __FILE__, 'nfd_bhsm_purge_all' );
add_action( 'admin_init', 'nfd_bhsm_redirect' );

// Initialize the Admin page
new BluehostSiteMigrator\WP_Admin();

// Initialize the REST APIs
new BluehostSiteMigrator\RestApi\RestApi();

// Initialize options
BluehostSiteMigrator\Utils\Options::fetch();

// Add the migration check filters
BluehostSiteMigrator\MigrationChecks\Checker::register();

// persist options on shutdown
add_action( 'shutdown', array( 'BluehostSiteMigrator\Utils\Options', 'maybe_persist' ) );
