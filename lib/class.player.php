<?php

class Clarify_Players {

	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'wp_audio_shortcode', array( $this, 'add_start_time' ), 10, 5 );
		add_filter( 'wp_video_shortcode', array( $this, 'add_start_time' ), 10, 5 );
	}

	public function add_start_time( $html, $atts, $audio, $post_id, $library ) {

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

		$this_timestamps = $api_results[$this_post_bundle];

		/*
		 * Array
(
    [0] => stdClass Object
        (
            [start] => 673.73
            [end] => 674.13
        )

    [1] => stdClass Object
        (
            [start] => 676.31
            [end] => 676.6
        )

    [2] => stdClass Object
        (
            [start] => 678.74
            [end] => 679.09
        )

    [3] => stdClass Object
        (
            [start] => 723.36
            [end] => 723.78
        )

    [4] => stdClass Object
        (
            [start] => 745.02
            [end] => 745.37
        )

)
		 */

		preg_match( '#id="((audio|video)?-\d+-\d+)"#', $html, $dom );

		if( !isset( $dom[1] )  )
			return $html;
		$dom_id = esc_js( $dom[1] );
		// Because JS is stupid and interprets - in DOM IDs as subtraction
		//$_dom_id = str_replace( '-', '_', $dom_id );

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
		$handles = '<ul class="clarify-seek-handles">';
		$iterator = 1;
		foreach( $this_timestamps as $timestamp ) {
			$start = round( $timestamp->start - 2 );
			$handles .= '<li>' . sprintf( __( 'Mention', 'clarify' ) . ' %d: %s', $iterator, '<a class="clarify-seek-handle" href="#" data-timestamp="' . $start . '">' . $start . ' seconds</a>' ) . '</li>';
			$iterator++;
		}
		$handles .= '</ul>';
		$html = $html . $script . $handles;
		return $html;
	}
	public function enqueue() {
		wp_register_script( 'clarify-player', CLARIFY_URL . '/js/clarify.js', array( 'jquery' ) );
	}
}