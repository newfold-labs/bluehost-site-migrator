<?php
/**
 * Plugin Name:  Bluehost Move
 * Plugin URI:   https://github.com/bluehost/bluehost-migration-pugin
 * Description:  Quickly and easily migrate your website to Bluehost.
 * Version:      1.0
 * Author:       Bluehost
 * Author URI:   https://www.bluehost.com/
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  bluehost-move
 * Domain Path:  /languages
 *
 * @package bluehost-move
 */

define( 'BH_MOVE_FILE', __FILE__ );
define( 'BH_MOVE_DIR', plugin_dir_path( __FILE__ ) );
define( 'BH_MOVE_URL', plugin_dir_url( __FILE__ ) );

require dirname( __FILE__ ) . '/includes/wp-compatibility.php';
require dirname( __FILE__ ) . '/includes/class-autoloader.php';

// Register class map autoloader
BH_Move_Class_Loader::register_class_map(
	array(
		'BH_Move_Admin_Page'                        => BH_MOVE_DIR . 'includes/class-admin-page.php',
		'BH_Move_Plugin_Data'                       => BH_MOVE_DIR . 'includes/class-data-plugin.php',
		'BH_Move_Theme_Data'                        => BH_MOVE_DIR . 'includes/class-data-theme.php',
		'BH_Move_Deactivate'                        => BH_MOVE_DIR . 'includes/class-deactivate.php',
		'BH_Move_Manifest'                          => BH_MOVE_DIR . 'includes/class-manifest.php',
		'BH_Move_Plugin_Manifest'                   => BH_MOVE_DIR . 'includes/class-manifest-plugin.php',
		'BH_Move_Theme_Manifest'                    => BH_MOVE_DIR . 'includes/class-manifest-theme.php',
		'BH_Move_WP_Manifest'                       => BH_MOVE_DIR . 'includes/class-manifest-wp.php',
		'BH_Move_Migration_Checks'                  => BH_MOVE_DIR . 'includes/class-migration-checks.php',
		'BH_Move_Migration_Package'                 => BH_MOVE_DIR . 'includes/class-migration-package.php',
		'BH_Move_Options'                           => BH_MOVE_DIR . 'includes/class-options.php',
		'BH_Move_Database_Packager'                 => BH_MOVE_DIR . 'includes/class-packager-database.php',
		'BH_Move_Dropins_Packager'                  => BH_MOVE_DIR . 'includes/class-packager-dropins.php',
		'BH_Move_Packager_Factory'                  => BH_MOVE_DIR . 'includes/class-packager-factory.php',
		'BH_Move_MU_Plugins_Packager'               => BH_MOVE_DIR . 'includes/class-packager-mu-plugins.php',
		'BH_Move_Plugins_Packager'                  => BH_MOVE_DIR . 'includes/class-packager-plugins.php',
		'BH_Move_Themes_Packager'                   => BH_MOVE_DIR . 'includes/class-packager-themes.php',
		'BH_Move_Uploads_Packager'                  => BH_MOVE_DIR . 'includes/class-packager-uploads.php',
		'BH_Move_Registry'                          => BH_MOVE_DIR . 'includes/class-registry.php',
		'BH_Move_REST_Can_We_Migrate_Controller'    => BH_MOVE_DIR . 'includes/class-rest-can-we-migrate-controller.php',
		'BH_Move_REST_Manifest_Controller'          => BH_MOVE_DIR . 'includes/class-rest-manifest-controller.php',
		'BH_Move_REST_Migration_Package_Controller' => BH_MOVE_DIR . 'includes/class-rest-migration-package-controller.php',
		'BH_Move_Scheduled_Events'                  => BH_MOVE_DIR . 'includes/class-scheduled-events.php',
		'BH_Move_Utilities'                         => BH_MOVE_DIR . 'includes/class-utilities.php',
		'BH_Move_WP_Filter_Iterator'                => BH_MOVE_DIR . 'includes/class-wp-filter-iterator.php',
		'BH_Move_Zip_Filter_Iterator'               => BH_MOVE_DIR . 'includes/class-zip-filter-iterator.php',
		'BH_Move_Packager'                          => BH_MOVE_DIR . 'includes/interface-packager.php',
	)
);

// Initialize options
BH_Move_Options::fetch();
add_action( 'shutdown', array( 'BH_Move_Options', 'maybe_persist' ) );

// Register deactivation hook
add_action( 'plugins_loaded', array( 'BH_Move_Deactivate', 'register_listener' ) );

// Register pre-migration checks
add_action( 'plugins_loaded', array( 'BH_Move_Migration_Checks', 'register' ) );

// Setup scheduled events
add_action( 'plugins_loaded', array( 'BH_Move_Scheduled_Events', 'initialize' ) );

// Setup REST API endpoints
add_action( 'rest_api_init', array( 'BH_Move_Utilities', 'rest_api_init' ) );

// Add admin menu page
add_action( 'admin_menu', array( 'BH_Move_Admin_Page', 'add_menu_page' ) );
