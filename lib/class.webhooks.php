<?php

class Clarify_Webhooks_Bundle_Notify {

	/**
	 * Catches postback data from Clarify and inserts related metadata into the appropriate post ID
	 *
	 * @author Aaron Brazell <aaron@technosailor.com>
	 * @since 1.0.0
	 * @return bool
	 */
	public function recieve() {

		if( filter_input( INPUT_SERVER, 'REQUEST_METHOD' ) != 'POST' ) {
			return false;
		}

		$required = array(
			'bundle_id',
			'track_id',
			'external_id'
		);
		$data = json_decode( file_get_contents('php://input') );

		if( !is_array( $data ) )
			return false;

		foreach( $required as $key ) {
			if( !array_key_exists( $key, $data ) )
				continue;
		}

		$id = (int) $data->external_id;
		add_post_meta( $id, '_clarify_track_id', $this->_validate( $data->track_id, 'hex' ) );
		add_post_meta( $id, '_clarify_bundle_id', $this-_validate( $date->bundle_id, 'hex' ) );
	}

	/**
	 * Simple validation. Leaving it scaffolded for more types
	 *
	 * @author Aaron Brazell <aaron@technosailor.com>
	 * @since 1.0.0
	 * @param $str
	 * @param $type
	 *
	 * @return bool
	 */
	protected function _validate( $str, $type ) {
		switch( $type ) {
			case 'hex' :
				$safe = ( ctype_xdigit( $str ) ) ? true : false;
				break;
			default :
				break;
		}
		return ( $safe ) ? $str : false;
	}
}