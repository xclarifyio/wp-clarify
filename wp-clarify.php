<?php
/*
Plugin Name: Podcast Searcher by Clarify
Plugin URI: http://github.com/Clarify/wp-clarify
Version: 1.0.2
Author: Aaron Brazell
Author URI: http://technosailor.com
Maintainer: Clarify, Inc
Maintainer URI: http://Clarify.io
Description: The <a href="http://Clarify.io">Clarify</a> plugin allows you to make any audio or video embedded in your posts, pages, etc searchable via the standard WordPress search box.
License: MIT
License URI: https://github.com/Clarify/wp-clarify/blob/master/LICENSE
*/


/**
 * Clarify version of plugin
 *
 * @author Aaron Brazell <aaron@technosailor.com>
 * @since 1.0.0
 */
define( 'CLARIFY_VERSION', '1.0' );
/**
 * Clarify URL to plugin
 *
 * @author Aaron Brazell <aaron@technosailor.com>
 * @since 1.0.0
 */
define( 'CLARIFY_URL',     plugin_dir_url( __FILE__ ) );

/**
 * Clarify Path to plugin
 *
 * @author Aaron Brazell <aaron@technosailor.com>
 * @since 1.0.0
 */
define( 'CLARIFY_PATH',    dirname( __FILE__ ) . '/' );

/**
 * Base API class
 */
require_once( CLARIFY_PATH . '/lib/class.api.php' );

/**
 * Child class of the base API class. Handles API search functionality
 */
require_once( CLARIFY_PATH . '/lib/class.search.php' );

/**
 * Class to handle all admin UI functionality
 */
require_once( CLARIFY_PATH . '/lib/class.admin.php' );

/**
 * Class to handle API callbacks
 */
require_once( CLARIFY_PATH . '/lib/class.webhooks.php' );

/**
 * Class to handle munging of media embeds
 */
require_once( CLARIFY_PATH . '/lib/class.player.php' );

/**
 * Declares a method to fire on the activation event
 */
register_activation_hook( __FILE__, array( 'Clarify', 'activate' ) );

/**
 * Declares a method to fire on the deactivation event
 */
register_deactivation_hook( __FILE__, array( 'Clarify', 'deactivate' ) );


/**
 * Handles the day to day activities of the Clarify Plugin
 *
 * @author Aaron Brazell <aaron@technosailor.com>
 * @since 1.0.0
 */
class Clarify {

    /**
     * List of media types supported by Clarify
     * @var array
     *
     * @author Aaron Brazell <aaron@technosailor.com>
     * @since 1.0.0
     */
    public $supported_media;

    /**
     * Constructor
     *
     * @author Aaron Brazell <aaron@technosailor.com>
     * @since 1.0.0
     */
    public function __construct() {

        $this->supported_media = array(
            'mpeg',
            'mp3',
            'wav',
            'mp4',
            'mov',
            'ogg',
            'flac',
            'webm'
        );
        $this->hooks();
    }

    /**
     * Activation method
     *
     * @author Aaron Brazell <aaron@technosailor.com>
     * @since 1.0.0
     */
    public static function activate() {}

    /**
     * Deactivation method
     *
     * @author Aaron Brazell <aaron@technosailor.com>
     * @since 1.0.0
     */
    public static function deactivate() {}

    /**
     * WordPress hook interactions
     *
     * @author Aaron Brazell <aaron@technosailor.com>
     * @since 1.0.0
     */
    public function hooks() {

        add_action( 'plugins_loaded', function(){
            $webhook = new Clarify_Webhooks_Bundle_Notify;
            $webhook->recieve();
        } );

        add_action( 'transition_post_status', array( $this, 'save_post' ), 10, 3 );
        add_action( 'init', array( $this, 'register_search' ) );
        if( is_admin() ) {
            add_action( 'init', array( $this, 'admin' ) );
        }

        add_filter( 'wp_audio_extensions', function( $types ){
            $types[] = 'mpeg';
            $types[] = 'flac';
            return $types;
        } );

        add_filter( 'wp_video_extensions', function( $types ) {
            $types[] = 'mov';
            return $types;
        } );

        add_action( 'plugins_loaded', array( $this, 'do_media' ),1 );

        add_action( 'wp_enqueue_scripts', function() {
            wp_enqueue_script( 'jquery' );
        });
    }

    /**
     * Instantiates the Player class
     *
     * @uses Clarify_Players
     * @author Aaron Brazell <aaron@technosailor.com>
     * @since 1.0.0
     */
    public function do_media() {
        $players = new Clarify_Players;
    }

    /**
     * Instantiates the Search class
     *
     * @uses Clarify_Search
     * @author Aaron Brazell <aaron@technosailor.com>
     * @since 1.0.0
     */
    public function register_search() {
        new Clarify_Search;
    }

    /**
     * Instantiates the Admin class
     *
     * @uses Clarify_Admin
     * @author Aaron Brazell <aaron@technosailor.com>
     * @since 1.0.0
     */
    public function admin() {
        new Clarify_Admin;
    }

    /**
     * Fires notification to the Clarify API when a post is published
     *
     * @param $new_status
     * @param $old_status
     * @param $post
     *
     * @uses Clarify_Bundle_API
     * @author Aaron Brazell <aaron@technosailor.com>
     * @since 1.0.0
     *
     * @return bool
     */
    public function save_post( $new_status, $old_status, $post ) {
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return false;

        if ( wp_is_post_revision( $post->ID ) )
            return false;

        if( 'publish' != $new_status )
            return false;

        $api = new Clarify_Bundle_API;
        return $api->save_bundle( $post->ID, $post );
    }
}

$clarifyio = new Clarify;