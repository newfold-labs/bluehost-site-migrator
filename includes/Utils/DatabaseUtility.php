<?php

namespace BluehostSiteMigrator\Utils;

class DatabaseUtility {

	/**
	 * Replace all occurrences of the search string with the replacement string.
	 * This function is case-sensitive.
	 *
	 * @param  array  $from List of string we're looking to replace.
	 * @param  array  $to   What we want it to be replaced with.
	 * @param  string $data Data to replace.
	 * @return mixed        The original string with all elements replaced as needed.
	 */
	public static function replace_values( $from = array(), $to = array(), $data = '' ) {
		if ( ! empty( $from ) && ! empty( $to ) ) {
			return strtr( $data, array_combine( $from, $to ) );
		}

		return $data;
	}

	/**
	 * Take a serialized array and unserialize it replacing elements as needed and
	 * unserializing any subordinate arrays and performing the replace on those too.
	 * This function is case-sensitive.
	 *
	 * @param  array $from       List of string we're looking to replace.
	 * @param  array $to         What we want it to be replaced with.
	 * @param  mixed $data       Used to pass any subordinate arrays back to in.
	 * @param  bool  $serialized Does the array passed via $data need serializing.
	 * @return mixed             The original array with all elements replaced as needed.
	 */
	public static function replace_serialized_values( $from = array(), $to = array(), $data = '', $serialized = false ) {
		try {

			// Some unserialized data cannot be re-serialized eg. SimpleXMLElements
			if ( is_serialized( $data ) && ( $unserialized = @unserialize( $data ) ) !== false ) {
				$data = self::replace_serialized_values( $from, $to, $unserialized, true );
			} elseif ( is_array( $data ) ) {
				$tmp = array();
				foreach ( $data as $key => $value ) {
					$tmp[ $key ] = self::replace_serialized_values( $from, $to, $value, false );
				}

				$data = $tmp;
				unset( $tmp );
			} elseif ( is_object( $data ) ) {
				if ( ! ( $data instanceof \__PHP_Incomplete_Class ) ) {
					$tmp   = $data;
					$props = get_object_vars( $data );
					foreach ( $props as $key => $value ) {
						if ( ! empty( $tmp->$key ) ) {
							$tmp->$key = self::replace_serialized_values( $from, $to, $value, false );
						}
					}

					$data = $tmp;
					unset( $tmp );
				}
			} else {
				if ( is_string( $data ) ) {
					if ( ! empty( $from ) && ! empty( $to ) ) {
						$data = strtr( $data, array_combine( $from, $to ) );
					}
				}
			}

			if ( $serialized ) {
				return serialize( $data );
			}
		} catch ( \Exception $e ) {
		}

		return $data;
	}

	/**
	 * Escape MySQL special characters
	 *
	 * @param  string $data Data to escape
	 * @return string
	 */
	public static function escape_mysql( $data ) {
		return strtr(
			$data,
			array_combine(
				array( "\x00", "\n", "\r", '\\', "'", '"', "\x1a" ),
				array( '\\0', '\\n', '\\r', '\\\\', "\\'", '\\"', '\\Z' )
			)
		);
	}

	/**
	 * Unescape MySQL special characters
	 *
	 * @param  string $data Data to unescape
	 * @return string
	 */
	public static function unescape_mysql( $data ) {
		return strtr(
			$data,
			array_combine(
				array( '\\0', '\\n', '\\r', '\\\\', "\\'", '\\"', '\\Z' ),
				array( "\x00", "\n", "\r", '\\', "'", '"', "\x1a" )
			)
		);
	}

	/**
	 * Encode base64 characters
	 *
	 * @param  string $data Data to encode
	 * @return string
	 */
	public static function base64_encode( $data ) {
		return base64_encode( $data );
	}

	/**
	 * Encode base64 characters
	 *
	 * @param  string $data Data to decode
	 * @return string
	 */
	public static function base64_decode( $data ) {
		return base64_decode( $data );
	}

	/**
	 * Validate base64 data
	 *
	 * @param  string $data Data to validate
	 * @return boolean
	 */
	public static function base64_validate( $data ) {
		return base64_encode( base64_decode( $data ) ) === $data;
	}
}
