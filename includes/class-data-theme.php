<?php

/**
 * Class BH_Site_Migrator_Theme_Data
 */
class BH_Site_Migrator_Theme_Data {

	/**
	 * Theme directory name.
	 *
	 * @var string
	 */
	protected $directory_name;

	/**
	 * WordPress theme object.
	 *
	 * @var WP_Theme
	 */
	protected $theme;

	/**
	 * WP.org plugin data.
	 *
	 * @var array
	 */
	protected $wp_org;

	/**
	 * Constructor.
	 *
	 * @param string $directory_name Theme directory name.
	 */
	public function __construct( $directory_name ) {
		$this->directory_name = $directory_name;
		$this->theme          = wp_get_theme( $directory_name );
	}

	/**
	 * Get theme author name.
	 *
	 * @return string
	 */
	public function author() {
		return $this->theme->get( 'Author' );
	}

	/**
	 * Get theme author URL.
	 *
	 * @return string
	 */
	public function author_url() {
		return $this->theme->get( 'AuthorURI' );
	}

	/**
	 * Get theme description.
	 *
	 * @return string
	 */
	public function description() {
		return $this->theme->get( 'Description' );
	}

	/**
	 * Get the number of downloads.
	 *
	 * @return int|null Number of downloads or null if there is no data available.
	 */
	public function downloads() {
		$downloads = $this->get_wp_org_data( 'downloaded', null );

		return is_null( $downloads ) ? null : absint( $downloads );
	}

	/**
	 * Check if theme is active.
	 *
	 * @return bool
	 */
	public function is_active() {
		return get_template() === $this->stylesheet();
	}

	/**
	 * Check if theme is on WP.org.
	 *
	 * @return bool
	 */
	public function is_wp_org() {
		return wp_validate_boolean( $this->get_wp_org_data( 'exists', false ) );
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
	 * Get theme name.
	 *
	 * @return string
	 */
	public function name() {
		return $this->theme->get( 'Name' );
	}

	/**
	 * Get the new theme version that is available for upgrade.
	 *
	 * @return string
	 */
	public function new_version() {
		return (string) $this->get_theme_update_data( 'new_version', '' );
	}

	/**
	 * Get the theme path.
	 *
	 * @return string
	 */
	public function path() {
		return $this->theme->get_theme_root() . DIRECTORY_SEPARATOR . $this->stylesheet();
	}

	/**
	 * Get required PHP version.
	 *
	 * @return string
	 */
	public function requires_php_version() {
		$php_version = '';

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
		$wp_version = '';

		if ( empty( $wp_version ) && $this->is_wp_org() ) {
			$wp_version = (string) $this->get_wp_org_data( 'requires' );
		}

		return $wp_version;
	}

	/**
	 * Get theme stylesheet.
	 *
	 * @return string
	 */
	public function stylesheet() {
		return $this->theme->get_stylesheet();
	}

	/**
	 * Get theme template.
	 *
	 * @return string
	 */
	public function template() {
		return $this->theme->get_template();
	}

	/**
	 * Get theme text domain.
	 *
	 * @return string
	 */
	public function text_domain() {
		return $this->theme->get( 'TextDomain' );
	}

	/**
	 * Get theme relative directory path to translation files.
	 *
	 * @return string
	 */
	public function text_domain_path() {
		return $this->theme->get( 'DomainPath' );
	}

	/**
	 * Get theme URL.
	 *
	 * @return string
	 */
	public function url() {
		return $this->theme->get( 'ThemeURI' );
	}

	/**
	 * Get theme version.
	 *
	 * @return string
	 */
	public function version() {
		return $this->theme->get( 'Version' );
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

			if ( ! function_exists( 'themes_api' ) ) {
				require ABSPATH . 'wp-admin/includes/theme.php';
			}

			$response = themes_api( 'theme_information', array( 'slug' => $this->stylesheet() ) );

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
	public function get_theme_update_data( $key, $default = null ) {
		$updates = get_site_transient( 'update_themes' );
		$data    = new stdClass();
		if ( $updates && isset( $updates->response, $updates->response[ $this->stylesheet() ] ) ) {
			$data = (object) $updates->response[ $this->stylesheet() ];
		}

		return isset( $data->{$key} ) ? $data->{$key} : $default;
	}

}
