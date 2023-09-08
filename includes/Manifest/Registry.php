<?php

namespace BluehostSiteMigrator\Manifest;

/**
 * Holds on to the manifest data
 */
class Registry {
	/**
	 * Registry data.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Check if the registry contains a property.
	 *
	 * @param string $key The property name.
	 *
	 * @return bool
	 */
	public function has( $key ) {
		return isset( $this->data[ $key ] );
	}

	/**
	 * Get a property from the registry.
	 *
	 * @param string $key           The property name.
	 * @param mixed  $default_value The fallback value, if property doesn't exist.
	 *
	 * @return mixed
	 */
	public function get( $key, $default_value = null ) {
		$value = $default_value;
		if ( $this->has( $key ) ) {
			$value = $this->data[ $key ];
		}

		return $value;
	}

	/**
	 * Set a property in the registry.
	 *
	 * @param string $key   The property name.
	 * @param mixed  $value The property value.
	 */
	public function set( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * Remove a property from the registry.
	 *
	 * @param string $key The property name.
	 */
	public function remove( $key ) {
		if ( $this->has( $key ) ) {
			unset( $this->data[ $key ] );
		}
	}

	/**
	 * Get the registry data as an array.
	 *
	 * @return array
	 */
	public function to_array() {
		return $this->data;
	}

	/**
	 * Get the registry data as JSON.
	 *
	 * @return string
	 */
	public function to_json() {
		return wp_json_encode( $this->data, JSON_PRETTY_PRINT );
	}
}
