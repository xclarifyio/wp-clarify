<?php

class Clarify_Webhooks {

	public function __construct() {

		add_filter( 'init', array( $this, 'endpoints' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );

		add_action( 'template_redirect', array( $this, 'notification_bundle' ) );
	}

	public function query_vars( $vars ) {
		$vars[] = 'clarify_notify_type';
		$vars[] = 'clarify_notify_id';
		return $vars;
	}

	public function endpoints() {
		global $wp_rewrite;

		$new = array(
			'^clarify/notify/track/$' => 'index.php?clarify_notify_type=track',
			'^clarify/notify/bundle/$' => 'index.php?clarify_notify_type=bundle',
		);

		$wp_rewrite->rules = $new + $wp_rewrite->rules;
		return $wp_rewrite;
	}

	public function recieve() {
		// Do Nothing
	}
}

class Clarify_Webhooks_Track_Notify extends Clarify_Webhooks {

	public function __construct() {
		parent::__construct();
	}

	public function recieve() {
		if( !is_array( $_POST ) )
			return false;

		$required = array(
			'bundle_id',
			'track_id',
			'external_id'
		);

		foreach( $required as $key ) {
			if( !array_key_exists( $key, $_POST ) )
				return false;
		}

		error_log( print_r( json_decode( $_POST, true ) ), 1, 'aaron@technosailor.com' );
	}
}