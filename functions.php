<?php

/**
 * Replace forward slash with current directory separator
 *
 * @param  string $path Path
 * @return string
 */
function nfd_bhsm_replace_forward_slash_with_directory_separator( $path ) {
	return str_replace( '/', DIRECTORY_SEPARATOR, $path );
}

/**
 * Escape Windows directory separator
 *
 * @param  string $path Path
 * @return string
 */
function nfd_bhsm_escape_windows_directory_separator( $path ) {
	return preg_replace( '/[\\\\]+/', '\\\\\\\\', $path );
}

/**
 * Returns encrypt/decrypt iv length
 *
 * @return int
 * @throws \Exception When we are not able to obtain the cipher length.
 */
function nfd_bhsm_crypt_iv_length() {
	$iv_length = openssl_cipher_iv_length( BH_SITE_MIGRATOR_CIPHER_NAME );
	if ( false === $iv_length ) {
		throw new \Exception( 'Unable to obtain cipher length.' );
	}

	return $iv_length;
}

/**
 * Encrypts a string with a key
 *
 * @param string $string String to encrypt
 * @param string $key    Key to encrypt the string with
 * @return string
 * @throws \Exception When we are unable to encrypt the data.
 */
function nfd_bhsm_encrypt_string( $string, $key ) {
	$iv_length = nfd_bhsm_crypt_iv_length();
	$key       = substr( sha1( $key, true ), 0, $iv_length );

	$iv = openssl_random_pseudo_bytes( $iv_length );
	if ( false === $iv ) {
		throw new \Exception( 'Unable to generate random bytes.' );
	}

	$encrypted_string = openssl_encrypt( $string, BH_SITE_MIGRATOR_CIPHER_NAME, $key, OPENSSL_RAW_DATA, $iv );
	if ( false === $encrypted_string ) {
		throw new \Exception( 'Unable to encrypt data.' );
	}

	return sprintf( '%s%s', $iv, $encrypted_string );
}

/**
 * Replace current directory separator with forward slash
 *
 * @param  string $path Path
 * @return string
 */
function nfd_bhsm_replace_directory_separator_with_forward_slash( $path ) {
	return str_replace( DIRECTORY_SEPARATOR, '/', $path );
}


/**
 * Function to open a file and throw an error when not possible
 *
 * @param string $file Path to the file to open
 * @param string $mode Mode in which to open the file
 *
 * @return resource
 *
 * @throws \Exception When we are unable to open the file.
 */
function nfd_bhsm_open( $file, $mode ) {
	$file_handle = fopen( $file, $mode );
	if ( false === $file_handle ) {
		throw new \Exception(
			sprintf( 'Unable to open %s with mode %s.', $file, $mode )
		);
	}

	return $file_handle;
}


/**
 * Write contents to a file
 *
 * @param resource $handle  File handle to write to
 * @param string   $content Contents to write to the file
 *
 * @return integer
 *
 * @throws \Exception If unable to write.
 */
function nfd_bhsm_write( $handle, $content ) {
	$write_result = fwrite( $handle, $content );
	if ( false === $write_result ) {
		$meta = stream_get_meta_data( $handle );
		if ( $meta ) {
			throw new \Exception( sprintf( 'Unable to write to: %s.', $meta['uri'] ) );
		}
	} elseif ( null === $write_result ) {
		return strlen( $content );
	} elseif ( strlen( $content ) !== $write_result ) {
		$meta = stream_get_meta_data( $handle );
		if ( $meta ) {
			throw new \Exception( sprintf( 'Out of disk space. Unable to write to: %s', $meta['uri'] ) );
		}
	}

	return $write_result;
}

/**
 * Check whether blog ID is main site
 *
 * @param  integer $blog_id Blog ID
 * @return boolean
 */
function nfd_bhsm_is_mainsite( $blog_id = null ) {
	return null === $blog_id || 0 === $blog_id || 1 === $blog_id;
}

/**
 * Get WordPress table prefix by blog ID
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function nfd_bhsm_table_prefix( $blog_id = null ) {
	global $wpdb;

	// Set base table prefix
	if ( nfd_bhsm_is_mainsite( $blog_id ) ) {
		return $wpdb->base_prefix;
	}

	return $wpdb->base_prefix . $blog_id . '_';
}

/**
 * Get the base storage directory
 */
function nfd_bhsm_storage_path() {
	$uploads   = wp_get_upload_dir();
	$directory = $uploads['basedir'] . DIRECTORY_SEPARATOR . 'bluehost-site-migrator' . DIRECTORY_SEPARATOR;
	wp_mkdir_p( $directory );

	return $directory;
}

/**
 * Get hashed file name
 *
 * @param string $name The file name.
 * @param string $type The file type.
 * @param string $ext  The file extensions.
 */
function nfd_bhsm_get_hashed_file_name( $name, $type, $ext ) {
	$base      = nfd_bhsm_storage_path();
	$date      = gmdate( 'Y-m-d-His' );
	$site_name = strtolower( preg_replace( '#[^a-zA-Z0-9]#', '-', get_bloginfo( 'name' ) ) );
	$unique_id = uniqid();

	return $base . "{$type}-{$date}-{$site_name}-{$unique_id}-{$name}.{$ext}";
}

/**
 * Get the full path to a file using filename
 *
 * @param string $name The file name.
 * @param string $type The file type.
 * @param string $ext  The file extensions.
 */
function nfd_bhsm_get_hashed_file_path( $name, $type, $ext ) {
	$filename  = nfd_bhsm_get_hashed_file_name( $name, $type, $ext );
	$directory = nfd_bhsm_storage_path();
	if ( ! file_exists( $directory . '/index.php' ) ) {
		file_put_contents( $directory . '/index.php', '<?php // Silence is golden.' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
	}

	return $directory . ltrim( $filename, DIRECTORY_SEPARATOR );
}

/**
 * Write fields to a file
 *
 * @param  resource $handle File handle to write to
 * @param  array    $fields Fields to write to the file
 * @return integer
 * @throws \Exception When we are unable to write.
 */
function nfd_bhsm_putcsv( $handle, $fields ) {
	$write_result = fputcsv( $handle, $fields );
	if ( false === $write_result ) {
		$meta = stream_get_meta_data( $handle );
		if ( $meta ) {
			throw new \Exception( sprintf( 'Unable to write to: %s. ', $meta['uri'] ) );
		}
	}

	return $write_result;
}