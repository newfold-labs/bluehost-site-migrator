<?php

/**
 * Class BH_Site_Migrator_REST_Can_We_Migrate_Controller
 */
class BH_Site_Migrator_REST_Can_We_Migrate_Controller extends WP_REST_Controller {

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
	protected $rest_base = 'can-we-migrate';

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
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

	}

	/**
	 * Check if we can migrate this site.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {

		$geo = $request->get_json_params();
		update_option( 'bh_site_migration_geo_data', $geo );
		update_option( 'bh_site_migration_country_code', bh_site_migrator_data_get( $geo, 'country.code', '' ) );

		$can_migrate = BH_Site_Migrator_Migration_Checks::run();

		return rest_ensure_response(
			array(
				'can_migrate' => $can_migrate,
				'results'     => BH_Site_Migrator_Migration_Checks::$results,
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
