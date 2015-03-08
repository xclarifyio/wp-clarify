<?php

class Clarify_Search extends Clarify_API_Base {

	public $search_term;

	public function __construct() {
		parent::__construct();
	}

	public function search( $term ) {
		$url = esc_url_raw( parent::API_BASE . 'search?query=' . $term );
		$response = wp_remote_get( $url, $this->headers );
		$body = json_decode( wp_remote_retrieve_body( $response ) );
		echo '<pre>';print_r($body);
		exit;
	}
}