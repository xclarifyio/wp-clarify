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
		$data = new stdClass;
		$data->module_id = false;
		$data->track_id = false;
		$data->external_id = false;

		$data = file_get_contents('php://input');

		if( !$this->is_json( $data ) )
			return false;
		$data = json_decode( $data );
		if( filter_input( INPUT_SERVER, 'REQUEST_METHOD' ) != 'POST' && $data->module_id ) {
			return false;
		}

		$required = array(
			'bundle_id',
			'track_id',
			'external_id'
		);


		if( !is_object( $data ) )
			return false;

		$id = (int) $data->external_id;

		if( $data->track_id )
			update_post_meta( $id, '_clarify_track_id', $data->track_id );
		if( $data->bundle_id )
			update_post_meta( $id, '_clarify_bundle_id',$data->bundle_id );
	}

	public function is_json( $string ) {
		$maybe = json_decode( $string );
		if( is_array( $maybe ) || is_object( $maybe ) ) {
			return true;
		}

		return false;
	}
}