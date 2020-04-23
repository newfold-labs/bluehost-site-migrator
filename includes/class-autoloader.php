<?php

/**
 * Class ClassLoader
 */
class BH_Site_Migrator_Class_Loader {

	/**
	 * Class map.
	 *
	 * @var array
	 */
	protected $class_map = array();

	/**
	 * Setup class properties.
	 *
	 * @param array $class_map Map of class names to file paths.
	 */
	public function __construct( array $class_map ) {
		$this->class_map = $class_map;
	}

	/**
	 * Register autoloader
	 *
	 * @param bool $throw   Whether or not to throw an exception if the autoloader cannot be registered.
	 * @param bool $prepend Whether or not to prepend the autoloader to the top of the stack.
	 *
	 * @return bool
	 */
	public function register( $throw = true, $prepend = false ) {
		return spl_autoload_register( array( $this, 'load_class' ), $throw, $prepend );
	}

	/**
	 * Unregister autoloader
	 *
	 * @return bool
	 */
	public function unregister() {
		return spl_autoload_unregister( array( $this, 'load_class' ) );
	}

	/**
	 * Load class
	 *
	 * @param string $class Class name.
	 *
	 * @return bool
	 */
	public function load_class( $class ) {
		$file = null;

		if ( isset( $this->class_map[ $class ] ) ) {
			$file = $this->class_map[ $class ];
		}

		if ( $file && is_readable( $file ) ) {
			require $file;

			return true;
		}

		return false;
	}

	/**
	 * Register a new class map
	 *
	 * @param array $class_map Map of class names to file paths.
	 *
	 * @return bool
	 */
	public static function register_class_map( array $class_map ) {
		$loader = new self( $class_map );

		return $loader->register();
	}

}
