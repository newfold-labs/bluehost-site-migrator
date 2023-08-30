<?php

namespace BluehostSiteMigrator\RestApi;

/**
 * Initialize them APIs
 */
class RestApi {
	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register routes from all classes.
	 */
	public function register_routes() {
		$controllers = array(
			'BluehostSiteMigrator\\RestApi\\MigrationTasksController',
			'BluehostSiteMigrator\\RestApi\\MigrationCheckController',
			'BluehostSiteMigrator\\RestApi\\MigrationDataController',
		);

		foreach ( $controllers as $controller ) {
			$instance = new $controller();
			$instance->register_routes();
		}
	}
}
