<?php

/**
 * Class BH_Site_Migrator_Options
 */
class BH_Site_Migrator_Options {

	/**
	 * Plugin option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'bluehost_site_migrator';

	/**
	 * Whether or not changes were made to the options.
	 *
	 * @var bool
	 */
	protected static $has_updates = false;

	/**
	 * Local copy of options.
	 *
	 * @var array
	 */
	protected static $options = array();

	/**
	 * Check if option name exists.
	 *
	 * @param string $name The option name.
	 *
	 * @return bool
	 */
	public static function has( $name ) {
		return isset( self::$options[ $name ] );
	}

	/**
	 * Get value of option. Optionally provide a default value.
	 *
	 * @param string $name    The option name.
	 * @param mixed  $default The default value if option isn't found. Defaults to null.
	 *
	 * @return mixed
	 */
	public static function get( $name, $default = null ) {
		$value = $default;
		if ( self::has( $name ) ) {
			$value = self::$options[ $name ];
		}

		return $value;
	}

	/**
	 * Set an option.
	 *
	 * @param string $name  The option name.
	 * @param mixed  $value The option value.
	 */
	public static function set( $name, $value ) {
		self::$options[ $name ] = $value;
		self::$has_updates      = true;
	}

	/**
	 * Delete an option.
	 *
	 * @param string $name The option name.
	 */
	public static function delete( $name ) {
		unset( self::$options[ $name ] );
		self::$has_updates = true;
	}

	/**
	 * Fetch the options from the database and store locally.
	 */
	public static function fetch() {
		self::$options = get_option( self::OPTION_NAME, array() );
	}

	/**
	 * Save the local options to the database.
	 */
	public static function persist() {
		update_option( self::OPTION_NAME, self::$options, true );
	}

	/**
	 * Only update data if changes were made.
	 */
	public static function maybe_persist() {
		if ( self::$has_updates ) {
			self::persist();
		}
	}

	/**
	 * Nuke all options in the database.
	 */
	public static function purge() {
		delete_option( self::OPTION_NAME );
	}

	/**
	 * Fetch current state of all local options.
	 *
	 * @return array
	 */
	public static function all() {
		return self::$options;
	}

}
