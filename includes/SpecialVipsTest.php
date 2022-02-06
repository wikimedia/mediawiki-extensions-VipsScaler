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

namespace MediaWiki\Extension\VipsScaler;

use Html;
use HTMLForm;
use MediaTransformError;
use MediaTransformOutput;
use MediaWiki\MediaWikiServices;
use MWException;
use OOUI\CheckboxInputWidget;
use OOUI\FieldLayout;
use OOUI\FieldsetLayout;
use OOUI\HtmlSnippet;
use OOUI\LabelWidget;
use OOUI\PanelLayout;
use PermissionsError;
use SpecialPage;
use Status;
use StreamFile;
use Title;
use User;
use Wikimedia\AtEase\AtEase;
use Wikimedia\IPUtils;

/**
 * A Special page intended to test the VipsScaler.
 * @author Bryan Tong Minh
 */
class SpecialVipsTest extends SpecialPage {
	public function __construct() {
		parent::__construct( 'VipsTest', 'vipsscaler-test' );
	}

	/**
	 * @inheritDoc
	 */
	public function userCanExecute( User $user ) {
		global $wgVipsExposeTestPage;

		return $wgVipsExposeTestPage && parent::userCanExecute( $user );
	}

	/**
	 * @inheritDoc
	 */
	public function displayRestrictionError() {
		global $wgVipsExposeTestPage;

		if ( !$wgVipsExposeTestPage ) {
			throw new PermissionsError(
				null,
				[ 'querypage-disabled' ]
			);
		}

		parent::displayRestrictionError();
	}

	/**
	 * Entry point
	 * @param string|null $par TODO describe what is expected there
	 */
	public function execute( $par ) {
		$request = $this->getRequest();
		$this->setHeaders();

		if ( !$this->userCanExecute( $this->getUser() ) ) {
			$this->displayRestrictionError();
		}

		if ( $request->getText( 'thumb' ) ) {
			$this->streamThumbnail();
		} else {
			$this->showForm();
		}
	}

	/**
	 */
	protected function showThumbnails() {
		$request = $this->getRequest();
		$this->getOutput()->enableOOUI();
		// Check if there is any input
		if ( !( $request->getText( 'file' ) ) ) {
			return;
		}

		// Check if valid file was provided
		$title = Title::newFromText( $request->getText( 'file' ), NS_FILE );
		if ( $title === null ) {
			$this->getOutput()->addWikiMsg( 'vipsscaler-invalid-file' );
			return;
		}
		$file = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $title );
		if ( !$file || !$file->exists() ) {
			$this->getOutput()->addWikiMsg( 'vipsscaler-invalid-file' );
			return;
		}

		// Create options
		$width = $request->getInt( 'width' );
		if ( !$width ) {
			$this->getOutput()->addWikiMsg( 'vipsscaler-invalid-width' );
			return;
		}
		$vipsUrlOptions = [ 'thumb' => $file->getName(), 'width' => $width ];
		if ( $request->getRawVal( 'sharpen' ) !== null ) {
			$vipsUrlOptions['sharpen'] = $request->getFloat( 'sharpen' );
		}
		if ( $request->getBool( 'bilinear' ) ) {
			$vipsUrlOptions['bilinear'] = 1;
		}

		// Generate normal thumbnail
		$params = [ 'width' => $width ];
		$thumb = $file->transform( $params );
		if ( !$thumb || $thumb->isError() ) {
			$this->getOutput()->addWikiMsg( 'vipsscaler-thumb-error' );
			return;
		}

		// Check if we actually scaled the file
		$normalThumbUrl = $thumb->getUrl();
		if ( wfExpandUrl( $normalThumbUrl ) == $file->getFullUrl() ) {
			// TODO: message
		}

		// Make url to the vips thumbnail
		$vipsThumbUrl = $this->getPageTitle()->getLocalUrl( $vipsUrlOptions );

		// HTML for the thumbnails
		$thumbs = new HtmlSnippet( Html::rawElement( 'div', [ 'id' => 'mw-vipstest-thumbnails' ],
			Html::element( 'img', [
				'src' => $normalThumbUrl,
				'alt' => $this->msg( 'vipsscaler-default-thumb' )->text(),
			] ) . ' ' .
			Html::element( 'img', [
				'src' => $vipsThumbUrl,
				'alt' => $this->msg( 'vipsscaler-vips-thumb' )->text(),
			] )
		) );

		// Helper messages shown above the thumbnails rendering
		$form = [
			new LabelWidget( [ 'label' => $this->msg( 'vipsscaler-thumbs-help' )->text() ] )
		];

		// A checkbox to easily alternate between both views:
		$form[] = new FieldLayout(
				new CheckboxInputWidget( [
					'name' => 'mw-vipstest-thumbs-switch',
					'inputId' => 'mw-vipstest-thumbs-switch',
				] ),
				[
					'label' => $this->msg( 'vipsscaler-thumbs-switch-label' )->text(),
					'align' => 'inline',
					'infusable' => true,
				]
			);

		$fieldset = new FieldsetLayout( [
			'label' => $this->msg( 'vipsscaler-thumbs-legend' )->text(),
			'items' => $form,
		] );

		$this->getOutput()->addHTML(
			new PanelLayout( [
				'expanded' => false,
				'padded' => true,
				'framed' => true,
				'content' => [ $fieldset , $thumbs ],
			] )
		);

		// Finally output all of the above
		$this->getOutput()->addModules( [
			'ext.vipsscaler',
			'jquery.ucompare',
		] );
	}

	/**
	 * TODO
	 */
	protected function showForm() {
		$form = HTMLForm::factory( 'ooui', $this->getFormFields(), $this->getContext() );
		$form->setWrapperLegend( $this->msg( 'vipsscaler-form-legend' )->text() );
		$form->setSubmitText( $this->msg( 'vipsscaler-form-submit' )->text() );
		$form->setSubmitCallback( [ __CLASS__, 'processForm' ] );
		$form->setMethod( 'get' );

		// Looks like HTMLForm does not actually show the form if submission
		// was correct. So we have to show it again.
		// See HTMLForm::show()
		$result = $form->show();
		if ( $result === true || $result instanceof Status && $result->isGood() ) {
			$form->displayForm( $result );
			$this->showThumbnails();
		}
	}

	/**
	 * [[Special:VipsTest]] form structure for HTMLForm
	 * @return array A form structure using the HTMLForm system
	 */
	protected function getFormFields() {
		$fields = [
			'File' => [
				'name'          => 'file',
				'class'         => 'HTMLTextField',
				'required'      => true,
				'size' 			=> '80',
				'label-message' => 'vipsscaler-form-file',
				'validation-callback' => [ __CLASS__, 'validateFileInput' ],
			],
			'Width' => [
				'name'          => 'width',
				'class'         => 'HTMLIntField',
				'default'       => '640',
				'size'          => '5',
				'required'      => true,
				'label-message' => 'vipsscaler-form-width',
				'validation-callback' => [ __CLASS__, 'validateWidth' ],
			],
			'SharpenRadius' => [
				'name'          => 'sharpen',
				'class'         => 'HTMLFloatField',
				'default'		=> '0.0',
				'size'			=> '5',
				'label-message' => 'vipsscaler-form-sharpen-radius',
				'validation-callback' => [ __CLASS__, 'validateSharpen' ],
			],
			'Bilinear' => [
				'name' 			=> 'bilinear',
				'class' 		=> 'HTMLCheckField',
				'label-message'	=> 'vipsscaler-form-bilinear',
			],
		];

		/**
		 * Match ImageMagick by default
		 */
		global $wgSharpenParameter;
		if ( preg_match( '/^[0-9.]+x([0-9.]+)$/', $wgSharpenParameter, $m ) ) {
			$fields['SharpenRadius']['default'] = $m[1];
		}
		return $fields;
	}

	/**
	 * @param string $input
	 * @param array $alldata
	 * @return bool|string
	 */
	public static function validateFileInput( $input, $alldata ) {
		if ( !trim( $input ) ) {
			// Don't show an error if the file is not yet specified,
			// because it is annoying
			return true;
		}

		$title = Title::newFromText( $input, NS_FILE );
		if ( $title === null ) {
			return wfMessage( 'vipsscaler-invalid-file' )->text();
		}
		$file = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $title );
		if ( !$file || !$file->exists() ) {
			return wfMessage( 'vipsscaler-invalid-file' )->text();
		}

		// Looks sensible enough.
		return true;
	}

	/**
	 * @param int $input
	 * @param array $allData
	 * @return bool|string
	 */
	public static function validateWidth( $input, $allData ) {
		if ( self::validateFileInput( $allData['File'], $allData ) !== true
			|| !trim( $allData['File'] )
		) {
			// Invalid file, error will already be shown at file field
			return true;
		}
		$title = Title::newFromText( $allData['File'], NS_FILE );
		$file = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $title );
		if ( $input <= 0 || $input >= $file->getWidth() ) {
			return wfMessage( 'vipsscaler-invalid-width' )->text();
		}
		return true;
	}

	/**
	 * @param int $input
	 * @param array $allData
	 * @return bool|string
	 */
	public static function validateSharpen( $input, $allData ) {
		if ( $input >= 5.0 || $input < 0.0 ) {
			return wfMessage( 'vipsscaler-invalid-sharpen' )->text();
		}
		return true;
	}

	/**
	 * Process data submitted by the form.
	 * @param array $data
	 * @return Status
	 */
	public static function processForm( array $data ) {
		return Status::newGood();
	}

	/**
	 *
	 */
	protected function streamThumbnail() {
		global $wgVipsThumbnailerHost, $wgVipsTestExpiry;

		$request = $this->getRequest();

		// Validate title and file existance
		$title = Title::newFromText( $request->getText( 'thumb' ), NS_FILE );
		if ( $title === null ) {
			$this->streamError( 404, "VipsScaler: invalid title\n" );
			return;
		}
		$services = MediaWikiServices::getInstance();
		$file = $services->getRepoGroup()->findFile( $title );
		if ( !$file || !$file->exists() ) {
			$this->streamError( 404, "VipsScaler: file not found\n" );
			return;
		}

		// Check if vips can handle this file
		if ( VipsScaler::getVipsHandler( $file ) === false ) {
			$this->streamError( 500, "VipsScaler: VIPS cannot handle this file type\n" );
			return;
		}

		// Validate param string
		$handler = $file->getHandler();
		$params = [ 'width' => $request->getInt( 'width' ) ];
		if ( !$handler->normaliseParams( $file, $params ) ) {
			$this->streamError( 500, "VipsScaler: invalid parameters\n" );
			return;
		}

		// Get the thumbnail
		if ( $wgVipsThumbnailerHost === null || $request->getBool( 'noproxy' ) ) {
			// No remote scaler, need to do it ourselves.
			// Emulate the BitmapHandlerTransform hook

			$tmpFile = VipsCommand::makeTemp( $file->getExtension() );
			$tmpFile->bind( $this );
			$dstPath = $tmpFile->getPath();
			$dstUrl = '';
			wfDebug( __METHOD__ . ": Creating vips thumbnail at $dstPath\n" );

			$scalerParams = [
				// The size to which the image will be resized
				'physicalWidth' => $params['physicalWidth'],
				'physicalHeight' => $params['physicalHeight'],
				'physicalDimensions' => "{$params['physicalWidth']}x{$params['physicalHeight']}",
				// The size of the image on the page
				'clientWidth' => $params['width'],
				'clientHeight' => $params['height'],
				// Comment as will be added to the EXIF of the thumbnail
				'comment' => isset( $params['descriptionUrl'] ) ?
					"File source: {$params['descriptionUrl']}" : '',
				// Properties of the original image
				'srcWidth' => $file->getWidth(),
				'srcHeight' => $file->getHeight(),
				'mimeType' => $file->getMimeType(),
				'srcPath' => $file->getLocalRefPath(),
				'dstPath' => $dstPath,
				'dstUrl' => $dstUrl,
				'interlace' => $request->getBool( 'interlace' ),
			];

			$options = [];
			if ( $request->getBool( 'bilinear' ) ) {
				$options['bilinear'] = true;
				wfDebug( __METHOD__ . ": using bilinear scaling\n" );
			}
			if ( $request->getRawVal( 'sharpen' ) !== null && $request->getFloat( 'sharpen' ) < 5 ) {
				// Limit sharpen sigma to 5, otherwise we have to write huge convolution matrices
				$sharpen = $request->getFloat( 'sharpen' );
				$options['sharpen'] = [ 'sigma' => $sharpen ];
				wfDebug( __METHOD__ . ": sharpening with radius {$sharpen}\n" );
			}

			// Call the hook
			/** @var MediaTransformOutput $mto */
			VipsScaler::doTransform( $handler, $file, $scalerParams, $options, $mto );
			if ( $mto && !$mto->isError() ) {
				wfDebug( __METHOD__ . ": streaming thumbnail...\n" );
				$this->getOutput()->disable();
				StreamFile::stream( $dstPath, [
					"Cache-Control: public, max-age=$wgVipsTestExpiry, s-maxage=$wgVipsTestExpiry",
					'Expires: ' . gmdate( 'r ', time() + $wgVipsTestExpiry )
				] );
			} else {
				'@phan-var MediaTransformError $mto';
				$this->streamError( 500, $mto->getHtmlMsg() );
			}

			// Cleanup the temporary file
			AtEase::suppressWarnings();
			unlink( $dstPath );
			AtEase::restoreWarnings();

		} else {
			// Request the thumbnail at a remote scaler
			$url = wfExpandUrl( $request->getRequestURL(), PROTO_INTERNAL );
			$url = wfAppendQuery( $url, [ 'noproxy' => '1' ] );
			wfDebug( __METHOD__ . ": Getting vips thumb from remote url $url\n" );

			$bits = IPUtils::splitHostAndPort( $wgVipsThumbnailerHost );
			if ( !$bits ) {
				throw new MWException( __METHOD__ . ': $wgVipsThumbnailerHost is not set to a valid host' );
			}
			list( $host, $port ) = $bits;
			if ( $port === false ) {
				$port = 80;
			}
			$proxy = IPUtils::combineHostAndPort( $host, $port );

			$options = [
				'method' => 'GET',
				'proxy' => $proxy,
			];

			$req = $services->getHttpRequestFactory()
				->create( $url, $options, __METHOD__ );
			$status = $req->execute();
			if ( $status->isOk() ) {
				// Disable output and stream the file
				$this->getOutput()->disable();
				wfResetOutputBuffers();
				header( 'Content-Type: ' . $file->getMimeType() );
				header( 'Content-Length: ' . strlen( $req->getContent() ) );
				header( "Cache-Control: public, max-age=$wgVipsTestExpiry, s-maxage=$wgVipsTestExpiry" );
				header( 'Expires: ' . gmdate( 'r ', time() + $wgVipsTestExpiry ) );
				print $req->getContent();
			} elseif ( $status->hasMessage( 'http-bad-status' ) ) {
				$this->streamError( 500, $req->getContent() );
				return;
			} else {
				$wikitext = Status::wrap( $status )->getWikiText();
				$this->streamError( 500, $this->getOutput()->parseAsInterface( $wikitext ) );
				return;
			}
		}
	}

	/**
	 * Generates a blank page with given HTTP error code
	 *
	 * @param int $code HTTP error either 404 or 500
	 * @param string $error
	 */
	protected function streamError( $code, $error = '' ) {
		$output = $this->getOutput();
		$output->setStatusCode( $code );
		$output->setArticleBodyOnly( true );
		$output->addHTML( $error );
	}

	protected function getGroupName() {
		return 'media';
	}
}
