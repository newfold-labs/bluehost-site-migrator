<?php

namespace BluehostSiteMigrator\Manifest;

use BluehostSiteMigrator\Data\Theme;

/**
 * The themes manifest data
 */
class Themes extends Registry {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$themes = array_keys( wp_get_themes() );
		foreach ( $themes as $theme ) {
			$this->set( $theme, $this->get_theme_data( $theme ) );
		}
	}

	/**
	 * Get data for a specific theme.
	 *
	 * @param string $directory_name Theme directory name.
	 *
	 * @return array
	 */
	protected function get_theme_data( $directory_name ) {
		$theme = new Theme( $directory_name );

		$last_updated = $theme->last_updated();

		return array(
			'author'      => $theme->author(),
			'authorUrl'   => $theme->author_url(),
			'description' => $theme->description(),
			'isActive'    => $theme->is_active(),
			'isWpOrg'     => $theme->is_wp_org(),
			'lastUpdated' => $last_updated ? $last_updated->format( 'c' ) : '',
			'name'        => $theme->name(),
			'newVersion'  => $theme->new_version(),
			'path'        => $theme->path(),
			'requiresPhp' => $theme->requires_php_version(),
			'requiresWp'  => $theme->requires_wp_version(),
			'stylesheet'  => $theme->stylesheet(),
			'template'    => $theme->template(),
			'textDomain'  => $theme->text_domain(),
			'url'         => $theme->url(),
			'version'     => $theme->version(),
		);
	}
}
