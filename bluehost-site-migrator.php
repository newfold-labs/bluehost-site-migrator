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
 * Version:           1.0.14
 * Requires PHP:      5.6
 * Requires at least: 4.7
 * Author:            Bluehost
 * Author URI:        https://www.bluehost.com/
 * Text Domain:       bluehost-site-migrator
 * Domain Path:       /languages
 * License:           GPL V2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

define( 'BH_SITE_MIGRATOR_VERSION', '1.0.14' );
define( 'BH_SITE_MIGRATOR_FILE', __FILE__ );
define( 'BH_SITE_MIGRATOR_DIR', plugin_dir_path( __FILE__ ) );
define( 'BH_SITE_MIGRATOR_URL', plugin_dir_url( __FILE__ ) );

require dirname( __FILE__ ) . '/vendor/autoload.php';

register_activation_hook( __FILE__, 'nfd_tasks_setup_tables' );
register_deactivation_hook( __FILE__, 'nfd_tasks_purge_tables' );

// Check plugin requirements
global $pagenow;
if ( 'plugins.php' === $pagenow ) {
	$plugin_check = new WP_Forge_Plugin_Check( __FILE__ );

	$plugin_check->min_php_version    = '5.6';
	$plugin_check->min_wp_version     = '4.7';
	$plugin_check->req_php_extensions = array( 'json', 'zlib' );

	$plugin_check->check_plugin_requirements();
}

require dirname( __FILE__ ) . '/includes/bootstrap.php';
