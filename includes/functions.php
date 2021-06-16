<?php

/**
 * Get a directory size.
 *
 * @param string $path The directory path.
 *
 * @return int
 */
function bh_site_migrator_get_dir_size( $path ) {
	set_time_limit( 90 );
	$bytes = 0;
	$path  = realpath( $path );
	if ( $path && file_exists( $path ) ) {
		foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ) ) as $object ) {
			$bytes += $object->getSize();
		}
	}

	return $bytes;
}

/**
 * Filter files used in the filter iterator.
 *
 * @param bool        $accept Whether or not to accept the file.
 * @param SplFileInfo $file   The file information.
 *
 * @return bool
 */
function bh_site_migrator_filter_files( $accept, SplFileInfo $file ) {

	// Filter by extension
	$exclude_extensions = apply_filters( 'bh_site_migrator_filter_by_extension', array() );

	if ( in_array( $file->getExtension(), $exclude_extensions, true ) ) {
		return false;
	}

	// Filter by name
	$exclude_names = apply_filters( 'bh_site_migrator_filter_by_name', array() );

	if ( in_array( $file->getFilename(), $exclude_names, true ) ) {
		return false;
	}

	// Filter by path
	$exclude_paths = apply_filters( 'bh_site_migrator_filter_by_path', array() );

	foreach ( $exclude_paths as $path ) {
		if ( 0 === strpos( $file->getRealPath(), $path ) ) {
			return false;
		}
	}

	return $accept;
}

/**
 * Filter to exclude files with specific extensions from generated packages.
 *
 * @param array $extensions A collection of extensions to ignore.
 *
 * @return array
 */
function bh_site_migrator_filter_by_extension( array $extensions ) {
	array_push( $extensions, 'bak', 'exe', 'gz', 'log', 'sql', 'tar' );

	return $extensions;
}

/**
 * Filter to exclude files with specific file/directory names from generated packages.
 *
 * @param array $names A collection of file/directory names to ignore.
 *
 * @return array
 */
function bh_site_migrator_filter_by_name( array $names ) {
	array_push( $names, '.git', '.gitignore', '.idea', '.svn', '.vscode', 'node_modules' );
	$names[] = str_replace( '.php', '', basename( plugin_basename( BH_SITE_MIGRATOR_FILE ) ) );

	return $names;
}

/**
 * Filter to exclude files with specific paths from generated packages.
 *
 * @param array $paths A collection of file paths to ignore.
 *
 * @return array
 */
function bh_site_migrator_filter_by_path( array $paths ) {

	foreach ( $paths as $index => $path ) {
		if ( 0 === strpos( $path, '{' ) ) {
			$paths[ $index ] = bh_site_migrator_set_path_context( $path );
		}
	}

	return $paths;
}

/**
 * Filter WordPress root files.
 *
 * @param array $paths A collection of WordPress core root files to ignore.
 *
 * @return array
 */
function bh_site_migrator_filter_wp_root_files( array $paths ) {

	array_push(
		$paths,
		ABSPATH . 'index.php',
		ABSPATH . 'license.txt',
		ABSPATH . 'readme.html',
		ABSPATH . 'wp-activate.php',
		ABSPATH . 'wp-blog-header.php',
		ABSPATH . 'wp-comments-post.php',
		ABSPATH . 'wp-config-sample.php',
		ABSPATH . 'wp-config.php',
		ABSPATH . 'wp-cron.php',
		ABSPATH . 'wp-links-opml.php',
		ABSPATH . 'wp-load.php',
		ABSPATH . 'wp-login.php',
		ABSPATH . 'wp-mail.php',
		ABSPATH . 'wp-settings.php',
		ABSPATH . 'wp-signup.php',
		ABSPATH . 'wp-trackback.php',
		ABSPATH . 'xmlrpc.php'
	);

	return $paths;
}

/**
 * Filter directories.
 *
 * @param bool        $accept Whether or not to accept the file.
 * @param SplFileInfo $file   The file information.
 *
 * @return bool
 */
function bh_site_migrator_filter_directories( $accept, SplFileInfo $file ) {
	return $accept && ! $file->isDir();
}

/**
 * Dynamically replace placeholders.
 *
 * @param string $path The path in which to replace placeholders.
 *
 * @return string|string[]
 */
function bh_site_migrator_set_path_context( $path ) {

	$uploads = wp_upload_dir( null, false );

	$contexts = array(
		'ABSPATH'         => untrailingslashit( ABSPATH ),
		'WP_CONTENT_DIR'  => WP_CONTENT_DIR,
		'WPMU_PLUGIN_DIR' => WPMU_PLUGIN_DIR,
		'WP_PLUGIN_DIR'   => WP_PLUGIN_DIR,
		'WP_THEME_DIR'    => get_theme_root(),
		'WP_UPLOAD_DIR'   => $uploads['basedir'],
		'WP_LANG_DIR'     => WP_LANG_DIR,
	);

	foreach ( $contexts as $needle => $contextual_path ) {
		$placeholder = '{' . $needle . '}';
		if ( 0 === strpos( $path, $placeholder ) ) {
			$relative_path = ltrim( str_replace( $placeholder, '', $path ), DIRECTORY_SEPARATOR );
			$path          = trailingslashit( $contextual_path ) . $relative_path;
			break;
		}
	}

	return $path;
}

/**
 * Load plugin text domain.
 */
function bh_site_migrator_load_plugin_textdomain() {
	$plugin_dir = basename( dirname( BH_SITE_MIGRATOR_FILE ) );
	load_plugin_textdomain( $plugin_dir, false, $plugin_dir . '/languages/' );
}

/**
 * Get geolocation data from Cloudflare.
 *
 * Sample response:
 * {
 *   "message": "success",
 *   "ip": "107.129.251.90",
 *   "country": {
 *     "code": "US",
 *     "name": "United States"
 *   },
 *   "airportCode": "ATL",
 *   "continent": "NA",
 *   "city": "Snellville",
 *   "lat": "33.85740",
 *   "lng": "-84.01300",
 *   "postalCode": "30078",
 *   "region": "Georgia",
 *   "regionCode": "GA",
 *   "timezone": "America/New_York"
 * }
 *
 * @return array
 */
function bh_site_migrator_get_geo_data() {
	$geo     = array();
	$request = wp_remote_get( 'https://geolocation.wpscholar.workers.dev' );
	if ( wp_remote_retrieve_response_code( $request ) === 200 ) {
		$body = wp_remote_retrieve_body( $request );
		$data = json_decode( $body, true );
		if ( $data && is_array( $data ) ) {
			$geo = $data;
		}
	}

	return $geo;
}

/**
 * Get a value from an object or an array.  Allows the ability to fetch a nested value from a
 * heterogeneous multidimensional collection using dot notation.
 *
 * @param array|object $data    Data to fetch from.
 * @param string       $key     Key as a string, optionally using dot notation.
 * @param mixed        $default The fallback value if a value doesn't exist.
 *
 * @return mixed
 */
function bh_site_migrator_data_get( $data, $key, $default = null ) {
	$value = $default;
	if ( is_array( $data ) && array_key_exists( $key, $data ) ) {
		$value = $data[ $key ];
	} elseif ( is_object( $data ) && property_exists( $data, $key ) ) {
		$value = $data->$key;
	} else {
		$segments = explode( '.', $key );
		foreach ( $segments as $segment ) {
			if ( is_array( $data ) && array_key_exists( $segment, $data ) ) {
				$value = $data = $data[ $segment ]; // phpcs:ignore
			} elseif ( is_object( $data ) && property_exists( $data, $segment ) ) {
				$value = $data = $data->$segment; // phpcs:ignore
			} else {
				$value = $default;
				break;
			}
		}
	}

	return $value;
}
