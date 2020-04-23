<?php

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
					'callback'            => array( $this, 'send_manifest' ),
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
	 * Send manifest to Bluehost.
	 *
	 * // TODO: Integrate with Migration API
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function send_manifest() {

		// phpcs:disable
		/*
		$response = wp_remote_post( 'https://', array(
			'body' => array(
				'key'   => '',
				'files' => BH_Site_Migrator_Migration_Package::fetch_all(),
			),
		) );

		$status_code = (int) wp_remote_retrieve_response_code( $response );

		if ( $status_code !== 201 ) {
			return new WP_Error( 'code', 'message', array(
				'status_code' => $status_code,
			) );
		}
		*/
		// phpcs:enable

		BH_Site_Migrator_Options::set( 'isComplete', true );
		BH_Site_Migrator_Scheduled_Events::schedule_migration_package_purge();

		return rest_ensure_response( true );
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
