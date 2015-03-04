<?php

class Clarify_API_Base {

	const API_BASE = 'https://api.clarify.io/v1/';

	public $headers;

	public function __construct() {
		$apikey = get_option( 'clarify_apikey' );
		$this->headers = array( 'headers' => array( 'Authorization' => 'Bearer ' . $apikey ) );
	}
}
class Clarify_API extends Clarify_API_Base {

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
			$endpoint = esc_url_raw( parent::API_BASE . 'bundle/' . $id );
			$response = wp_remote_get( $endpoint, $this->headers );
			$body     = wp_remote_retrieve_body( $response );
			wp_cache_set( 'bundle ' . $id, $body, 'bundles', 86400 );
		}
		return json_decode( $body );
	}
}