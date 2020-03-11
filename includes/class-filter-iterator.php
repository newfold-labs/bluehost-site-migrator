<?php

/**
 * Class BH_Move_Filter_Iterator
 *
 * Generic filter iterator with the ability to inject rules.
 */
class BH_Move_Filter_Iterator extends RecursiveFilterIterator {

	/**
	 * Whether or not to accept the file.
	 *
	 * @return bool
	 */
	public function accept() {
		return apply_filters( 'bh_move_filter_files', true, $this->current() );
	}

}
