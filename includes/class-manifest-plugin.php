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
			'author'               => $plugin->author(),
			'author_url'           => $plugin->author_url(),
			'basename'             => $plugin->basename(),
			'description'          => $plugin->description(),
			'is_active'            => $plugin->is_active(),
			'is_wp_org'            => $plugin->is_wp_org(),
			'last_modified'        => $last_modified ? $last_modified->format( 'c' ) : '',
			'last_updated'         => $last_updated ? $last_updated->format( 'c' ) : '',
			'name'                 => $plugin->name(),
			'new_version'          => $plugin->new_version(),
			'requires_php'         => $plugin->requires_php_version(),
			'requires_wp'          => $plugin->requires_wp_version(),
			'slug'                 => $plugin->slug(),
			'tested_to_wp_version' => $plugin->tested_with_wp_version(),
			'text_domain'          => $plugin->text_domain(),
			'url'                  => $plugin->url(),
			'version'              => $plugin->version(),
		);
	}

}
