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

	public function save_bundle( $post_id, $post ) {
		global $clarifyio;

		$formats = join( '|', $clarifyio->supported_media );

		$bundle_id = get_post_meta( $post_id, '_clarify_bundle_id', true );
		if( !$bundle_id ) {
			$regex   = '#https?:\/\/[www]?.+\.(' . $formats . ')#mi';

			preg_match_all( $regex, $post->post_content, $raw_media);
			if( empty( $raw_media[0] ) )
				return false;
			$medias = $raw_media[0];

			$payload = array(
				'body' => array(
					'external_id' => (string) $post_id,
					'notify_url'  => $this->notify_url
				)
			);

			foreach( $medias as $url ) {
				$payload[ 'body' ][ 'media_url' ] = $url;
				$args                             = array_merge_recursive( $this->headers, $payload );
				$request                          = wp_remote_post( parent::API_BASE . 'bundles', $args );

				if( '201' == wp_remote_retrieve_response_code( $request ) ) {

					$bundle_id_2 = get_post_meta( $post_id, '_clarify_bundle_id', true );

					if( ! $bundle_id_2 ) {
						wp_remote_post( parent::API_BASE . 'bundles', $args );
						$bundle_id_3 = get_post_meta( $post_id, '_clarify_bundle_id', true );
						//echo $bundle_id_3;exit;
					}
					continue;
				}
				continue;
			}
		}

		return true;
	}
}