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
require_once( CLARIFY_PATH . '/lib/class.admin.php' );
require_once( CLARIFY_PATH . '/lib/class.webhooks.php' );

register_activation_hook( __FILE__, array( 'Clarify', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Clarify', 'deactivate' ) );


class Clarify {
	public function __construct() {
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
			load_plugin_textdomain( 'brightcove', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
	}

	public function hooks() {

		/*if( is_admin() ) {
			add_action( 'init', array( $this, 'admin' ) );
		}
		*/
	}

	public function admin() {
		$admin = new Clarify_Admin;
	}

	public function save_post() {

	}
}

new Clarify;