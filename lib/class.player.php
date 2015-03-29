<?php

/**
 * Handles the implementation of Clarify results with native WordPress media embeds
 *
 * @since 1.0.0
 * @access public
 * @author Aaron Brazell <aaron@technosailor.com>
 */
class Clarify_Players {

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @author Aaron Brazell <aaron@technosailor.com>
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Hooks methods into WordPress
     *
     * @since 1.0.0
     *
     * @author Aaron Brazell <aaron@technosailor.com>
     */
    public function hooks() {
        add_filter( 'wp_audio_shortcode', array( $this, 'add_start_time' ), 10, 5 );
        add_filter( 'wp_video_shortcode', array( $this, 'add_start_time' ), 10, 5 );
    }

    /**
     * A filter for media shortcodes that adds timestamp results and switcher to media embeds
     * @since 1.0.0
     *
     * @author Aaron Brazell <aaron@technosailor.com>
     * @see `wp_audio_shortcode` filter
     * @see `wp_video_shortcode` filter
     * @link https://core.trac.wordpress.org/browser/tags/4.1.1/src/wp-includes/media.php#L1646
     *
     * @param $html string The HTML source of the embed
     * @param $atts array The shortcode attributes
     * @param $media_element The media URL
     * @param $post_id The post ID of the content
     * @param $library The player JS library used. Default `mediaelement`
     *
     * @return string The modified HTML source for the embed
     */
    public function add_start_time( $html, $atts, $media_element, $post_id, $library ) {

        $search = new Clarify_Search;
        $term = $search->_from_search();

        if( !$term )
            return $html;

        $api_results = get_transient( 'clarify-search-' . $term );
        if( !$api_results )
            return $html;

        $this_post_bundle = get_post_meta( $post_id, '_clarify_bundle_id', true );
        if( !array_key_exists( $this_post_bundle, $api_results ) )
            return $html;

        $timestamps = $api_results[$this_post_bundle];

        preg_match( '#id="((audio|video)?-\d+-\d+)"#', $html, $dom );

        if( !isset( $dom[1] )  )
            return $html;
        $dom_id = esc_js( $dom[1] );

        $script = <<<SCRIPT_TAG
		<script>
		jQuery(document).ready(function($){

			var media  = document.getElementById('$dom_id');
			var src = media.currentSrc;

			$('.clarify-seek-handle').on('click', function(ev){
				var timestamp = $(this).data('timestamp');

				var s = src + '#t=' + timestamp;

				media.src = s;
				media.play();
			});
		});
		</script>
SCRIPT_TAG;

        /**
         * Filter to modify Clarify included Javascript
         *
         * Allows plugin developers to modify the Clarify-generated Javascript. Useful for things like implementing custom JS. Please ensure proper sanitization and security practices
         *
         * @since 1.0.0
         * @author Aaron Brazell <aaron@technosailor.com>
         *
         * @param string $script The Clarify-generated Javascript
         */
        $script = apply_filters( 'clarify_embed_js', $script );

        // Trust NO ONE. A rogue plugin developer could filter our JS and introduce potentially unsafe JS/XSS vulnerabilities.

        $handles = '<ul class="clarify-seek-handles">';
        $iterator = 1;

        foreach( $timestamps as $timestamp ) {
            $start = round( $timestamp->start - 2 );
            $handles .= '<li>' . sprintf( __( 'Mention', 'clarify' ) . ' %d: %s', $iterator, '<a class="clarify-seek-handle" href="#" data-timestamp="' . $start . '">' . $start . ' seconds</a>' ) . '</li>';
            $iterator++;
        }
        $handles .= '</ul>';
        $handles = apply_filters( 'clarify_results_html', $handles, $timestamps );
        $html = $html . $script . $handles;

        /**
         * Filter to modify Clarify HTML structure
         *
         * Allows plugin developers to modify the Clarify-generated HTML. Useful for things like changing DOM structure or adding additional elements
         *
         * @since 1.0.0
         * @author Aaron Brazell <aaron@technosailor.com>
         *
         * @param string $html The Clarify-generated HTML
         */
        return apply_filters( 'clarify_embed_html', $html );
    }
}