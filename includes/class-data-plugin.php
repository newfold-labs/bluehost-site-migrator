<?php

/**
 * Class BH_Site_Migrator_Plugin_Data
 */
class BH_Site_Migrator_Plugin_Data {

	/**
	 * Plugin basename.
	 *
	 * @var string
	 */
	protected $basename;

	/**
	 * Plugin file headers.
	 *
	 * @var array
	 */
	protected $headers;

	/**
	 * WP.org plugin data.
	 *
	 * @var array
	 */
	protected $wp_org;

	/**
	 * Constructor.
	 *
	 * @param string $basename Plugin basename.
	 */
	public function __construct( $basename ) {
		$this->basename = $basename;
	}

	/**
	 * Get the number of active installs.
	 *
	 * @return int|null Number of active installs or null if there is no data available.
	 */
	public function active_installs() {
		$active_installs = $this->get_wp_org_data( 'active_installs', null );

		return is_null( $active_installs ) ? null : absint( $active_installs );
	}

	/**
	 * Get plugin author name.
	 *
	 * @return string
	 */
	public function author() {
		return $this->get_plugin_header( 'Author' );
	}

	/**
	 * Get plugin author URL.
	 *
	 * @return string
	 */
	public function author_url() {
		return $this->get_plugin_header( 'AuthorURI' );
	}

	/**
	 * Get plugin basename.
	 *
	 * @return string
	 */
	public function basename() {
		return $this->basename;
	}

	/**
	 * Get plugin description.
	 *
	 * @return string
	 */
	public function description() {
		return $this->get_plugin_header( 'Description' );
	}

	/**
	 * Check if plugin is active.
	 *
	 * @return bool
	 */
	public function is_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $this->basename() );
	}

	/**
	 * Check if plugin is on WP.org.
	 *
	 * @return bool
	 */
	public function is_wp_org() {
		return wp_validate_boolean( $this->get_wp_org_data( 'exists', false ) );
	}

	/**
	 * Get plugin last modified date.
	 *
	 * @return DateTime|null Date in GMT or null on failure.
	 */
	public function last_modified() {
		$last_modified = null;
		$timestamp     = filemtime( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->basename() );
		if ( false !== $timestamp ) {
			$modified = DateTime::createFromFormat( 'U', $timestamp );
			if ( $modified ) {
				$modified->setTimezone( new DateTimeZone( 'GMT' ) );
				$last_modified = $modified;
			}
		}

		return $last_modified;
	}

	/**
	 * Get last updated date.
	 *
	 * @return DateTime|null Date in GMT or null on failure.
	 */
	public function last_updated() {

		$last_updated = null;

		$updated = $this->get_wp_org_data( 'last_updated' );

		if ( $updated ) {
			try {
				$last_updated = new DateTime( $updated );
			} catch ( Exception $exception ) { // phpcs:ignore
				$last_updated = null;
			}
		}

		return $last_updated;
	}

	/**
	 * Get plugin name.
	 *
	 * @return string
	 */
	public function name() {
		return $this->get_plugin_header( 'Name' );
	}

	/**
	 * Get the new plugin version that is available for upgrade.
	 *
	 * @return string
	 */
	public function new_version() {
		return (string) $this->get_plugin_update_data( 'new_version', '' );
	}

	/**
	 * Check if plugin can only be activated network-wide.
	 *
	 * @return bool
	 */
	public function is_network_only() {
		return wp_validate_boolean( $this->get_plugin_header( 'Network' ) );
	}

	/**
	 * Get required PHP version.
	 *
	 * @return string
	 */
	public function requires_php_version() {

		$php_version = $this->get_plugin_header( 'RequiresPHP' );

		if ( empty( $php_version ) && $this->is_wp_org() ) {
			$php_version = (string) $this->get_wp_org_data( 'requires_php' );
		}

		return $php_version;
	}

	/**
	 * Get required WordPress version.
	 *
	 * @return string
	 */
	public function requires_wp_version() {

		$wp_version = $this->get_plugin_header( 'RequiresWP' );

		if ( empty( $wp_version ) && $this->is_wp_org() ) {
			$wp_version = (string) $this->get_wp_org_data( 'requires' );
		}

		return $wp_version;
	}

	/**
	 * Get plugin slug.
	 *
	 * @return string
	 */
	public function slug() {
		$parts = explode( '/', $this->basename() );

		return (string) array_shift( $parts );
	}

	/**
	 * Get latest WordPress version this plugin has been tested with.
	 *
	 * @return string
	 */
	public function tested_with_wp_version() {
		$wp_version = '';
		if ( $this->is_wp_org() ) {
			$wp_version = (string) $this->get_wp_org_data( 'tested' );
		}

		return $wp_version;
	}

	/**
	 * Get plugin text domain.
	 *
	 * @return string
	 */
	public function text_domain() {
		return $this->get_plugin_header( 'TextDomain' );
	}

	/**
	 * Get plugin relative directory path to translation files.
	 *
	 * @return string
	 */
	public function text_domain_path() {
		return $this->get_plugin_header( 'DomainPath' );
	}

	/**
	 * Get plugin URL.
	 *
	 * @return string
	 */
	public function url() {
		return $this->get_plugin_header( 'PluginURI' );
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function version() {
		return $this->get_plugin_header( 'Version' );
	}

	/**
	 * Fetch a plugin header value.
	 *
	 * @param string $header Plugin header name.
	 *
	 * @return string
	 */
	protected function get_plugin_header( $header = '' ) {
		if ( ! $this->headers ) {

			if ( ! function_exists( 'get_plugin_data' ) ) {
				require ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$this->headers = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->basename(), false, false );

		}

		return isset( $this->headers[ $header ] ) ? $this->headers[ $header ] : '';
	}

	/**
	 * Get WP.org plugin data.
	 *
	 * @param string $key     WP.org data array key.
	 * @param string $default Default return value if key doesn't exist.
	 *
	 * @return mixed|string
	 */
	public function get_wp_org_data( $key = '', $default = '' ) {

		if ( ! $this->wp_org ) {

			if ( ! function_exists( 'plugins_api' ) ) {
				require ABSPATH . 'wp-admin/includes/plugin-install.php';
			}

			$response = plugins_api( 'plugin_information', array( 'slug' => $this->slug() ) );

			$this->wp_org           = is_wp_error( $response ) ? array() : (array) $response;
			$this->wp_org['exists'] = ! is_wp_error( $response );

		}

		return isset( $this->wp_org[ $key ] ) ? $this->wp_org[ $key ] : $default;
	}

	/**
	 * Get plugin update data.
	 *
	 * @param string $key     The data key.
	 * @param mixed  $default Default return value if key doesn't exist.
	 *
	 * @return mixed
	 */
	public function get_plugin_update_data( $key, $default = null ) {

		$updates = get_site_transient( 'update_plugins' );
		$data    = new stdClass();
		if ( $updates && isset( $updates->response, $updates->response[ $this->basename() ] ) ) {
			$data = $updates->response[ $this->basename() ];
		}

		return isset( $data->{$key} ) ? $data->{$key} : $default;
	}

}
