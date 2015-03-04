<?php

class Clarify_Admin {

	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_clarify-bulk', array( $this, 'clarify_bulk' ) );
	}

	public function enqueue() {
		wp_register_script( 'clarify-admin', CLARIFY_URL . 'js/admin.js', array( 'jquery' ) );
		wp_localize_script( 'clarify-admin', 'clarify_ajax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'_bulk_nonce' => wp_create_nonce( '_nonce_bulk' ),
			'always' => __( 'Processing...', 'clarify' ),
			'fail' => __( 'Failed', 'clarify' ),
			'success' => __( 'Success', 'clarify' )
		) );
		wp_enqueue_script( 'clarify-admin' );
	}

	public function menu() {
		$hook = add_options_page( __( 'Clarify', 'clarify' ), __( 'Clarify', 'clarify' ), 'manage_options', 'clarify.php', array( $this, 'panel' ), 'dashicons-media-interactive', 30 );

		//add_action( 'admin_enqueue_scripts-' . $hook, 'clarify-admin' );
	}

	public function panel() {

		global $clarifyio;

		$api_key = ( $ak = get_option( 'clarify_apikey' ) ) ? esc_attr( $ak ) : '';
		?>
		<div class="wrap">
			<h2><?php _e( 'Clarify Options', 'clarify' ) ?></h2>
			<form action="" method="post">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="api_key"><?php _e( 'Clarify API Key', 'clarify' ) ?></label>
					</th>
					<td>
						<input type="text" id="api_key" class="regular-text" name="api_key" value="<?php echo $api_key ?>" />
						<small><?php printf( __( 'API Keys can be found <a href="%s">here</a>', 'clarify' ), 'https://developer.clarify.io/apps/list/#' ) ?></small>
					</td>
				</tr>
			</table>
				<?php
				wp_nonce_field( 'clarify_save', 'clarify_save' );
				submit_button( __( 'Save', 'clarify' ), 'primary' );
				?>
			</form>
		<?php
		if( $ak ) {
			?>
			<hr />
			<h2><?php _e( 'Scan for Media', 'clarify' ) ?></h2>
			<p><?php _e( 'By clicking the button below, Clarify will scann all of your published content for supported media, and upload them to the Clarify service. <b>This may take some time. Please do not navigate away from this page.</b>', 'clarify' ) ?></p>
			<p><?php printf( __( 'We support the following media types:', 'clarify' ) . ' <b>%s</b>', implode( ', ', $clarifyio->supported_media ) ) ?></p>
			<input type="button" id="clarify-bulk-media" class="button" value="<?php _e( 'Scan Now', 'clarify' ) ?>" />
			<div id="bulk_result"></div>
			</div>
		<?php
		}
	}

	public function save() {
		if( !array_key_exists( 'clarify_save', $_POST ) )
			return false;

		if( !wp_verify_nonce( $_POST['clarify_save'], 'clarify_save' ) )
			return false;

		update_option( 'clarify_apikey', $_POST['api_key'] );
	}

	public function clarify_bulk() {
		// Never use raw SQL... unless the database overhead is too great not to... such as potentially thousands of queries for thousands of posts
		global $wpdb;
		$sql = "SELECT ID, meta_value FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id) WHERE $wpdb->postmeta.meta_key='enclosure'";
		$results = $wpdb->get_results( $sql );

		$api = new Clarify_Bundle_API;
		foreach( $results as $track ) {
			$val = explode( "\n", $track->meta_value );

			// Make sure this is actually an attached WordPress enclosure
			if( !is_array( $val ) )
				continue;

			// Make sure the enclosure is among the supported media types
			$mimetype = $val[2];
			if( !in_array( $clarifyio->supported_media[$mimetype] ) )
				continue;

			// Extract the valid media url
			$url = $val[0];

			// Construct the object
			$payload = (object) array(
				'name'          => '',
				'media_url'     => esc_url( $url ),
				'external_id'   => '',
				'metadata'      => '',
			);
		}

		echo wp_json_encode( $results );
		exit;
	}

}