<?php

namespace BluehostSiteMigrator;

/**
 * Register admin menu, assets and other functionality WordPress.
 */
final class WP_Admin {

	/**
	 * Identifier for page and assets.
	 *
	 * @var string
	 */
	public static $slug = 'bluehost-site-migrator';

	/**
	 * Tap WordPress Hooks
	 *
	 * @return void
	 */
	public function __construct() {
		\add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
		\add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	/**
	 * Register the admin menu for site migrator
	 */
	public static function register_admin_menu() {
		\add_menu_page(
			__( 'Bluehost Site Migrator', 'bluehost_site_migrator' ),
			__( 'Site Migrator', 'bluehost_site_migrator' ),
			'manage_options',
			'bluehost-site-migrator',
			array( __CLASS__, 'render_page' ),
			'data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1OC4wMyA1OC4xMyI+PGRlZnM+PHN0eWxlPi5jbHMtMXtmaWxsOiNmZmY7fTwvc3R5bGU+PC9kZWZzPjx0aXRsZT5iaC13aGl0ZTwvdGl0bGU+PGcgaWQ9Il9Hcm91cF8iIGRhdGEtbmFtZT0iJmx0O0dyb3VwJmd0OyI+PGcgaWQ9Il9Hcm91cF8yIiBkYXRhLW5hbWU9IiZsdDtHcm91cCZndDsiPjxnIGlkPSJfR3JvdXBfMyIgZGF0YS1uYW1lPSImbHQ7R3JvdXAmZ3Q7Ij48cmVjdCBpZD0iX1BhdGhfIiBkYXRhLW5hbWU9IiZsdDtQYXRoJmd0OyIgY2xhc3M9ImNscy0xIiB3aWR0aD0iMTYuMiIgaGVpZ2h0PSIxNi4yMSIvPjxyZWN0IGlkPSJfUGF0aF8yIiBkYXRhLW5hbWU9IiZsdDtQYXRoJmd0OyIgY2xhc3M9ImNscy0xIiB4PSIyMC45MSIgd2lkdGg9IjE2LjIxIiBoZWlnaHQ9IjE2LjIxIi8+PHJlY3QgaWQ9Il9QYXRoXzMiIGRhdGEtbmFtZT0iJmx0O1BhdGgmZ3Q7IiBjbGFzcz0iY2xzLTEiIHg9IjQxLjgyIiB3aWR0aD0iMTYuMjEiIGhlaWdodD0iMTYuMjEiLz48cmVjdCBpZD0iX1BhdGhfNCIgZGF0YS1uYW1lPSImbHQ7UGF0aCZndDsiIGNsYXNzPSJjbHMtMSIgeT0iMjAuOTYiIHdpZHRoPSIxNi4yIiBoZWlnaHQ9IjE2LjIxIi8+PHJlY3QgaWQ9Il9QYXRoXzUiIGRhdGEtbmFtZT0iJmx0O1BhdGgmZ3Q7IiBjbGFzcz0iY2xzLTEiIHg9IjIwLjkxIiB5PSIyMC45NiIgd2lkdGg9IjE2LjIxIiBoZWlnaHQ9IjE2LjIxIi8+PHJlY3QgaWQ9Il9QYXRoXzYiIGRhdGEtbmFtZT0iJmx0O1BhdGgmZ3Q7IiBjbGFzcz0iY2xzLTEiIHg9IjQxLjgyIiB5PSIyMC45NiIgd2lkdGg9IjE2LjIxIiBoZWlnaHQ9IjE2LjIxIi8+PHJlY3QgaWQ9Il9QYXRoXzciIGRhdGEtbmFtZT0iJmx0O1BhdGgmZ3Q7IiBjbGFzcz0iY2xzLTEiIHk9IjQxLjkyIiB3aWR0aD0iMTYuMiIgaGVpZ2h0PSIxNi4yMSIvPjxyZWN0IGlkPSJfUGF0aF84IiBkYXRhLW5hbWU9IiZsdDtQYXRoJmd0OyIgY2xhc3M9ImNscy0xIiB4PSIyMC45MSIgeT0iNDEuOTIiIHdpZHRoPSIxNi4yMSIgaGVpZ2h0PSIxNi4yMSIvPjxyZWN0IGlkPSJfUGF0aF85IiBkYXRhLW5hbWU9IiZsdDtQYXRoJmd0OyIgY2xhc3M9ImNscy0xIiB4PSI0MS44MiIgeT0iNDEuOTIiIHdpZHRoPSIxNi4yMSIgaGVpZ2h0PSIxNi4yMSIvPjwvZz48L2c+PC9nPjwvc3ZnPg=='
		);
	}

	/**
	 * Register built assets with WordPress
	 */
	public static function register_assets() {
		$asset_file = BH_SITE_MIGRATOR_PLUGIN_BUILD_DIR . '/bh-site-migrator.asset.php';

		if ( is_readable( $asset_file ) ) {
			$asset = include_once $asset_file;

			\wp_register_script(
				self::$slug,
				BH_SITE_MIGRATOR_PLUGIN_BUILD_URL . '/bh-site-migrator.js',
				array_merge( $asset['dependencies'], array() ),
				$asset['version'],
				true
			);

			\wp_register_style(
				self::$slug,
				BH_SITE_MIGRATOR_PLUGIN_BUILD_URL . '/bh-site-migrator.css',
				array(),
				$asset['version']
			);

			\wp_enqueue_style( self::$slug );
			\wp_enqueue_script( self::$slug );
		}
	}

	/**
	 * Render DOM element for React SPA mount.
	 *
	 * @return void
	 */
	public static function render_page() {
		echo PHP_EOL;
		echo '<!-- BH:SITE:MIGRATOR -->';
		echo PHP_EOL;
		echo '<div id="bh-sm-app" class="bh-sm"></div>';
		echo PHP_EOL;
		echo '<!-- /BH:SITE:MIGRATOR -->';
		echo PHP_EOL;
	}
}
