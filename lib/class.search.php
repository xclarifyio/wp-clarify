<?php

class Clarify_Search extends Clarify_API_Base {

	const SEARCH_TRANSIENT_EXPIRY = 300;

	public $search_term;
	public $hashes;
	public $ids;

	public $start;

	public function __construct() {
		parent::__construct();

		$this->hashes = false;
		$this->ids = false;

		add_filter( 'the_posts', array( $this, 'search' ) );

		add_action( 'wp_head', array( $this, 'extract_start_end_times_from_search' ) );

		add_filter( 'the_content', array( $this, 'adjust_start' ),1 );
	}

	public function adjust_start( $content ) {
		global $clarifyio;
		$term = $this->_from_search();
		if( !$term )
			return $content;

		$regex   = '#https?:\/\/[www]?.+\.(' . join( '|', $clarifyio->supported_media ) . ')#mi';
		preg_match_all( $regex, $content, $raw_media);
		if( empty( $raw_media[0] ) )
			return $content;

		$medias = $raw_media[0];
		foreach( $medias as $media ) {
			$audio_types = wp_get_audio_extensions();
			$video_types = wp_get_video_extensions();

			$file_info = wp_check_filetype( $media );

			$ext = $file_info['ext'];
			if( in_array( $ext, $audio_types ) )
				$type = 'audio';
			if( in_array( $ext, $video_types ) )
				$type = 'video';

			add_filter( 'shortcode_atts_' . $type, function( $atts ) {
				$atts['start'] = $this->start;
				return $atts;
			});
		}
		return $content;
	}

	public function extract_start_end_times_from_search() {

		$term = $this->_from_search();
		if( !$term )
			return false;

		$data = get_transient( 'clarify-search-' . $term );
		$data = false;
		if( !$data ) {
			$data = $this->_api_search( $term );
		}

		$this->start = (int) round( ( $data->item_results[0]->term_results[0]->matches[0]->hits[0]->start ) - 6 );
	}

	protected function _from_search() {
		$referer = wp_get_referer();

		$bits = parse_url( $referer );
		if( !array_key_exists( 'query', $bits ) )
			return false;

		parse_str( $bits['query'], $out );
		if( !array_key_exists( 's', $out ) )
			return false;
		return $out['s'];
	}

	public function search( $posts ) {
		global $wp_query;
		if( !is_search() )
			return $posts;
		$posts = $wp_query->posts;
		$term = get_query_var( 's' );
		delete_transient( 'clarify-search-' . $term );
		$body = get_transient( 'clarify-search-' . $term );

		if( !$body ) {
			$body = $this->_api_search( $term );
			set_transient( 'clarify-search-' . $term, $body, self::SEARCH_TRANSIENT_EXPIRY );
		}
		//$this->clarify_search = $body;
		$cookie = array();
		$hashes = array();
		foreach( $body->_links->items as $key => $item ) {
			$href = $item->href;
			$bits = explode( '/', $href );
			$hash = end( $bits );
			$hashes[] = $hash;

			$cookie[$hash] = $body->item_results[$key]->term_results[0]->matches[0]->hits[0];
		}


		$json = wp_json_encode( $cookie );

		if( !empty( $hashes ) ) {
			$mq = query_posts( array(
				'post_type'              => 'any',
				'no_found_rows'          => false,
				'update_post_term_cache' => false,
				'posts_per_page'         => 100,
				'meta_query'             => array(
					array(
						'key'     => '_clarify_bundle_id',
						'value'   => $hashes,
						'compare' => 'IN',
					)
				)
			) );

			foreach( $mq as $mi ) {

				$new = true;
				foreach( $posts as $post ) {
					if( $post->ID === $mi->ID ) {
						$new = false;
					};
				}

				if( $new ) {
 					$posts[] = $mi;
				}
			}
		}
		return $posts;
	}

	protected function _api_search( $term ) {
		$url      = esc_url_raw( parent::API_BASE . 'search?query=' . $term );
		$response = wp_remote_get( $url, $this->headers );
		$body     = json_decode( wp_remote_retrieve_body( $response ) );
		return $body;
	}
}