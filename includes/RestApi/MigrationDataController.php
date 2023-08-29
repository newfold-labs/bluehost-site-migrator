<?php

namespace BluehostSiteMigrator\RestApi;

/**
 * Class MigrationDataController
 */
class MigrationDataController extends \WP_REST_Controller {

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
	protected $rest_base = 'migration-data';

	/**
	 * Register the routes for this objects of the controller.
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
	}

	/**
	 * Fetch migration ID.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		return rest_ensure_response(
			array(
				'regions'     => get_option( BH_SITE_MIGRATOR_REGIONS_OPTION, null ),
				'countryCode' => get_option( BH_SITE_MIGRATOR_COUNTRY_CODE_OPTION, '' ),
				'migrationId' => get_option( BH_SITE_MIGRATOR_MIGRATION_ID_OPTION, null ),
			)
		);
	}

	/**
	 * Check permissions for routes.
	 *
	 * @return bool|\WP_Error
	 */
	public function check_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to access this endpoint.', 'bluehost-site-migrator' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
}
