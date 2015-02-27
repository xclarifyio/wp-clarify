<?php
/*
Plugin Name: WP-Clarify
Author: Aaron Brazell
Author URI: http://technosailor.com
Description: Leverages the Clarify API
License: MIT
License URI: https://github.com/technosailor/wp-clarify/blob/master/LICENSE
*/


require_once( plugin_dir_path( __FILE__ ) . '/lib/class.api.php' );
require_once( plugin_dir_path( __FILE__ ) . '/lib/class.admin.php' );

class Clarify {
	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'admin_init', array( $this, 'admin' ) );
	}

	public function admin() {
		new Clarify_Admin;
	}
}

$clarify = new Clarify;