<?php

class Clarify_API_Base {

	public static $headers;
	public function __construct() {
		$apikey = get_option( 'clarify_apikey' );
		self::$headers = array( 'headers' => array( 'Bearer ' . $apikey ) );
	}
}
class Clarify_API extends Clarify_API_Base {

	const API_BASE = 'https://api.clarify.io/v1/';

	public static function get_bundles( $limit = 5, $embed = 'items', $iteerator = false ) {
		//parent::__construct();
		echo '<pre>';print_r(self::$headers);echo'</pre>';
		$endpoint = esc_url_raw( self::API_BASE . '/bundles/' );
		$response = wp_remote_get( $endpoint, parent::$headers );
		$body = wp_remote_retrieve_body( $response );
		//echo '<pre>';print_r( $body );echo'</pre>';
	}
}