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
	}

	public function _from_search() {
		if( is_search() )
			return get_query_var( 's' );

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

		$api_results = get_transient( 'clarify-search-' . $term );
		if( !$api_results ) {
			$body = $this->_api_search( $term );
			$combined_hits = array();
			foreach( $body->item_results as $result ) {
				$time = array();
				$matches = $result->term_results[0]->matches;
				$term_hits = array();
				foreach( $matches as $match ) {
					$hits = $match->hits;
					foreach( $hits as $hit ) {
						$term_hits[] = $hit;
					}
					$combined_hits = $term_hits;
				}
				$timestamps[] = $combined_hits;
			}

			$bundles = array();
			foreach( $body->_links->items as $item ) {
				$href = $item->href;
				$parts = explode( '/', $href );
				$bundles[] = end( $parts );
			}

			$api_results = array_combine( $bundles, $timestamps );

			set_transient( 'clarify-search-' . $term, $api_results, self::SEARCH_TRANSIENT_EXPIRY );
		}

		$hashes = array();
		foreach( $api_results as $key => $item ) {
			$hashes[] = $key;
		}

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
		wp_reset_query();

		return $posts;
	}

	protected function _api_search( $term ) {
		$url      = esc_url_raw( parent::API_BASE . 'search?query=' . $term );
		$response = wp_remote_get( $url, $this->headers );
		$body     = json_decode( wp_remote_retrieve_body( $response ) );
		return $body;
	}
}