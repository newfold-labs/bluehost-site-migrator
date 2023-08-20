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
