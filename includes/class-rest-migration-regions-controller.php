<?php

/**
 * Class BH_Site_Migrator_REST_Migration_Regions_Controller
 */
class BH_Site_Migrator_REST_Migration_Regions_Controller extends WP_REST_Controller {

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
	protected $rest_base = 'migration-regions';

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
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

	}

	/**
	 * Fetch migration ID.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		return rest_ensure_response(
			array(
				'regions'     => $this->get_regions(),
				'countryCode' => get_option( 'bh_site_migration_country_code', '' ),
				'migrationId' => get_option( 'bh_site_migration_id', null ),
			)
		);
	}

	/**
	 * Get active regions.
	 *
	 * @return array
	 */
	public function get_regions() {
		$regions = array();
		$items   = get_option( 'bh_site_migration_region_urls', null );
		foreach ( $items as $item ) {
			if ( isset( $item['enabled'] ) && true === $item['enabled'] ) {
				$regions[] = $item;
			}
		}
		return $regions;
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
