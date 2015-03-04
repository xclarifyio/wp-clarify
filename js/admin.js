jQuery(document).ready(function($){

	var notice_container = $('#bulk_result');

	$('#clarify-bulk-media').on( 'click', function() {
		$.post(
			clarify_ajax.ajaxurl,
			{
				action : 'clarify-bulk',
				_bulk_nonce : clarify_ajax._bulk_nonce
			},
			function( json ) {
				var data = $.parseJSON( json );
				console.debug( data );
				//notice_container(clarify_ajax.success);
			}
			/*.always( function(){
				notice_container.html(clarify_ajax.always);
			})
			.fail( function(){
				notice_container.html(clarify_ajax.fail);
			})
			.success( function( json ) {
				var data = $.parseJSON( json );
				console.debug( data );
				notice_container(clarify_ajax.success);
				}
			)*/
		);
	});
	//console.log(clarify_ajax.ajaxurl);
});