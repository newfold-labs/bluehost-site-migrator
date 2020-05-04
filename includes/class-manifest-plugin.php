<?php

/**
 * Class BH_Site_Migrator_Plugin_Manifest
 */
class BH_Site_Migrator_Plugin_Manifest extends BH_Site_Migrator_Registry {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$plugins = self::get_plugins();
		foreach ( $plugins as $plugin ) {
			$this->set( $plugin, $this->get_plugin_data( $plugin ) );
		}
	}

	/**
	 * Get a list of plugin relative file paths.
	 *
	 * @return array
	 */
	public static function get_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();

		return array_keys( $plugins );
	}

	/**
	 * Get data for a specific plugin.
	 *
	 * @param string $basename Plugin basename.
	 *
	 * @return array
	 */
	protected function get_plugin_data( $basename ) {
		$plugin = new BH_Site_Migrator_Plugin_Data( $basename );

		$last_modified = $plugin->last_modified();
		$last_updated  = $plugin->last_updated();

		return array(
			'author'            => $plugin->author(),
			'authorUrl'         => $plugin->author_url(),
			'basename'          => $plugin->basename(),
			'description'       => $plugin->description(),
			'isActive'          => $plugin->is_active(),
			'isWpOrg'           => $plugin->is_wp_org(),
			'lastModified'      => $last_modified ? $last_modified->format( 'c' ) : '',
			'lastUpdated'       => $last_updated ? $last_updated->format( 'c' ) : '',
			'name'              => $plugin->name(),
			'newVersion'        => $plugin->new_version(),
			'requiresPhp'       => $plugin->requires_php_version(),
			'requiresWp'        => $plugin->requires_wp_version(),
			'slug'              => $plugin->slug(),
			'testedToWpVersion' => $plugin->tested_with_wp_version(),
			'textDomain'        => $plugin->text_domain(),
			'url'               => $plugin->url(),
			'version'           => $plugin->version(),
		);
	}

}
