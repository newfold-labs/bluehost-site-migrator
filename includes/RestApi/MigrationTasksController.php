<?php

namespace BluehostSiteMigrator\RestApi;

use BluehostSiteMigrator\MigrationManager\MigrationTasks;
use BluehostSiteMigrator\Utils\Common;
use BluehostSiteMigrator\Utils\Options;
use BluehostSiteMigrator\Utils\Status;
use NewfoldLabs\WP\Module\Tasks\Models\TaskResult;

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
			)
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
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/cancel',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'cancel_transfer' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/send-files',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'send_files' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/report-errors',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'report_failed_migration' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
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
			)
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
			)
		);
	}

	/**
	 * Cancel transfer purge tasks and reset queued transfer
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function cancel_transfer( $request ) {
		Common::purge_tasks();
		Options::set( 'queued_packaging_tasks', false );
		// Reset the status
		delete_option( BH_SITE_MIGRATOR_PACKAGING_STATUS_OPTION );
		delete_option( BH_SITE_MIGRATOR_PACKAGING_SUCCESS_OPTION );
		delete_option( BH_SITE_MIGRATOR_PACKAGING_FAILED_OPTION );

		Options::set( 'cancelled_packaging', true );

		// Delete the archived files if any
		nfd_bhsm_delete_directory( nfd_bhsm_storage_path() );

		return rest_ensure_response(
			array(
				'success' => true,
			)
		);
	}

	/**
	 * Send the files list to CWM
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function send_files( $request ) {
		$migration_id = get_option( BH_SITE_MIGRATOR_MIGRATION_ID_OPTION );
		$files        = array_values( Options::get( 'packaged_files', array() ) );
		$payload      = \wp_json_encode( $files, JSON_PRETTY_PRINT );
		$response     = \wp_remote_post(
			BH_SITE_MIGRATOR_API_BASEURL . "/migration/{$migration_id}/files",
			array(
				'headers'   => array(
					'Content-Type' => 'application/json',
					'x-auth-token' => get_option( BH_SITE_MIGRATOR_TOKEN_OPTION ),
				),
				'body'      => $payload,
				'sslverify' => \is_ssl(),
			)
		);

		$status_code = (int) wp_remote_retrieve_response_code( $response );

		if ( 200 !== $status_code ) {
			Status::set_packaging_success( false );
			return new \WP_Error(
				'migration_payload_failure',
				'An error occurred when delivering the migration payload.',
				array(
					'status_code' => $status_code,
				)
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'files'   => $files,
			)
		);
	}

	/**
	 * Send files manifest to Bluehost.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function report_failed_migration() {
		Status::set_packaging_success( false );
		$migration_id = get_option( BH_SITE_MIGRATOR_MIGRATION_ID_OPTION );

		// Get all failed tasks
		$failed_tasks          = TaskResult::get_failed_tasks();
		$relevant_failed_tasks = array();

		$package_task_names = Common::get_packaging_task_names();

		foreach ( $failed_tasks as $failed_task ) {
			if ( in_array( $failed_task->task_name, $package_task_names, true ) ) {
				array_push( $relevant_failed_tasks, $failed_task );
			}
		}

		if ( empty( $relevant_failed_tasks ) ) {
			return new \WP_Error(
				'migration_error_log_failure',
				'No failed tasks found.',
				array(
					'status_code' => 404,
				)
			);
		}

		$error_logs = wp_json_encode(
			$relevant_failed_tasks,
			JSON_PRETTY_PRINT
		);
		$payload    = wp_json_encode(
			array( 'runtimeLogs' => $error_logs ),
			JSON_PRETTY_PRINT
		);
		$response   = wp_remote_post(
			BH_SITE_MIGRATOR_API_BASEURL . "/migration/{$migration_id}/reportFailed",
			array(
				'headers'   => array(
					'Content-Type' => 'application/json',
					'x-auth-token' => get_option( 'bh_site_migration_token' ),
				),
				'body'      => $payload,
				'sslverify' => is_ssl(),
			)
		);

		$status_code = (int) wp_remote_retrieve_response_code( $response );

		if ( 200 !== $status_code ) {
			return new \WP_Error(
				'migration_payload_failure',
				'An error occurred when delivering the migration payload.',
				array(
					'status_code' => $status_code,
				)
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'errors'  => wp_json_encode( $relevant_failed_tasks ),
			)
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
