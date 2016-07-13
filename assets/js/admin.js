; jQuery( function( $ ) {
	
	var timeout;

	$( '.bibliomundi-button' ).click( function() {
		var _this   = $( this );
		var buttons = $( '.bibliomundi-button' );
		var nonce   = $( '#bbm-nonce' ).val();
		var scope   = _this.hasClass( 'complete' ) ? 'complete' : 'updates';
		var alert   = _this.parent().find('.bibliomundi-alert');
		
		if( ! buttons.hasClass( 'disabled' ) ) {
			buttons.removeClass( 'loading' )
					.addClass( 'disabled' );
			
			_this.addClass( 'loading' );

			clearTimeout( timeout );
			alert.removeClass('error updated').empty();


			var data = {
				'action' 	: 'bibliomundi_import_catalog',
				'security' 	: nonce,
				'scope' 	: scope,
			};
			
			$.post( ajaxurl, data, function( d ) {
				_this.removeClass( 'loading' );
				buttons.removeClass( 'disabled' );
				
				alert.text( d.msg ).addClass( d.error ? 'error' : 'updated' ).show();
				timeout = setTimeout( function() {
					alert.hide().empty();
				}, 3000 );
			}, 'json' );
		} 

		return false;
	} );

} );