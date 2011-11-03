<?php
/*
 * Copyright © Bryan Tong Minh, 2011
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

/**
 * A Special page intended to test the Vipscaler.
 * @author Bryan Tong Minh
 */
class SpecialVipsTest extends SpecialPage {
	public function __construct() {
		parent::__construct( 'VipsTest', 'vipsscaler-test' );
	}

	/**
	 * Entry point
	 * @param $par Array TODO describe what is expected there
	 */
	public function execute( $par ) {
		$request = $this->getRequest();

		if( !$this->userCanExecute( $this->getUser() ) ) {
			$this->displayRestrictionError();
			return;
		}

		if ( $request->getText( 'thumb' ) && $request->getText( 'file' ) ) {
			$this->streamThumbnail();
		} elseif ( $par || $request->getText( 'file' ) ) {
			$this->showForm();
			$this->showThumbnails();
		} else {
			$this->showForm();
		}
	}

	/**
	 */
	protected function showThumbnails() {
		$request = $this->getRequest();

		$title = Title::makeTitleSafe( NS_FILE, $request->getText( 'file' ) );
		if ( is_null( $title ) ) {
			$this->getOutput()->addWikiMsg( 'vipsscaler-invalid-file' );
			return;
		}
		$file = wfFindFile( $title );
		if ( !$file || !$file->exists() ) {
			$this->getOutput()->addWikiMsg( 'vipsscaler-invalid-file' );
			return;
		}

		$width = $request->getInt( 'width' );
		if ( !$width ) {
			$this->getOutput()->addWikiMsg( 'vipsscaler-invalid-width' );
			return;
		}

		$params = array( 'width' => $width );
		$thumb = $file->transform( $params );
		if ( !$thumb || $thumb->isError() ) {
			$this->getOutput()->addWikiMsg( 'vipsscaler-thumb-error' );
		}

		$vipsThumbUrl = $this->makeUrl( $file, $width );

	}

	/**
	 * TODO
	 */
	protected function showForm() {

	}

	/**
	 *
	 */
	protected function streamThumbnail() {
		global $wgVipsThumbnailerUrl;

		$request = $this->getRequest();

		# Validate title and file existance
		$title = Title::makeTitleSafe( NS_FILE, $request->getText( 'file' ) );
		if ( is_null( $title ) ) {
			return $this->streamError( 404 );
		}
		$file = wfFindFile( $title );
		if ( !$file || !$file->exists() ) {
			return $this->streamError( 404 );
		}

		# Check if vips can handle this file
		if ( VipsScaler::getVipsHandler( $file ) === false ) {
			return $this->streamError( 500 );
		}

		# Validate param string
		$handler = $file->getHandler();
		$params = $handler->parseParamString( $request->getText( 'thumb' ) );
		if ( !$handler->normaliseParams( $file, $params ) ) {
			return $this->streamError( 500 );
		}

		# Get the thumbnail
		if ( is_null( $wgVipsThumbnailerUrl ) ) {
			# No remote scaler, need to do it ourselves.
			# Emulate the BitmapHandlerTransform hook

			$dstPath = VipsCommand::makeTemp( strrchr( $file->getName(), '.' ) );
			$dstUrl = '';

			$scalerParams = array(
				# The size to which the image will be resized
				'physicalWidth' => $params['physicalWidth'],
				'physicalHeight' => $params['physicalHeight'],
				'physicalDimensions' => "{$params['physicalWidth']}x{$params['physicalHeight']}",
				# The size of the image on the page
				'clientWidth' => $params['width'],
				'clientHeight' => $params['height'],
				# Comment as will be added to the EXIF of the thumbnail
				'comment' => isset( $params['descriptionUrl'] ) ?
					"File source: {$params['descriptionUrl']}" : '',
				# Properties of the original image
				'srcWidth' => $file->getWidth(),
				'srcHeight' => $file->getHeight(),
				'mimeType' => $file->getMimeType(),
				'srcPath' => $file->getPath(),
				'dstPath' => $dstPath,
				'dstUrl' => $dstUrl,
			);


			# Call the hook
			$mto = null;
			if ( VipsScaler::onTransform( $handler, $file, $params, $mto ) ) {
				StreamFile::stream( $dstPath );
			} else {
				$this->streamError( 500 );
			}

			# Cleanup the temporary file
			wfSuppressWarnings();
			unlink( $dstPath );
			wfRestoreWarning();

		} else {
			# Request the thumbnail at a remote scaler
			global $wgVipsThumbnailerProxy;

			$url = wfAppendQuery( $wgVipsThumbnailerUrl, array(
				'file' => $file->getName(),
				'thumb' => $handler->makeParamString( $params ) . '-' . $file->getName()
			) );
			$options = array( 'method' => 'GET' );
			if ( $wgVipsThumbnailerProxy ) {
				$options['proxy'] = $wgVipsThumbnailerProxy;
			}

			$req = MWHttpRequest::factory( $url, $options );
			$status = $req->execute();
			if ( $status->isOk() ) {
				# Disable output and stream the file
				$this->getOutput()->disable();
				print 'Content-Type: ' . $file->getMimeType() . "\r\n";
				print 'Content-Length: ' . strlen( $req->getContent() ) . "\r\n";
				print "\r\n";
				print $req->getContent();
			} else {
				return $this->streamError( 500 );
			}

		}
	}

	/**
	 *
	 */
	protected function makeUrl( $file, $width ) {

	}

	protected function streamError( $code ) {

	}

}
