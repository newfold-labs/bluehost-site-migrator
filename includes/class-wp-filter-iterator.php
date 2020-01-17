<?php

/**
 * Class BH_Move_WP_Filter_Iterator
 *
 * Filter any standard WordPress directories or files so that we can package up all the other randomness.
 */
class BH_Move_WP_Filter_Iterator extends RecursiveFilterIterator {

	/**
	 * Collection of file extensions to exclude.
	 *
	 * @var array
	 */
	public static $extensions = BH_Move_Zip_Filter_Iterator::EXCLUDE_EXTENSIONS;

	/**
	 * Collection of file names to exclude.
	 *
	 * @var array
	 */
	public static $file_names = BH_Move_Zip_Filter_Iterator::EXCLUDE_PATHS;

	/**
	 * Collection of file paths to exclude.
	 *
	 * @var array
	 */
	public static $file_paths;

	/**
	 * Collection of directories to exclude.
	 *
	 * @var array
	 */
	public static $directories;

	/**
	 * Whether or not to accept the file.
	 *
	 * @return bool
	 */
	public function accept() {
		$accept = true;
		if ( $this->current()->isDir() ) {
			return $this->filter_by_directory();
		} else {
			return $this->filter_by_file_path() && $this->filter_by_file_name() && $this->filter_by_extension();
		}

		return $accept;
	}

	/**
	 * Filter by extension.
	 *
	 * @return bool
	 */
	public function filter_by_extension() {
		return ! in_array( $this->current()->getFilename(), self::$extensions, true );
	}

	/**
	 * Filter by file path.
	 *
	 * @return bool
	 */
	public function filter_by_file_path() {

		if ( ! isset( self::$file_paths ) ) {

			self::$file_paths = array(
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
				ABSPATH . 'xmlrpc.php',
				WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'index.php',
			);

			if ( ! function_exists( 'get_dropins' ) ) {
				require ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$dropins = array_keys( get_dropins() );
			foreach ( $dropins as $dropin ) {
				self::$file_paths[] = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $dropin;
			}
		}

		foreach ( self::$file_paths as $file ) {
			if ( 0 === strpos( $this->current()->getRealPath(), $file ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Filter by file name.
	 *
	 * @return bool
	 */
	public function filter_by_file_name() {
		return ! in_array( $this->current()->getFilename(), self::$file_names, true );
	}

	/**
	 * Filter by directory.
	 *
	 * @return bool
	 */
	public function filter_by_directory() {

		if ( ! isset( self::$directories ) ) {

			$uploads = wp_upload_dir();

			self::$directories = array(
				ABSPATH . 'wp-admin',
				ABSPATH . 'wp-includes',
				WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'upgrade',
				WPMU_PLUGIN_DIR,
				WP_PLUGIN_DIR,
				get_theme_root(),
				$uploads['basedir'],
			);

		}

		foreach ( self::$directories as $path ) {
			if ( 0 === strpos( $this->current()->getRealPath(), $path ) ) {
				return false;
			}
		}

		return true;
	}

}
