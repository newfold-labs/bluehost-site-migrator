<?php
/**
 * Plugin Name:  Bluehost Site Migrator
 * Plugin URI:   https://wordpress.org/plugins/bluehost-migration-pugin
 * Description:  Quickly and easily migrate your website to Bluehost.
 * Version:      1.0
 * Author:       Bluehost
 * Author URI:   https://www.bluehost.com/
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  bluehost-site-migrator
 * Domain Path:  /languages
 *
 * @package bluehost-site-migrator
 */

define( 'BH_SITE_MIGRATOR_VERSION', '1.0' );
define( 'BH_SITE_MIGRATOR_FILE', __FILE__ );
define( 'BH_SITE_MIGRATOR_DIR', plugin_dir_path( __FILE__ ) );
define( 'BH_SITE_MIGRATOR_URL', plugin_dir_url( __FILE__ ) );

require dirname( __FILE__ ) . '/vendor/autoload.php';

// Check plugin requirements
global $pagenow;
if ( 'plugins.php' === $pagenow ) {
	$plugin_check = new WP_Forge_Plugin_Check( __FILE__ );

	$plugin_check->min_php_version    = '5.3';
	$plugin_check->min_wp_version     = '4.7';
	$plugin_check->req_php_extensions = array( 'json', 'zlib' );

	$plugin_check->check_plugin_requirements();
}

require dirname( __FILE__ ) . '/includes/bootstrap.php';
