<?php

/**
 * Class BH_Move_Theme_Manifest
 */
class BH_Move_Theme_Manifest extends BH_Move_Registry {

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
		$theme = new BH_Move_Theme_Data( $directory_name );

		$last_updated = $theme->last_updated();

		return array(
			'author'       => $theme->author(),
			'author_url'   => $theme->author_url(),
			'description'  => $theme->description(),
			'is_active'    => $theme->is_active(),
			'is_wp_org'    => $theme->is_wp_org(),
			'last_updated' => $last_updated ? $last_updated->format( 'c' ) : '',
			'name'         => $theme->name(),
			'new_version'  => $theme->new_version(),
			'path'         => $theme->path(),
			'requires_php' => $theme->requires_php_version(),
			'requires_wp'  => $theme->requires_wp_version(),
			'stylesheet'   => $theme->stylesheet(),
			'template'     => $theme->template(),
			'text_domain'  => $theme->text_domain(),
			'url'          => $theme->url(),
			'version'      => $theme->version(),
		);
	}

}
