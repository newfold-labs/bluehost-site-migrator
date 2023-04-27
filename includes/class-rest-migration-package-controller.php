<?php

use NewfoldLabs\WP\Module\Tasks\Models\Task;
use NewfoldLabs\WP\Module\Tasks\Models\TaskResult;

/**
 * Class BH_Site_Migrator_REST_Migration_Package_Controller
 */
class BH_Site_Migrator_REST_Migration_Package_Controller extends WP_REST_Controller {

	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace = 'bluehost-site-migrator/v1';

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	protected $rest_base = 'migration-package';

	/**
	 * Register the routes for this objects of the controller.
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_items' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			"/{$this->rest_base}/queue-tasks",
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'queue_tasks' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			"/{$this->rest_base}/(?P<type>[\w-]+)",
			array(
				'args' => array(
					'type' => array(
						'validate_callback' => function ( $param ) {
							return BH_Site_Migrator_Migration_Package::is_valid_type( $param );
						},
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			"/{$this->rest_base}/(?P<type>[\w-]+)/is-valid",
			array(
				'args' => array(
					'type' => array(
						'validate_callback' => function ( $param ) {
							return BH_Site_Migrator_Migration_Package::is_valid_type( $param );
						},
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'is_package_valid' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			"/{$this->rest_base}/(?P<type>[\w-]+)/is-scheduled",
			array(
				'args' => array(
					'type' => array(
						'validate_callback' => function ( $param ) {
							return BH_Site_Migrator_Migration_Package::is_valid_type( $param );
						},
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'is_package_scheduled' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
	}

	/**
	 * Generate migration package.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {

		// Give us extra time, if possible.
		set_time_limit( 90 );

		try {

			// Create package
			$package_data = BH_Site_Migrator_Migration_Package::create( $request->get_param( 'type' ) );

			// Remove any unreferenced packages
			BH_Site_Migrator_Migration_Package::delete_orphans();

			return rest_ensure_response( $package_data );

		} catch ( Exception $e ) {

			return rest_ensure_response( new WP_Error( 'package_error', $e->getMessage() ) );

		}

	}


	/**
	 * Fetch migration packages.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		return rest_ensure_response( BH_Site_Migrator_Migration_Package::fetch( $request->get_param( 'type' ) ) );
	}

	/**
	 * Delete migration package.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		return rest_ensure_response( BH_Site_Migrator_Migration_Package::delete( $request->get_param( 'type' ) ) );
	}

	/**
	 * Fetch all migration packages.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$packages = BH_Site_Migrator_Migration_Package::fetch_all();

		// Ensure that empty arrays are properly converted to empty objects!
		foreach ( $packages as $key => $value ) {
			if ( empty( $value ) ) {
				$packages[ $key ] = (object) $value;
			}
		}

		return rest_ensure_response( $packages );
	}

	/**
	 * Queue in packaging tasks if not already queued.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function queue_tasks( $request ) {
		try {
			$queued_packaging_tasks = BH_Site_Migrator_Options::get( 'queued_packaging_tasks', false );

			if ( ! $queued_packaging_tasks ) {
				BH_Site_Migrator_Utilities::queue_packaging_tasks();
				BH_Site_Migrator_Options::set( 'queued_packaging_tasks', true );
			}

			return rest_ensure_response( true );
		} catch ( Exception $e ) {
			return rest_ensure_response( new WP_Error( 'queue-error', $e->getMessage() ) );
		}
	}

	/**
	 * Delete all migration packages.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_items() {
		return rest_ensure_response( BH_Site_Migrator_Migration_Package::delete_all() );
	}

	/**
	 * Check if migration package is still valid.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function is_package_valid( $request ) {
		return rest_ensure_response( BH_Site_Migrator_Migration_Package::is_valid_package( $request->get_param( 'type' ) ) );
	}

	/**
	 * Check if the task to migrate the package is scheduled
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function is_package_scheduled( $request ) {
		// Try to get any task scheduled for the package
		$scheduled_tasks_for_package = Task::get_tasks_with_name( 'package_' . $request->get_param( 'type' ) );
		$failed_tasks_for_package    = TaskResult::get_failed_tasks_by_name( 'package_' . $request->get_param( 'type' ) );
		$scheduled                   = count( $scheduled_tasks_for_package ) > 0 || count( $failed_tasks_for_package ) === 0;

		return rest_ensure_response( $scheduled );
	}

	/**
	 * Check permissions for routes.
	 *
	 * @return bool|WP_Error
	 */
	public function check_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to access this endpoint.', 'bluehost-site-migrator' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

}
