<?php

namespace BluehostSiteMigrator\RestApi;

use BluehostSiteMigrator\MigrationManager\MigrationTasks;
use BluehostSiteMigrator\Utils\Options;
use BluehostSiteMigrator\Utils\Status;

/**
 * Controller to queue and manage tasks
 */
class MigrationTasksController extends \WP_REST_Controller {
	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace = 'bluehost-site-migrator/v1';

	/**
	 * The base of this controller's route
	 *
	 * @var string
	 */
	protected $rest_base = 'migration-tasks';

	/**
	 * Register the routes for this objects of the controller
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'queue_tasks' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			),
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/status',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_task_status' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			),
		);
	}

	/**
	 * Queue the tasks.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function queue_tasks( $request ) {
		$migration_tasks = new MigrationTasks();
		$migration_tasks->queue_tasks();

		Options::set( 'queued_packaging_tasks', true );

		return rest_ensure_response(
			array(
				'queued' => true,
			),
		);
	}

	/**
	 * Queue the tasks.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_task_status( $request ) {
		$status           = Status::get_status();
		$packaging_status = Status::get_packaging_status();
		$packaged_success = $packaging_status['success'];
		$packaged_failed  = $packaging_status['failed'];

		return rest_ensure_response(
			array(
				'status'           => $status,
				'packaged_success' => $packaged_success,
				'packaged_failed'  => $packaged_failed,
			),
		);
	}

	/**
	 * Check permissions for routes.
	 *
	 * @return bool|WP_Error
	 */
	public function check_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to access this endpoint.', 'bluehost-site-migrator' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}
}
