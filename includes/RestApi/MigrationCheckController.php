<?php

namespace BluehostSiteMigrator\RestApi;

use BluehostSiteMigrator\MigrationChecks\Checker;
use BluehostSiteMigrator\Utils\Options;
use BluehostSiteMigrator\Utils\Status;

/**
 * Try the migration check
 */
class MigrationCheckController extends \WP_REST_Controller {

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
	protected $rest_base = 'migration-check';

	/**
	 * Register the routes for this objects of the controller.
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Get the result for compatibility check
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/compatible',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_compatibility' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Get the current migration stage, i.e. compatibility check, tasks queued etc.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/step',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_current_step' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
	}

	/**
	 * Check if we can migrate this site.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {

		$geo = $request->get_json_params();
		update_option( BH_SITE_MIGRATOR_GEO_DATA_OPTION, $geo );
		update_option( BH_SITE_MIGRATOR_COUNTRY_CODE_OPTION, \nfd_bhsm_data_get( $geo, 'country.code', '' ) );

		$can_migrate = Checker::run();

		return rest_ensure_response(
			array(
				'can_migrate' => $can_migrate,
				'results'     => Checker::$results,
			)
		);
	}

	/**
	 * Get the compatibility option directly.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_compatibility( $request ) {
		$compatible = Options::get( 'isCompatible', null );
		return rest_ensure_response(
			array(
				'compatible' => $compatible,
				'checked'    => null !== $compatible ? true : false,
			)
		);
	}

	/**
	 * Get the current migration step, basically gives information about all progress related options in DB
	 * except for the status message and progress of transfer.
	 *
	 * @param \WP_REST_Request $request The Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_current_step( $request ) {
		$compatible       = Options::get( 'isCompatible', null );
		$transfer_queued  = Options::get( 'queued_packaging_tasks', false );
		$cancelled        = Options::get( 'cancelled_packaging', false );
		$packaging_status = Status::get_packaging_status();
		$packaged_success = $packaging_status['success'];
		$packaged_failed  = $packaging_status['failed'];
		return rest_ensure_response(
			array(
				'compatible'       => $compatible,
				'transfer_queued'  => $transfer_queued,
				'checked'          => null !== $compatible ? true : false,
				'packaged_success' => $packaged_success,
				'packaged_failed'  => $packaged_failed,
				'cancelled'        => $cancelled,
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
