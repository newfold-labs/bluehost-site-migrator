<?php
/**
 * Plugin Name:  Bluehost Site Migrator
 * Plugin URI:   https://github.com/bluehost/bluehost-migration-pugin
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

require BH_SITE_MIGRATOR_DIR . 'includes/functions.php';
require BH_SITE_MIGRATOR_DIR . 'includes/wp-compatibility.php';
require BH_SITE_MIGRATOR_DIR . 'includes/class-autoloader.php';

// Register class map autoloader
BH_Site_Migrator_Class_Loader::register_class_map(
	array(
		'BH_Site_Migrator_Admin_Page'                     => BH_SITE_MIGRATOR_DIR . 'includes/class-admin-page.php',
		'BH_Site_Migrator_Plugin_Data'                    => BH_SITE_MIGRATOR_DIR . 'includes/class-data-plugin.php',
		'BH_Site_Migrator_Theme_Data'                     => BH_SITE_MIGRATOR_DIR . 'includes/class-data-theme.php',
		'BH_Site_Migrator_Deactivate'                     => BH_SITE_MIGRATOR_DIR . 'includes/class-deactivate.php',
		'BH_Site_Migrator_Filter_Iterator'                => BH_SITE_MIGRATOR_DIR . 'includes/class-filter-iterator.php',
		'BH_Site_Migrator_Manifest'                       => BH_SITE_MIGRATOR_DIR . 'includes/class-manifest.php',
		'BH_Site_Migrator_Plugin_Manifest'                => BH_SITE_MIGRATOR_DIR . 'includes/class-manifest-plugin.php',
		'BH_Site_Migrator_Theme_Manifest'                 => BH_SITE_MIGRATOR_DIR . 'includes/class-manifest-theme.php',
		'BH_Site_Migrator_WP_Manifest'                    => BH_SITE_MIGRATOR_DIR . 'includes/class-manifest-wp.php',
		'BH_Site_Migrator_Migration_Checks'               => BH_SITE_MIGRATOR_DIR . 'includes/class-migration-checks.php',
		'BH_Site_Migrator_Migration_Package'              => BH_SITE_MIGRATOR_DIR . 'includes/class-migration-package.php',
		'BH_Site_Migrator_Notification_Blocker'           => BH_SITE_MIGRATOR_DIR . 'includes/class-notification-blocker.php',
		'BH_Site_Migrator_Options'                        => BH_SITE_MIGRATOR_DIR . 'includes/class-options.php',
		'BH_Site_Migrator_Database_Packager'              => BH_SITE_MIGRATOR_DIR . 'includes/class-packager-database.php',
		'BH_Site_Migrator_Dropins_Packager'               => BH_SITE_MIGRATOR_DIR . 'includes/class-packager-dropins.php',
		'BH_Site_Migrator_Packager_Factory'               => BH_SITE_MIGRATOR_DIR . 'includes/class-packager-factory.php',
		'BH_Site_Migrator_MU_Plugins_Packager'            => BH_SITE_MIGRATOR_DIR . 'includes/class-packager-mu-plugins.php',
		'BH_Site_Migrator_Plugins_Packager'               => BH_SITE_MIGRATOR_DIR . 'includes/class-packager-plugins.php',
		'BH_Site_Migrator_Root_Packager'                  => BH_SITE_MIGRATOR_DIR . 'includes/class-packager-root.php',
		'BH_Site_Migrator_Themes_Packager'                => BH_SITE_MIGRATOR_DIR . 'includes/class-packager-themes.php',
		'BH_Site_Migrator_Uploads_Packager'               => BH_SITE_MIGRATOR_DIR . 'includes/class-packager-uploads.php',
		'BH_Site_Migrator_Registry'                       => BH_SITE_MIGRATOR_DIR . 'includes/class-registry.php',
		'BH_Site_Migrator_REST_Can_We_Migrate_Controller' => BH_SITE_MIGRATOR_DIR . 'includes/class-rest-can-we-migrate-controller.php',
		'BH_Site_Migrator_REST_Manifest_Controller'       => BH_SITE_MIGRATOR_DIR . 'includes/class-rest-manifest-controller.php',
		'BH_Site_Migrator_REST_Migration_Id_Controller'   => BH_SITE_MIGRATOR_DIR . 'includes/class-rest-migration-id-controller.php',
		'BH_Site_Migrator_REST_Migration_Package_Controller' => BH_SITE_MIGRATOR_DIR . 'includes/class-rest-migration-package-controller.php',
		'BH_Site_Migrator_Scheduled_Events'               => BH_SITE_MIGRATOR_DIR . 'includes/class-scheduled-events.php',
		'BH_Site_Migrator_Utilities'                      => BH_SITE_MIGRATOR_DIR . 'includes/class-utilities.php',
		'BH_Site_Migrator_Packager'                       => BH_SITE_MIGRATOR_DIR . 'includes/interface-packager.php',
	)
);

register_activation_hook( __FILE__, 'bh_site_migrator_on_activation' );

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
