<?php
/*
Plugin Name: WP-Clarify
Plugin URI: http://github.com
Verion: 1.0
Author: Aaron Brazell
Author URI: http://technosailor.com
Description: Leverages the Clarify API
License: MIT
License URI: https://github.com/technosailor/wp-clarify/blob/master/LICENSE
*/

define( 'CLAIRFY_VERSION', '1.0' );
define( 'CLARIFY_URL',     plugin_dir_url( __FILE__ ) );
define( 'CLARIFY_PATH',    dirname( __FILE__ ) . '/' );

require_once( CLARIFY_PATH . '/lib/class.api.php' );
require_once( CLARIFY_PATH . '/lib/class.search.php' );
require_once( CLARIFY_PATH . '/lib/class.admin.php' );
require_once( CLARIFY_PATH . '/lib/class.webhooks.php' );
require_once( CLARIFY_PATH . '/lib/class.player.php' );

register_activation_hook( __FILE__, array( 'Clarify', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Clarify', 'deactivate' ) );


class Clarify {

	public $supported_media;

	public function __construct() {

		$this->supported_media = array(
			'mpeg'  => 'audio/mpeg',
			'mp3'   => 'audio/mpeg',
			'wav'   => 'audio/wav',
			'mp4'   => 'video/mp4',
			'mov'   => 'video/quicktime',
			'ogg'   => 'audio/ogg',
			'flac'  => 'audio/flac',
			'webm'  => 'video/webm'
		);
		$this->hooks();
	}

	public static function activate() {
		flush_rewrite_rules();
	}

	public static function deactivate() {}

	public function init() {
		if( defined( WP_LANG_DIR ) ) {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'clarify' );
			load_textdomain( 'clarify', WP_LANG_DIR . '/clarify/clarify-' . $locale . '.mo' );
			load_plugin_textdomain( 'clarify', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
	}

	public function hooks() {

		add_action( 'plugins_loaded', function(){
			$webhook = new Clarify_Webhooks_Bundle_Notify;
			$webhook->recieve();
		} );

		add_action( 'publish_post', array( $this, 'save_post' ), 99 );
		//add_action( 'template_redirect', array( $this, 'register_search' ) );
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

	}

	public function do_media() {
		$players = new Clarify_Players;
	}

	//public function register_search() {

		//if( !is_search() )
		//	return true;
		//global $wp_query;
		//$term = get_query_var( 's' );

		//$search = new Clarify_Search;
		//$results = $search->search( $term );
	//}

	public function admin() {
		$admin = new Clarify_Admin;
	}

	public function save_post( $post_id ) {
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return false;

		if ( wp_is_post_revision( $post_id ) )
			return false;

		if( 'publish' != get_post_status( $post_id ) )
			return false;

		$api = new Clarify_Bundle_API;
		return $api->save_bundle( $post_id );
	}
}

$clarifyio = new Clarify;