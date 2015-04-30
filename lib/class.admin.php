<?php

/**
 * Class for Admin UI Elements
 *
 * @since 1.0.0
 * @access public
 * @author Aaron Brazell <aaron@technosailor.com>
 */
class Clarify_Admin {

    /**
     * Constructor
     *
     * @since 1.0.0
     * @access public
     * @author Aaron Brazell <aaron@technosailor.com>
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Hooks methods into WordPress
     *
     * @since 1.0.0
     * @author Aaron Brazell <aaron@technosailor.com>
     */
    public function hooks() {
        add_action( 'admin_menu', array( $this, 'menu' ) );
        add_action( 'admin_init', array( $this, 'save' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
        add_action( 'add_meta_boxes', array( $this, 'processing_alert' ),1 );
    }

    /**
     * Enqueues Javascript
     *
     * @since 1.0.0
     * @author Aaron Brazell <aaron@technosailor.com>
     * @see `admin_enqueue_scripts`
     */
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

    /**
     * Hooks in a new Clarify menu
     *
     * @since 1.0.0
     * @author Aaron Brazell <aaron@technosailor.com>
     * @see `admin_menu`
     */
    public function menu() {
        add_options_page( __( 'Clarify', 'clarify' ), __( 'Clarify', 'clarify' ), 'manage_options', 'clarify.php', array( $this, 'panel' ), 'dashicons-media-interactive', 30 );
    }

    /**
     * HTML for admin panel generation
     *
     * @since 1.0.0
     * @author Aaron Brazell <aaron@technosailor.com>
     *
     * @return void
     */
    public function panel() {

        global $clarifyio;

        $api_key = ( $ak = get_option( 'clarify_apikey' ) ) ? esc_attr( $ak ) : '';
        ?>
        <div class="wrap">
        <h2><?php _e( 'Clarify Options', 'clarify' ) ?></h2>
        <p>This plugin allows you to integrate Clarify's audio and video search capabilities directly into your on-site search.</p>
        <p>After you put an API key in below, any audio or video files you embed in posts will automatically be indexed through
            Clarify. Then when a visitor uses your on-site search, WordPress will automatically search the body of your post <em>and</em>
            the content of the audio and video with Clarify. This plugin will handle combining the results and letting the visitor
            jump directly to the parts they're looking for.</p>
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

            /**
             * Allows plugin developers to add additional Clarify settings
             *
             * @since 1.0.0
             * @author Aaron Brazell <aaron@technosailor.com>
             */
            do_action( 'clarify_admin_settings' );

            wp_nonce_field( 'clarify_save', 'clarify_save' );
            submit_button( __( 'Save', 'clarify' ), 'primary' );
            ?>
        </form>
    <?php
    }

    /**
     * Saves Clarify settings
     *
     * @since 1.0.0
     * @author Aaron Brazell <aaron@technosailor.com>
     * @see `admin_init`
     *
     * @return bool
     */
    public function save() {
        if( !array_key_exists( 'clarify_save', $_POST ) )
            return false;

        if( !wp_verify_nonce( $_POST['clarify_save'], 'clarify_save' ) )
            return false;

        update_option( 'clarify_apikey', $_POST['api_key'] );
    }

    /**
     * Notification method for display while media is being analyzed.
     *
     * Notifies users that the video is being processed by Clarify. After Clarify calls back, the postmeta _clarify_bundle_id is created and the message goes away
     *
     * @uses Clarify_Bundle_API
     * @author Keith Casey <support@clarify.io>
     * @since 1.0.0
     */
    public function processing_alert($post_type)
    {
        global $pagenow;

        $post_types = array('post', 'page');     //limit meta box to certain post types
        if ( in_array( $post_type, $post_types ) && $pagenow != 'post-new.php') {
            add_meta_box(
                'some_meta_box_name'
                ,__( 'Clarify: Indexing Status', 'clarify' )
                ,array( $this, 'render_meta_box_content' )
                ,$post_type
                ,'advanced'
                ,'high'
            );
        }
    }

    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content( $post )
    {
        $key = get_post_meta( get_the_ID(), '_clarify_bundle_id', true );
        $enclosure = get_post_meta( get_the_ID(), 'enclosure', true );

        if (strlen($key) > 0 ) {
            _e( 'This media is indexed with Clarify.', 'clarify' );
        } else {
            if (strlen($enclosure) > 0) {
                _e( 'Your media file is still processing with Clarify. When it is complete, it will be available via search. On average, it will take about 1 minute for every minute of media. An hour long podcast will take about an hour to process.', 'clarify' );
            }
        }
    }
}