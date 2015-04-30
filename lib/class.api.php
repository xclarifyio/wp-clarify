<?php

/**
 * Base class for all Clarify APIs. This should be extended
 *
 * @since 1.0.0
 * @access public
 * @author Aaron Brazell <aaron@technosailor.com>
 */
class Clarify_API_Base {

    /**
     * Defines base api endpoint
     *
     * @const API_BASE base api endpoint
     * @since 1.0.0
     * @author Aaron Brazell <aaron@technosailor.com>
     */
    const API_BASE = 'https://api.clarify.io/v1/';

    /**
     * @var array default headers to be sent with API requests. Classes should override this if necessary
     * @since 1.0.0
     * @access public
     * @author Aaron Brazell <aaron@technosailor.com>
     */
    public $headers;

    /**
     * Constructor
     *
     * @since 1.0.0
     * @access public
     * @author Aaron Brazell <aaron@technosailor.com>
     */
    public function __construct() {
        $apikey = get_option( 'clarify_apikey' );
        $this->headers = array( 'headers' => array( 'Authorization' => 'Bearer ' . $apikey ) );
    }
}

/**
 * Class to interact with Clarify's Bundle API
 *
 * @extends Clarify_API_Base
 * @since 1.0.0
 * @access public
 * @author Aaron Brazell <aaron@technosailor.com>
 */
class Clarify_Bundle_API extends Clarify_API_Base {

    /**
     * @var string the designated callback URL for Clarify to send post data to upon ingestion completion
     * @access public
     * @since 1.0.0
     * @author Aaron Brazell <aaron@technosailor.com>
     */
    public $notify_url;

    /**
     * Constructor
     *
     * @since 1.0.0
     * @access public
     * @author Aaron Brazell <aaron@technosailor.com>
     */
    public function __construct() {
        parent::__construct();

        $this->notify_url = site_url( '?clarify_notify_type=bundle' );
    }

    /**
     * Retrieves cached bundles or performs API call to retrieve bundles
     *
     * @param int    $limit Unused. The number of results to return. Default 5
     * @param string $embed Unused. Link relations to include in the results. Can be items|tracks|metadata. Default `links`
     * @param bool   $iterator Unused. Default `false`
     *
     * @access public
     * @since 1.0.0
     * @author Aaron Brazell <aaron@technosailor.com>
     *
     * @return object API results
     */
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

    /**
     * Retrieves cached information about a specific bundle, or retrieves the info directly from the API
     *
     * @param $path string the URL path of the bundle, as represented by the API
     *
     * @access public
     * @since 1.0.0
     * @author Aaron Brazell <aaron@technosailor.com>
     *
     * @return array|mixed
     */
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


    /**
     * Creates a new bundle via the API and saves relevant data in postmeta
     *
     * @param $post_id int The WordPress post ID that the media is embedded in
     * @param $post object The WordPress post object
     *
     * @access public
     * @since 1.0.0
     * @author Aaron Brazell <aaron@technosailor.com>
     *
     * @return bool
     */
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

                continue;
            }
        }

        return true;
    }
}