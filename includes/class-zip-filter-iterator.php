<?php

/**
 * Class BH_Move_Zip_Filter_Iterator
 */
class BH_Move_Zip_Filter_Iterator extends RecursiveFilterIterator {

	/**
	 * A collection of extensions to exclude.
	 *
	 * @var array
	 */
	const EXCLUDE_EXTENSIONS = array(
		'bak',
		'exe',
		'gz',
		'log',
		'sql',
		'tar',
		'zip',
	);

	/**
	 * A collection of file and directory names to exclude.
	 *
	 * @var array
	 */
	const EXCLUDE_PATHS = array(
		'.git',
		'.gitignore',
		'.idea',
		'.svn',
		'.vscode',
		'bluehost-move',
		'node_modules',
	);

	/**
	 * Whether or not to accept a file.
	 *
	 * @return bool
	 */
	public function accept() {
		return $this->filter_by_extension() && $this->filter_by_path();
	}

	/**
	 * Filter by extension.
	 *
	 * @return bool
	 */
	protected function filter_by_extension() {
		return ! in_array( $this->current()->getExtension(), self::EXCLUDE_EXTENSIONS, true );
	}

	/**
	 * Filter by path.
	 *
	 * @return bool
	 */
	protected function filter_by_path() {
		return ! in_array( $this->current()->getFilename(), self::EXCLUDE_PATHS, true );
	}

}
