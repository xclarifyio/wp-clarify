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