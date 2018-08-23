( function ( $ ) {
	"use strict";
	var request = {
		'option'	: 'com_ajax',
		'group'		: 'twofactorauth',
		'plugin'	: 'VerifySignatureLogin',
		'format'	: 'json'
	};
	$( window ).ready( function () {
		$( "#hydro_raindrop_authenticate" ).on( "click", function (e) {
			e.preventDefault();
			$.ajax( {
				method: "POST",
				data: request
			} ).done( function ( msg ) {
				console.log( msg, msg.data );
				if (msg.data[0] == true) window.location.reload();
			} );
			return false;
		} );

	} );
} )( jQuery );