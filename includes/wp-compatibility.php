<?php

if ( ! function_exists( 'wp_timezone_string' ) ) {
	/**
	 * Retrieves the timezone from site settings as a string.
	 *
	 * Uses the `timezone_string` option to get a proper timezone if available,
	 * otherwise falls back to an offset.
	 *
	 * @return string PHP timezone string or a ±HH:MM offset.
	 * @since 5.3.0
	 */
	function wp_timezone_string() {
		$timezone_string = get_option( 'timezone_string' );

		if ( $timezone_string ) {
			return $timezone_string;
		}

		$offset  = (float) get_option( 'gmt_offset' );
		$hours   = (int) $offset;
		$minutes = ( $offset - $hours );

		$sign      = ( $offset < 0 ) ? '-' : '+';
		$abs_hour  = abs( $hours );
		$abs_mins  = abs( $minutes * 60 );
		$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

		return $tz_offset;
	}
}

if ( ! function_exists( 'recurse_dirsize' ) ) {
	/**
	 * Get the size of a directory recursively.
	 *
	 * Used by get_dirsize() to get a directory's size when it contains
	 * other directories.
	 *
	 * @param string       $directory          Full path of a directory.
	 * @param string|array $exclude            Optional. Full path of a subdirectory to exclude from the total, or array of paths.
	 *                                         Expected without trailing slash(es).
	 * @param int          $max_execution_time Maximum time to run before giving up. In seconds.
	 *                                         The timeout is global and is measured from the moment WordPress started to load.
	 *
	 * @return int|false|null Size in bytes if a valid directory. False if not. Null if timeout.
	 * @since 4.3.0 $exclude parameter added.
	 * @since 5.2.0 $max_execution_time parameter added.
	 *
	 * @since MU (3.0.0)
	 */
	function recurse_dirsize( $directory, $exclude = null, $max_execution_time = null ) {
		$size = 0;

		$directory = untrailingslashit( $directory );

		if ( ! file_exists( $directory ) || ! is_dir( $directory ) || ! is_readable( $directory ) ) {
			return false;
		}

		if (
			( is_string( $exclude ) && $directory === $exclude ) ||
			( is_array( $exclude ) && in_array( $directory, $exclude, true ) )
		) {
			return false;
		}

		if ( null === $max_execution_time ) {
			// Keep the previous behavior but attempt to prevent fatal errors from timeout if possible.
			if ( function_exists( 'ini_get' ) ) {
				$max_execution_time = ini_get( 'max_execution_time' );
			} else {
				// Disable...
				$max_execution_time = 0;
			}

			// Leave 1 second "buffer" for other operations if $max_execution_time has reasonable value.
			if ( $max_execution_time > 10 ) {
				-- $max_execution_time;
			}
		}

		$handle = opendir( $directory );
		if ( $handle ) {
			while ( ( $file = readdir( $handle ) ) !== false ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
				$path = $directory . '/' . $file;
				if ( '.' !== $file && '..' !== $file ) {
					if ( is_file( $path ) ) {
						$size += filesize( $path );
					} elseif ( is_dir( $path ) ) {
						$handlesize = recurse_dirsize( $path, $exclude, $max_execution_time );
						if ( $handlesize > 0 ) {
							$size += $handlesize;
						}
					}

					if ( $max_execution_time > 0 && microtime( true ) - WP_START_TIMESTAMP > $max_execution_time ) {
						// Time exceeded. Give up instead of risking a fatal timeout.
						$size = null;
						break;
					}
				}
			}
			closedir( $handle );
		}

		return $size;
	}
}
