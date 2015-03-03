<?php

class Clarify_Admin {

	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'save' ) );
	}

	public function menu() {
		add_options_page( __( 'Clarify Options', 'clarify' ), __( 'Clarify Options', 'clarify' ), 'read', 'clarify.php', array( $this, 'panel' ) );
	}

	public function panel() {

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
		</div>
		<?php

		Clarify_API::get_bundles();
	}

	public function save() {
		if( !wp_verify_nonce( $_POST['clarify_save'], 'clarify_save' ) )
			return false;

		update_option( 'clarify_apikey', $_POST['api_key'] );
	}
}