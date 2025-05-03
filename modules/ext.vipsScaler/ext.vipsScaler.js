( function () {
	'use strict';

	function switchThumbs() {
		const e = $( '#mw-vipstest-thumbnails' ),
			mask = e.children( '.uc-mask' ),
			caption = e.children( '.uc-caption' ),
			width = e.width(),
			maskWidth = mask.width();

		if ( maskWidth < width / 2 ) {
			// Bar is 3 pixels width. We want to show it on the right.
			mask.width( width - 3 );
			caption.html( e.children( 'img:eq(0)' ).attr( 'alt' ) );
		} else {
			mask.width( 0 );
			caption.html( e.children( 'img:eq(1)' ).attr( 'alt' ) );
		}
	}

	$( () => {
		if ( !document.getElementById( 'mw-vipstest-thumbnails' ) ) {
			return;
		}
		// options are detailed in upstream documentation available at
		// http://www.userdot.net/files/jquery/jquery.ucompare/demo/
		//
		// Copying them here for version 1.0
		// - caption: toggle the
		// - leftgap: the gap to the left of the image
		// - rightgap: the gap to the right of the image
		// - defaultgap: the default gap shown before any interactions
		$( '#mw-vipstest-thumbnails' ).ucompare( {
			defaultgap: 50,
			leftgap: 0,
			rightgap: 0,
			caption: true,
			reveal: 0.5
		} );

		// Also add a click handler to instantly switch beetween pics
		// This can be done by clicking the thumbnail or using a checkbox
		$( '#mw-vipstest-thumbs-switch' ).on(
			'click',
			() => {
				switchThumbs();
			}
		);
		$( '#mw-vipstest-thumbnails' ).on(
			'click',
			() => {
				switchThumbs();
			}
		);
	} );

}() );
