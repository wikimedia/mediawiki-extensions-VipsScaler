jQuery( function( $ ) {
	var container = document.getElementById( 'mw-vipstest-thumbnails' );
	if ( container ) {
/*
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
*/		
/**
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
**/
		/**
		 * options are detailed in upstream documentation available at
		 * http://www.userdot.net/files/jquery/jquery.ucompare/demo/
		 *
		 * Copying them here for version 1.0 
		 * - caption: toggle the
		 * - leftgap: the gap to the left of the image 
		 * - rightgap: the gap to the right of the image
		 * - defaultgap: the default gap shown before any interactions
		 */
		$('#mw-vipstest-thumbnails').ucompare({
			defaultgap: 50,
			leftgap: 0,
			rightgap: 0,
			caption: true, 
			reveal: 0.5
   		});

		/** Also add a click handler to instantly switch beetween pics */
		$('#mw-vipstest-thumbnails').click( function() {
			var e = $(this)
			var mask = e.children(".uc-mask")	
			var caption = e.children(".uc-caption")

			width = e.width();
			maskWidth = mask.width();

			if( maskWidth < width / 2 ) {
				mask.width( width );
				caption.html( e.children("img:eq(0)").attr("alt") );
			} else {
				mask.width( 0 );
				caption.html( e.children("img:eq(1)").attr("alt") );
			}	
		});
	}
}	
);
