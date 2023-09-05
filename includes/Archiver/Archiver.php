<?php

namespace BluehostSiteMigrator\Archiver;

/**
 * Contains the utilities for creating, managing and truncating an archive
 */
abstract class Archiver {

	/**
	 * Filename including path to the file
	 *
	 * @var string
	 */
	protected $file_name = null;

	/**
	 * Handle to the file
	 *
	 * @var resource
	 */
	protected $file_handle = null;

	/**
	 * Header block format of a file
	 *
	 * Field Name    Offset    Length    Contents
	 * name               0       255    filename (no path, no slash)
	 * size             255        14    size of file contents
	 * mtime            269        12    last modification time
	 * prefix           281      4096    path name, no trailing slashes
	 *
	 * @var array
	 */
	protected $block_format = array(
		'a255',  // filename
		'a14',   // size of file contents
		'a12',   // last time modified
		'a4096', // path
	);

	/**
	 * End of file block string
	 *
	 * @var string
	 */
	protected $eof = null;

	/**
	 * Default constructor
	 *
	 * Initializes filename and end of file block
	 *
	 * @param string $file_name Archive file
	 * @param bool   $write     Read/write mode
	 *
	 * @throws \Exception When we cannot open or seek to file location.
	 */
	public function __construct( $file_name, $write = false ) {
		$this->file_name = $file_name;

		// Initialize end of file block
		$this->eof = pack( 'a4377', '' );

		// Open archive file
		if ( $write ) {
			// Open archive file for writing
			$this->file_handle = fopen( $file_name, 'cb' );
			if ( false === $this->file_handle ) {
				throw new \Exception(
					sprintf(
						// translators: %s: file name
						esc_html__( 'Unable to open file for writing. File: %s', 'bluehost-site-migrator' ),
						esc_xml( $this->file_name )
					)
				);
			}

			// Seek to end of archive file
			if ( -1 === fseek( $this->file_handle, 0, SEEK_END ) ) {
				throw new \Exception(
					sprintf(
						// translators: %s: file name
						esc_html__( 'Unable to seek to end of file. File: %s', 'bluehost-site-migrator' ),
						esc_xml( $this->file_name )
					)
				);
			}
		} else {
			// Open archive file for reading
			$this->file_handle = fopen( $file_name, 'rb' );
			if ( false === $this->file_handle ) {
				throw new \Exception(
					sprintf(
						// translators: %s: file name
						esc_html__( 'Unable to open file for reading. File: %s', 'bluehost-site-migrator' ),
						esc_xml( $this->file_name )
					)
				);
			}
		}
	}

	/**
	 * Set current file pointer
	 *
	 * @param int $offset Archive offset
	 *
	 * @throws \Exception When we are unable to seek to the given offset.
	 *
	 * @return void
	 */
	public function set_file_pointer( $offset ) {
		if ( -1 === fseek( $this->file_handle, $offset, SEEK_SET ) ) {
			throw new \Exception(
				sprintf(
					// translators: %s: file name %d: offset
					esc_html__( 'Unable to seek to offset of file. File: %1$s Offset: %2$d', 'bluehost-site-migrator' ),
					esc_xml( $this->file_name ),
					esc_xml( $offset )
				)
			);
		}
	}

	/**
	 * Get current file pointer
	 *
	 * @throws \Exception When we cannot get the current position of file handler.
	 *
	 * @return int
	 */
	public function get_file_pointer() {
		$offset = ftell( $this->file_handle );
		if ( false === $offset ) {
			throw new \Exception(
				sprintf(
					// translators: %s: file name
					esc_html__( 'Unable to tell offset of file. File: %s', 'bluehost-site-migrator' ),
					esc_xml( $this->file_name )
				)
			);
		}

		return $offset;
	}

	/**
	 * Appends end of file block to the archive file
	 *
	 * @throws \Exception When we cannot open, seek or write to file or if we are out of disk space.
	 *
	 * @return void
	 */
	protected function append_eof() {
		// Seek to end of archive file
		if ( -1 === fseek( $this->file_handle, 0, SEEK_END ) ) {
			throw new \Exception(
				sprintf(
					// translators: %s: file name
					esc_html__( 'Unable to seek to end of file. File: %s', 'bluehost-site-migrator' ),
					esc_xml( $this->file_name )
				)
			);
		}

		// Write end of file block
		$file_bytes = fwrite( $this->file_handle, $this->eof );
		if ( false === $file_bytes ) {
			if ( strlen( $this->eof ) !== $file_bytes ) {
				throw new \Exception(
					sprintf(
						// translators: %s: file name
						esc_html__( 'Out of disk space. Unable to write end of block to file. File: %s', 'bluehost-site-migrator' ),
						esc_xml( $this->file_name )
					)
				);
			}
		} else {
			throw new \Exception(
				sprintf(
					// translators: %s: file name
					esc_html__( 'Unable to write end of block to file. File: %s', 'bluehost-site-migrator' ),
					esc_xml( $this->file_name )
				)
			);
		}
	}

	/**
	 * Replace forward slash with current directory separator
	 *
	 * @param string $path Path
	 *
	 * @return string
	 */
	protected function replace_forward_slash_with_directory_separator( $path ) {
		return str_replace( '/', DIRECTORY_SEPARATOR, $path );
	}

	/**
	 * Replace current directory separator with forward slash
	 *
	 * @param string $path Path
	 *
	 * @return string
	 */
	protected function replace_directory_separator_with_forward_slash( $path ) {
		return str_replace( DIRECTORY_SEPARATOR, '/', $path );
	}

	/**
	 * Escape Windows directory separator
	 *
	 * @param string $path Path
	 *
	 * @return string
	 */
	protected function escape_windows_directory_separator( $path ) {
		return preg_replace( '/[\\\\]+/', '\\\\\\\\', $path );
	}

	/**
	 * Validate archive file
	 *
	 * @return bool
	 */
	public function is_valid() {
		$offset = ftell( $this->file_handle );
		// Failed detecting the current file pointer offset
		if ( false === $offset ) {
			return false;
		}

		// Failed seeking the beginning of EOL block
		if ( -1 === fseek( $this->file_handle, -4377, SEEK_END ) ) {
			return false;
		}

		// Trailing block does not match EOL: file is incomplete
		if ( fread( $this->file_handle, 4377 ) !== $this->eof ) {
			return false;
		}

		// Failed returning to original offset
		if ( -1 === fseek( $this->file_handle, $offset, SEEK_SET ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Truncates the archive file
	 *
	 * @return void
	 *
	 * @throws \Exception When we are not able to perform the file related options.
	 */
	public function truncate() {
		$offset = ftell( $this->file_handle );
		if ( false === $offset ) {
			throw new \Exception(
				sprintf(
					// translators: %s: file name
					esc_html__( 'Unable to tell offset of file. File: %s', 'bluehost-site-migrator' ),
					esc_xml( $this->file_name )
				)
			);
		}

		if ( filesize( $this->file_name ) > $offset ) {
			if ( ftruncate( $this->file_handle, $offset ) === false ) {
				throw new \Exception(
					sprintf(
					// translators: %s: file name
						esc_html__( 'Unable to truncate file. File: %s', 'bluehost-site-migrator' ),
						esc_xml( $this->file_name )
					)
				);
			}
		}
	}

	/**
	 * Closes the archive file
	 *
	 * We either close the file or append the end of file block if complete argument is set to true
	 *
	 * @param  bool $complete Flag to append end of file block
	 *
	 * @return void
	 *
	 * @throws \Exception When we are unable to close the file.
	 */
	public function close( $complete = false ) {
		// Are we done appending to the file?
		if ( true === $complete ) {
			$this->append_eof();
		}

		if ( fclose( $this->file_handle ) === false ) {
			throw new \Exception(
				sprintf(
					// translators: %s: file name
					esc_html__( 'Unable to close file. File: %s', 'bluehost-site-migrator' ),
					esc_xml( $this->file_name )
				)
			);
		}
	}
}
