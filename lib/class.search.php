<?php

/**
 * Extends the base API class and performs searches against the Clarify API
 *
 * @extends Clarify_API_Base
 * @since 1.0.0
 * @access public
 * @author Aaron Brazell <aaron@technosailor.com>
 */
class Clarify_Search extends Clarify_API_Base {

    /**
     * Defines the number of seconds to store transient search data ion seconds. 300 = 5 minutes
     *
     * @const SEARCH_TRANSIENT_EXPIRY Number of seconds to store search data as a transient
     * @since 1.0.0
     * @author Aaron Brazell <aaron@technosailor.com>
     */
    const SEARCH_TRANSIENT_EXPIRY = 300;

    /**
     * @var string Makes the search term available to the whole class
     * @since 1.0.0
     * @access public
     * @author Aaron Brazell <aaron@technosailor.com>
     */
    public $search_term;

    /**
     * @var array makes the hashes of each bundle available to the entire class
     * @since 1.0.0
     * @access public
     * @author Aaron Brazell <aaron@technosailor.com>
     */
    public $hashes;

    /**
     * Constructor
     *
     * @since 1.0.0
     * @access public
     * @author Aaron Brazell <aaron@technosailor.com>
     */
    public function __construct() {
        parent::__construct();

        $this->hashes = false;

        add_filter( 'the_posts', array( $this, 'search' ) );
    }

    /**
     * Helper method to determine if the page is a result of a search query
     *
     * @since 1.0.0
     * @access public
     * @author Aaron Brazell <aaron@technosailor.com>
     * @uses wp_get_referer()
     *
     * @return bool|string Returns false if we are not coming from search, or the search term if we are
     */
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

    /**
     * Performs an API/transient search and combines the results with the standard WP search post objects into a standard set of post objects
     * @param $posts A list of standard post objects generated from a standard WP Search query
     *
     * @since 1.0.0
     * @author Aaron Brazell <aaron@technosailor.com>
     * @access public
     * @see `the_posts` filter
     *
     * @return array
     */
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

            if( !isset( $timestamps ) || !is_array( $timestamps ) )
                $timestamps = array();

            $api_results = array_combine( $bundles, $timestamps );

            set_transient( 'clarify-search-' . $term, $api_results, self::SEARCH_TRANSIENT_EXPIRY );
        }

        $hashes = array();
        foreach( $api_results as $key => $item ) {
            $hashes[] = $key;
        }

        if( !empty( $hashes ) ) {

            /**
             * Filter to modify query args
             *
             * Allows plugin developers to modify default query used in searching WP for Clarify embeds. Most obvious one is `posts_per_page`
             *
             * @since 1.0.0
             * @author Aaron Brazell <aaron@technosailor.com>
             *
             * @param array $filterable_args {
             *      WP_Query args used for search results from WordPress
             *
             *      @type string post_type. Default 'any'
             *      @type boolean no_found_rows Useful for performance. Default false
             *      @type boolean update_post_term_cache Useful for performance when we are not performing taxonomy queries. Default false
             *      @type int posts_per_page Number of results to return. Default 100
             * }
             */
            $filterable_args = apply_filters( 'clarify_filterable_search_args', array(
                'post_type'              => 'any',
                'no_found_rows'          => false,
                'update_post_term_cache' => false,
                'posts_per_page'         => 100,
            ) );

            $args = array_merge( $filterable_args, array(
                'meta_query'             => array(
                    array(
                        'key'     => '_clarify_bundle_id',
                        'value'   => $hashes,
                        'compare' => 'IN',
                    )
                )
            ) );
            $mq = query_posts( $args );

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
        $wp_query->found_posts = count($posts);

        return $posts;
    }

    protected function _api_search( $term ) {
        $url      = esc_url_raw( parent::API_BASE . 'search?query=' . $term );
        $response = wp_remote_get( $url, $this->headers );
        $body     = json_decode( wp_remote_retrieve_body( $response ) );
        return $body;
    }
}