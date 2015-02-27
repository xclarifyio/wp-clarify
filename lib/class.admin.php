<?php

class Clarify_Admin {

	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
	}

	public function menu() {
		add_options_page( __( 'Clarify Options', 'clarify' ), __( 'Clarify Options', 'clarify' , 'read', 'clarify.php', array( $this, 'panel' ) ) );
	}

	public function panel() {

	}
}