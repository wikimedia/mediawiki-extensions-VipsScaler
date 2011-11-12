jQuery( function( $ ) {
	var container = document.getElementById( 'mw-vipstest-thumbnails' );
	if ( container ) {
		$( '<p id="mw-vipstest-buttons">\n' +
			'<button id="mw-vipstest-show-both">' + 
				mediaWiki.message( 'vipsscaler-show-both' ).escaped() + 
			'</button> ' + 
			'<button id="mw-vipstest-show-default">' + 
				mediaWiki.message( 'vipsscaler-show-default' ).escaped() + 
			'</button> ' + 
			'<button id="mw-vipstest-show-vips">' + 
				mediaWiki.message( 'vipsscaler-show-vips' ).escaped() + 
			'</button> ' +
		   '</p>'
		).prependTo( container );
		

		$( '#mw-vipstest-show-both' ).click( function() {
			document.getElementById( 'mw-vipstest-show-default' ).style.display = 'inline';
			document.getElementById( 'mw-vipstest-show-vips' ).style.display = 'inline';
			document.getElementById( 'mw-vipstest-default-thumb' ).style.display = 'block';
			document.getElementById( 'mw-vipstest-vips-thumb' ).style.display = 'block';
		} );
		$( '#mw-vipstest-show-default' ).click( function() {
			document.getElementById( 'mw-vipstest-show-default' ).style.display = 'none';
			document.getElementById( 'mw-vipstest-show-vips' ).style.display = 'inline';
			document.getElementById( 'mw-vipstest-default-thumb' ).style.display = 'block';
			document.getElementById( 'mw-vipstest-vips-thumb' ).style.display = 'none';
		} );	
		$( '#mw-vipstest-show-vips' ).click( function() {
			document.getElementById( 'mw-vipstest-show-default' ).style.display = 'inline';
			document.getElementById( 'mw-vipstest-show-vips' ).style.display = 'none';
			document.getElementById( 'mw-vipstest-default-thumb' ).style.display = 'none';
			document.getElementById( 'mw-vipstest-vips-thumb' ).style.display = 'block';
		} );
	}
}	
);
