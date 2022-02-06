<?php
/**
 * PHP wrapper class for VIPS under MediaWiki
 *
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
 * @file
 */

namespace MediaWiki\Extension\VipsScaler;

use Exception;
use MediaWiki\Shell\Shell;

/**
 * A wrapper class around im_conv because that command expects a convolution
 * matrix file as its last argument
 */
class VipsConvolution extends VipsCommand {

	/**
	 * @return int
	 */
	public function execute() {
		$format = $this->getFormat( $this->input );
		# Convert a 2D array into a space/newline separated matrix
		$convolutionMatrix = array_pop( $this->args );
		$convolutionString = '';
		foreach ( $convolutionMatrix as $row ) {
			$convolutionString .= implode( ' ', $row ) . "\n";
		}
		# Save the matrix in a tempfile
		$tmpFile = self::makeTemp( 'conv' );
		$tmpFile->bind( $this );
		$convolutionFile = $tmpFile->getPath();
		file_put_contents( $convolutionFile, $convolutionString );
		array_push( $this->args, $convolutionFile );

		$tmpOutput = self::makeTemp( 'v' );
		$tmpOutput->bind( $this );
		$tmpOutputPath = $tmpOutput->getPath();

		wfDebug( __METHOD__ . ": Convolving image [\n" . $convolutionString . "] \n" );

		$limits = [ 'filesize' => 409600 ];
		$env = [ 'IM_CONCURRENCY' => '1' ];

		$cmd = [
			$this->vips,
			array_shift( $this->args ),
			$this->input,
			$tmpOutputPath
		];
		$cmd = array_merge( $cmd, $this->args );

		// Execute
		$result = Shell::command( $cmd )
			->environment( $env )
			->limits( $limits )
			->includeStderr()
			->execute();
		$retval = $result->getExitCode();
		$this->err = $result->getStdout();

		if ( $retval === 0 ) {
			// vips seems to get confused about the bit depth after a convolution
			// so reset it. Without this step, 16-bit tiff files seem to become all
			// black when converted to pngs (https://github.com/jcupitt/libvips/issues/344)
			$formatCmd = [
				$this->vips, 'im_clip2fmt', $tmpOutputPath, $this->output, (string)$format
			];
			$result = Shell::command( $formatCmd )
				->environment( $env )
				->limits( $limits )
				->includeStderr()
				->execute();
			$retval = $result->getExitCode();
			$this->err .= $result->getStdout();
		}

		// Cleanup temp file
		if ( $retval != 0 && file_exists( $this->output ) ) {
			unlink( $this->output );
		}
		if ( $this->removeInput ) {
			unlink( $this->input );
		}

		// Remove the temporary matrix file
		$tmpFile->purge();
		$tmpOutput->purge();

		return $retval;
	}

	/**
	 * Get the vips internal format (aka bit depth)
	 *
	 * @see https://github.com/jcupitt/libvips/issues/344 for why we do this
	 * @param string $input Path to file
	 * @return int Vips internal format number
	 *   (common value 0 = VIPS_FORMAT_UCHAR, 2 = VIPS_FORMAT_USHORT)
	 */
	private function getFormat( $input ) {
		$cmd = [ $this->vips, 'im_header_int', 'format', $input ];
		$result = Shell::command( $cmd )
			->environment( [ 'IM_CONCURRENCY' => '1' ] )
			->execute();

		$res = trim( $result->getStdout() );

		if ( $result->getExitCode() !== 0 || !is_numeric( $res ) ) {
			throw new Exception( "Cannot determine vips format of image" );
		}

		$format = (int)$res;
		// Must be in range -1 to 10
		// We might want to be even stricter. Its assumed that the answer will usually be 0 or 2.
		if ( $format < -1 || $format > 10 ) {
			throw new Exception( "vips format '$format' is invalid" );
		}
		if ( $format === -1 || $format >= 6 ) {
			// This will still work, but not something we expect to ever get. So log.
			wfDebugLog( 'vips', __METHOD__ . ": Vips format value is outside the range expected " .
				"(got: $format)\n" );
		}

		return $format;
	}
}
