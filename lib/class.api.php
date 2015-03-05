<?php

class Clarify_API_Base {

	const API_BASE = 'https://api.clarify.io/v1/';

	public $headers;

	public function __construct() {
		$apikey = get_option( 'clarify_apikey' );
		$this->headers = array( 'headers' => array( 'Authorization' => 'Bearer ' . $apikey ) );
	}
}
class Clarify_Bundle_API extends Clarify_API_Base {

	public $notify_url;

	public function __construct() {
		parent::__construct();

		$this->notify_url = site_url( '?clarify_notify_type=bundle' );
	}

	public function get_bundles( $limit = 5, $embed = 'items', $iterator = false ) {

		$body = wp_cache_get( 'bundles', 'bundles' );
		if( !$body ) {
			$endpoint = esc_url_raw( parent::API_BASE . 'bundles' );
			$response = wp_remote_get( $endpoint, $this->headers );
			$body     = wp_remote_retrieve_body( $response );
			wp_cache_set( 'bundles', $body, 'bundles', 3600 );
		}
		return json_decode( $body );
	}

	public function get_bundle( $path ) {
		$bits = explode( '/', $path );
		$id = array_pop( $bits );

		$body = wp_cache_get( 'bundle ' . $id, 'bundles' );
		if( !$body ) {
			$endpoint = esc_url_raw( parent::API_BASE . 'bundles/' . $id );
			$response = wp_remote_get( $endpoint, $this->headers );
			$body     = wp_remote_retrieve_body( $response );
			wp_cache_set( 'bundle ' . $id, $body, 'bundles', 86400 );
		}
		return json_decode( $body );
	}

	public function save_bundle( $post_id ) {
		global $clarifyio;

		$enclosure = get_post_meta( $post_id, 'enclosure', true );

		if( !$enclosure )
			return false;

		$val = explode( "\n", $enclosure );
		// Make sure this is actually an attached WordPress enclosure
		if( !is_array( $val ) )
			return false;

		// Make sure the enclosure is among the supported media types
		$mimetype = trim( $val[2] );
		if( !array_search( $mimetype, $clarifyio->supported_media ) )
			return false;

		// Extract the valid media url
		$url = trim( $val[0] );

		// Construct the object
		$payload = array(
			'body' => array(
				'media_url'     => esc_url( $url ),
				'external_id'   => (string) $post_id,
				'notify_url'    => $this->notify_url
			)
		);


		$args = array_merge_recursive( $this->headers, $payload );
		$request = wp_remote_post( parent::API_BASE . 'bundles', $args );
		$body = wp_remote_retrieve_body( $request );
		//echo '<prE>';print_r($request);exit;
		if( '201' == wp_remote_retrieve_response_code( $request ) )
			return true;

		return false;
	}
}