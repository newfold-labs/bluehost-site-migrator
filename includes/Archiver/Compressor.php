<?php

namespace BluehostSiteMigrator\Archiver;

use BluehostSiteMigrator\Archiver\Archiver;
use Throwable;

/**
 * Extends archiver to allow creating zips for the necessary files.
 */
class Compressor extends Archiver {
	/**
	 * Overloaded constructor that opens the passed file for writing
	 *
	 * @param string $file_name File to use as archive
	 */
	public function __construct( $file_name ) {
		parent::__construct( $file_name, true );
	}

	/**
	 * Add a file to the archive
	 *
	 * @param string $file_name     File to add to the archive
	 * @param string $new_file_name Write the file with a different name
	 * @param int    $file_written  File written (in bytes)
	 * @param int    $file_offset   File offset (in bytes)
	 * @param bool   $encrypt       Whether to encrypt the file or not.
	 * @param string $encrypt_pass  The password to be used for encryption.
	 *
	 * @throws \Exception On errors out of space and other errors with file.
	 *
	 * @return bool
	 */
	public function add_file( $file_name, $new_file_name = '', $file_written = 0, $file_offset = 0, $encrypt = false, $encrypt_pass = 'pass' ) {

		$file_written = 0;

		// Replace forward slash with current directory separator in file name
		$file_name = nfd_bhsm_replace_forward_slash_with_directory_separator( $file_name );

		// Escape Windows directory separator in file name
		$file_name = nfd_bhsm_escape_windows_directory_separator( $file_name );

		// Flag to hold if file data has been processed
		$completed = true;

		// Start time
		$start = microtime( true );

		// Open the file for reading in binary mode (fopen may return null for quarantined files)
		$file_handle = fopen( $file_name, 'rb' );
		if ( $file_handle ) {
			$file_bytes = 0;

			// Get header block
			$block = $this->get_file_block( $file_name, $new_file_name );
			if ( $block ) {
				// Write header block
				if ( 0 === $file_offset ) {
					$file_bytes = fwrite( $this->file_handle, $block );
					if ( false !== $file_bytes ) {
						if ( strlen( $block ) !== $file_bytes ) {
							throw new \Exception( sprintf( 'Out of disk space. Unable to write header to file. File: %s', esc_xml( $this->file_name ) ) );
						}
					} else {
						throw new \Exception( sprintf( 'Unable to write header to file. File: %s', esc_xml( $this->file_name ) ) );
					}
				}

					// Set file offset
				if ( fseek( $file_handle, $file_offset, SEEK_SET ) !== -1 ) {

					// Read the file in 512KB chunks
					while ( false === feof( $file_handle ) ) {

						// Read the file in chunks of 512KB
						$file_content = fread( $file_handle, 512000 );
						if ( false !== $file_content ) {
							// Don't encrypt package.json
							if ( $encrypt && basename( $file_name ) !== 'package.json' ) {
								$file_content = nfd_bhsm_encrypt_string( $file_content, $encrypt_pass );
							}

							$file_bytes = fwrite( $this->file_handle, $file_content );

							if ( false !== $file_bytes ) {
								if ( strlen( $file_content ) !== $file_bytes ) {
									throw new \Exception( sprintf( 'Out of disk space. Unable to write content to file. File: %s', esc_xml( $this->file_name ) ) );
								}
							} else {
								throw new \Exception( sprintf( 'Unable to write content to file. File: %s', esc_xml( $this->file_name ) ) );
							}

							// Set file written
							$file_written += $file_bytes;
						}

						// Time elapsed
						$timeout = apply_filters( 'nfd_bhsm_completed_timeout', 10 );
						if ( $timeout ) {
							if ( ( microtime( true ) - $start ) > $timeout ) {
								$completed = false;
								break;
							}
						}
					}
				}

				// Set file offset
				$file_offset += $file_written;

				// Write file size to file header
				$block = $this->get_file_size_block( $file_offset );
				if ( $block ) {
					// Seek to beginning of file size
					if ( -1 === fseek( $this->file_handle, - $file_offset - 4096 - 12 - 14, SEEK_CUR ) ) {
						throw new \Exception( 'Your PHP is 32-bit. In order to export your file, please change your PHP version to 64-bit and try again. <a href="https://help.servmask.com/knowledgebase/php-32bit/" target="_blank">Technical details</a>' );
					}

					// Write file size to file header
					$file_bytes = fwrite( $this->file_handle, $block );
					if ( false !== $file_bytes ) {
						if ( strlen( $block ) !== $file_bytes ) {
							throw new \Exception( sprintf( 'Out of disk space. Unable to write size to file. File: %s', esc_xml( $this->file_name ) ) );
						}
					} else {
						throw new \Exception( sprintf( 'Unable to write size to file. File: %s', esc_xml( $this->file_name ) ) );
					}

					// Seek to end of file content
					if ( -1 === fseek( $this->file_handle, + $file_offset + 4096 + 12, SEEK_CUR ) ) {
						throw new \Exception( 'Your PHP is 32-bit. In order to export your file, please change your PHP version to 64-bit and try again. <a href="https://help.servmask.com/knowledgebase/php-32bit/" target="_blank">Technical details</a>' );
					}
				}
			}

			// Close the handle
			fclose( $file_handle );
		}

		return $completed;
	}

	/**
	 * Generate binary block header for a file
	 *
	 * @param string $file_name     Filename to generate block header for
	 * @param string $new_file_name Write the file with a different name
	 *
	 * @return string
	 */
	private function get_file_block( $file_name, $new_file_name = '' ) {
		$block = '';

		// Get stats about the file
		$stat = stat( $file_name );
		if ( false !== $stat ) {

			// Filename of the file we are accessing
			if ( empty( $new_file_name ) ) {
				$name = basename( $file_name );
			} else {
				$name = basename( $new_file_name );
			}

			// Size in bytes of the file
			$size = $stat['size'];

			// Last time the file was modified
			$date = $stat['mtime'];

			// Replace current directory separator with backward slash in file path
			if ( empty( $new_file_name ) ) {
				$path = nfd_bhsm_replace_directory_separator_with_forward_slash( dirname( $file_name ) );
			} else {
				$path = nfd_bhsm_replace_directory_separator_with_forward_slash( dirname( $new_file_name ) );
			}

			// Concatenate block format parts
			$format = implode( '', $this->block_format );

			// Pack file data into binary string
			$block = pack( $format, $name, $size, $date, $path );
		}

		return $block;
	}

	/**
	 * Generate file size binary block header for a file
	 *
	 * @param int $file_size File size
	 *
	 * @return string
	 */
	public function get_file_size_block( $file_size ) {
		$block = '';

		// Pack file data into binary string
		if ( isset( $this->block_format[1] ) ) {
			$block = pack( $this->block_format[1], $file_size );
		}

		return $block;
	}
}
