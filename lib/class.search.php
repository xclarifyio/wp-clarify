<?php

class Clarify_Search extends Clarify_API_Base {

	public $search_term;
	public $hashes;
	public $ids;

	public function __construct() {
		parent::__construct();

		$this->hashes = false;
		$this->ids = false;

		add_filter( 'the_posts', array( $this, 'search' ) );

		add_filter( 'the_permalink', array( $this, 'search_modify_permalink' ) );

		add_action( 'init', function() {
			global $wp;
			echo '<prE>foo';exit;
		});
	}

	public function search_modify_permalink( $url ) {
		if( !is_search() )
			return $url;

		$body = get_transient( 'clarify-search-body-' . get_query_var( 's' ) );

		echo '<pre>';print_r($body);echo'</pre>';
		return add_query_arg( array( 'foo' => 'bar' ), $url );
	}

	public function search( $posts ) {
		global $wp_query;
		if( !is_search() )
			return $posts;

		$posts = $wp_query->posts;
		$term = get_query_var( 's' );

		$hashes = get_transient( 'clarify-search-' . $term );
		//$hashes = false;
		if( !$hashes ) {
			$url      = esc_url_raw( parent::API_BASE . 'search?query=' . $term );
			$response = wp_remote_get( $url, $this->headers );
			$body     = json_decode( wp_remote_retrieve_body( $response ) );
			//$this->clarify_search = $body;
			//echo '<pre>';print_r( $body );exit;

			$hashes = array();
			foreach( $body->_links->items as $item ) {
				$href = $item->href;
				$bits = explode( '/', $href );
				$hashes[] = end( $bits );
			}
			set_transient( 'clarify-search-' . $term, $hashes, 3600 );
			return $body;
		}

		$this->hashes = $hashes;

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
					add_query_arg( array( 'foo' => 'bar' ), get_permalink($mi->ID) );
					$mi->clarify = true;
					$mi->start = 125;
 					$posts[] = $mi;
				}
			}
		}
		return $posts;
	}
}