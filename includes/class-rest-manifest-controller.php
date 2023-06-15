<?php

use NewfoldLabs\WP\Module\Tasks\Models\TaskResult;

/**
 * Class BH_Site_Migrator_REST_Manifest_Controller
 */
class BH_Site_Migrator_REST_Manifest_Controller extends WP_REST_Controller {

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
	protected $rest_base = 'manifest';

	/**
	 * Register the routes for this objects of the controller.
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
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
			'/' . $this->rest_base . '/send',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'send_files_manifest' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/report-errors',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'report_failed_migration' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

	}

	/**
	 * Generate manifest.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		return rest_ensure_response( BH_Site_Migrator_Manifest::create() );
	}


	/**
	 * Fetch manifest.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		return rest_ensure_response( BH_Site_Migrator_Manifest::fetch() );
	}

	/**
	 * Delete manifest.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		return rest_ensure_response( BH_Site_Migrator_Manifest::delete() );
	}

	/**
	 * Send files manifest to Bluehost.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function send_files_manifest() {
		$migration_id = get_option( 'bh_site_migration_id' );
		$files        = array_values( array_filter( BH_Site_Migrator_Migration_Package::fetch_all() ) );
		$payload      = wp_json_encode( $files, JSON_PRETTY_PRINT );
		$response     = wp_remote_post(
			BH_SITE_MIGRATOR_API_BASEURL . "/migration/{$migration_id}/files",
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
			return new WP_Error(
				'migration_payload_failure',
				__( 'An error occured when delivering the migration payload.', 'bluehost-site-migrator' ),
				array(
					'status_code' => $status_code,
				)
			);
		}

		BH_Site_Migrator_Options::set( 'isComplete', true );
		BH_Site_Migrator_Scheduled_Events::schedule_migration_package_purge();

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
		$migration_id = get_option( 'bh_site_migration_id' );

		// Get all failed tasks
		$failed_tasks          = TaskResult::get_failed_tasks();
		$relevant_failed_tasks = array();

		$package_task_names = BH_Site_Migrator_Utilities::get_packaging_task_names();

		foreach ( $failed_tasks as $failed_task ) {
			if ( in_array( $failed_task->task_name, $package_task_names, true ) ) {
				array_push( $relevant_failed_tasks, $failed_task );
			}
		}

		if ( empty( $relevant_failed_tasks ) ) {
			return new WP_Error(
				'migration_error_log_failure',
				__( 'No failed tasks found.', 'bluehost-site-migrator' ),
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
			return new WP_Error(
				'migration_payload_failure',
				__( 'An error occured when delivering the migration payload.', 'bluehost-site-migrator' ),
				array(
					'status_code' => $status_code,
				)
			);
		}

		BH_Site_Migrator_Scheduled_Events::schedule_migration_package_purge();

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
			return new \WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to access this endpoint.', 'bluehost-site-migrator' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

}
