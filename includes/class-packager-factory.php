<?php

/**
 * Class BH_Move_Packager_Factory
 */
class BH_Move_Packager_Factory {

	/**
	 * Mapping of package types to packager classes.
	 *
	 * @var array
	 */
	protected static $class_map = array(
		'database'   => 'BH_Move_Database_Packager',
		'dropins'    => 'BH_Move_Dropins_Packager',
		'mu-plugins' => 'BH_Move_MU_Plugins_Packager',
		'plugins'    => 'BH_Move_Plugins_Packager',
		'themes'     => 'BH_Move_Themes_Packager',
		'uploads'    => 'BH_Move_Uploads_Packager',
	);

	/**
	 * Create a new packager instance.
	 *
	 * @param string $package_type One of the keys listed in the class map above.
	 *
	 * @return BH_Move_Packager|null
	 */
	public static function create( $package_type ) {

		if ( ! self::is_valid_package_type( $package_type ) ) {
			return null;
		}

		// Get package class
		$class = self::$class_map[ $package_type ];

		// Create instance
		$instance = new $class();

		// Return class instance
		return $instance;

	}

	/**
	 * Get a list of available package types.
	 *
	 * @return array
	 */
	public static function get_package_types() {
		return array_keys( self::$class_map );
	}

	/**
	 * Check if package type is valid.
	 *
	 * @param string $package_type Type of migration package (e.g. plugins, themes, uploads, etc.).
	 *
	 * @return bool
	 */
	public static function is_valid_package_type( $package_type ) {
		return array_key_exists( $package_type, self::$class_map );
	}

}
