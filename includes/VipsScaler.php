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

use BitmapHandler;
use File;
use ImageHandler;
use MediaHandler;
use MediaTransformOutput;
use MediaWiki\Shell\Shell;
use ThumbnailImage;

/**
 * Wrapper class for VIPS, a free image processing system good at handling
 * large pictures.
 *
 * http://www.vips.ecs.soton.ac.uk/
 *
 * @author Bryan Tong Minh
 */
class VipsScaler {
	/**
	 * Hook to BitmapHandlerTransform. Transforms the file using VIPS if it
	 * matches a condition in $wgVipsConditions
	 *
	 * @param BitmapHandler $handler
	 * @param File $file
	 * @param array &$params
	 * @param MediaTransformOutput &$mto
	 * @return bool
	 */
	public static function onTransform( $handler, $file, &$params, &$mto ) {
		// Check $wgVipsConditions
		$options = self::getHandlerOptions( $handler, $file, $params );
		if ( !$options ) {
			wfDebug( "...\n" );
			return true;
		}
		return self::doTransform( $handler, $file, $params, $options, $mto );
	}

	/**
	 * Performs a transform with VIPS
	 *
	 * @see VipsScaler::onTransform
	 *
	 * @param BitmapHandler|MediaHandler $handler
	 * @param File $file
	 * @param array $params
	 * @param array $options
	 * @param MediaTransformOutput &$mto
	 * @return bool
	 */
	public static function doTransform( $handler, $file, $params, $options, &$mto ) {
		wfDebug( __METHOD__ . ': scaling ' . $file->getName() . " using vips\n" );

		$vipsCommands = self::makeCommands( $handler, $file, $params, $options );
		if ( count( $vipsCommands ) == 0 ) {
			return true;
		}

		$actualSrcPath = $params['srcPath'];
		// Only do this for tiff files, as valid format options in vips is per-format.
		if ( $file->isMultipage() && isset( $params['page'] )
			&& preg_match( '/\.tiff?$/', $actualSrcPath )
		) {
			$actualSrcPath .= $vipsCommands[0]->makePageArgument( $params['page'] );
		}
		// Execute the commands
		/** @var VipsCommand $command */
		foreach ( $vipsCommands as $i => $command ) {
			// Set input/output files
			if ( $i == 0 && count( $vipsCommands ) == 1 ) {
				// Single command, so output directly to dstPath
				$command->setIO( $actualSrcPath, $params['dstPath'] );
			} elseif ( $i == 0 ) {
				// First command, input from srcPath, output to temp
				$command->setIO( $actualSrcPath, 'v', VipsCommand::TEMP_OUTPUT );
			} elseif ( $i + 1 == count( $vipsCommands ) ) {
				// Last command, output to dstPath
				$command->setIO( $vipsCommands[$i - 1], $params['dstPath'] );
			} else {
				$command->setIO( $vipsCommands[$i - 1], 'v', VipsCommand::TEMP_OUTPUT );
			}

			$retval = $command->execute();
			if ( $retval != 0 ) {
				wfDebug( __METHOD__ . ": vips command failed!\n" );
				$error = $command->getErrorString() . "\nError code: $retval";
				$mto = $handler->getMediaTransformError( $params, $error );
				return false;
			}
		}

		// Set comment
		if ( !empty( $options['setcomment'] ) && !empty( $params['comment'] ) ) {
			self::setJpegComment( $params['dstPath'], $params['comment'] );
		}

		// Set the output variable
		$mto = new ThumbnailImage( $file, $params['dstUrl'],
			$params['clientWidth'], $params['clientHeight'], $params['dstPath'] );

		// Stop processing
		return false;
	}

	/**
	 * @param BitmapHandler $handler
	 * @param File $file
	 * @param array $params
	 * @param array $options
	 * @return array
	 */
	public static function makeCommands( $handler, $file, $params, $options ) {
		global $wgVipsCommand;
		$commands = [];

		// Get the proper im_XXX2vips handler
		$vipsHandler = self::getVipsHandler( $file );
		if ( !$vipsHandler ) {
			return [];
		}

		// Check if we need to convert to a .v file first
		if ( !empty( $options['preconvert'] ) ) {
			$commands[] = new VipsCommand( $wgVipsCommand, [ $vipsHandler ] );
		}

		// Do the resizing
		$rotation = 360 - $handler->getRotation( $file );

		wfDebug( __METHOD__ . " rotating '{$file->getName()}' by {$rotation}°\n" );
		if ( empty( $options['bilinear'] ) ) {
			# Calculate shrink factors. Offsetting by a small amount is required
			# because of rounding down of the target size by VIPS. See 25990#c7

			# No need to invert source and physical dimensions. They already got
			# switched if needed.

			# Use sprintf() instead of plain string conversion so that we can
			# control the precision
			$rx = sprintf( "%.18e", $params['srcWidth'] / ( $params['physicalWidth'] + 0.125 ) );
			$ry = sprintf( "%.18e", $params['srcHeight'] / ( $params['physicalHeight'] + 0.125 ) );

			wfDebug( sprintf(
				"%s to shrink '%s'. Source: %sx%s, Physical: %sx%s. Shrink factors (rx,ry) = %sx%s.\n",
				__METHOD__, $file->getName(),
				$params['srcWidth'], $params['srcHeight'],
				$params['physicalWidth'], $params['physicalHeight'],
				$rx, $ry
			) );

			$roundedRx = round( (float)$rx );
			$roundedRy = round( (float)$ry );

			if (
				floor( $params['srcWidth'] / $roundedRx ) == $params['physicalWidth']
				&& floor( $params['srcHeight'] / $roundedRy ) == $params['physicalHeight']
			) {
				// For tiff files, shrink only seems to work properly when given integer shrink factors.
				// Otherwise, in vips 7.34 it segfaults. In 7.38 it works but gives weird artifcats.
				// Docs say non-integer shrink factors give bad results (Although I can only notice a
				// difference in tiffs), so might as well round them in cases where it doesn't matter
				// for all formats.
				$rx = $roundedRx;
				$ry = $roundedRy;
				$shrinkCmd = 'shrink';
			} elseif ( $file->getMimeType() === 'image/tiff' ) {
				$shrinkCmd = 'im_shrink';
			} else {
				// For everything else, shrink works best.
				$shrinkCmd = 'shrink';
			}

			$commands[] = new VipsCommand( $wgVipsCommand, [ $shrinkCmd, $rx, $ry ] );
		} else {
			if ( $rotation % 180 == 90 ) {
				$dstWidth = $params['physicalHeight'];
				$dstHeight = $params['physicalWidth'];
			} else {
				$dstWidth = $params['physicalWidth'];
				$dstHeight = $params['physicalHeight'];
			}
			wfDebug( sprintf(
				"%s to bilinear resize %s. Source: %sx%s, Physical: %sx%s. Destination: %sx%s\n",
				__METHOD__, $file->getName(),
				$params['srcWidth'], $params['srcHeight'],
				$params['physicalWidth'], $params['physicalHeight'],
				$dstWidth, $dstHeight
			) );

			$commands[] = new VipsCommand( $wgVipsCommand,
				[ 'im_resize_linear', $dstWidth, $dstHeight ] );
		}

		if ( !empty( $options['sharpen'] ) ) {
			$options['convolution'] = self::makeSharpenMatrix( $options['sharpen'] );
		}

		if ( !empty( $options['convolution'] ) ) {
			$commands[] = new VipsConvolution( $wgVipsCommand,
				[ 'im_convf', $options['convolution'] ] );
		}

		# Rotation
		if ( $rotation % 360 != 0 && $rotation % 90 == 0 ) {
			$commands[] = new VipsCommand( $wgVipsCommand, [ "im_rot{$rotation}" ] );
		}

		// Interlace
		if ( isset( $params['interlace'] ) && $params['interlace'] ) {
			list( $major, $minor ) = File::splitMime( $file->getMimeType() );
			if ( $major == 'image' && in_array( $minor, [ 'jpeg', 'png' ] ) ) {
				$commands[] = new VipsCommand( $wgVipsCommand, [ "{$minor}save", "--interlace" ] );
			} else {
				// File type unsupported for interlacing, return empty array to cancel processing
				return [];
			}
		}

		return $commands;
	}

	/**
	 * Create a sharpening matrix suitable for im_convf. Uses the ImageMagick
	 * sharpening algorithm from SharpenImage() in magick/effect.c
	 *
	 * @param mixed $params
	 * @return array
	 */
	public static function makeSharpenMatrix( $params ) {
		$sigma = $params['sigma'];
		$radius = empty( $params['radius'] ) ?
			# After 3 sigma there should be no significant values anymore
			intval( round( $sigma * 3 ) ) : $params['radius'];

		$norm = 0;
		$conv = [];

		// Fill the matrix with a negative Gaussian distribution
		$variance = $sigma * $sigma;
		for ( $x = -$radius; $x <= $radius; $x++ ) {
			$row = [];
			for ( $y = -$radius; $y <= $radius; $y++ ) {
				$z = -exp( -( $x * $x + $y * $y ) / ( 2 * $variance ) ) /
					( 2 * pi() * $variance );
				$row[] = $z;
				$norm += $z;
			}
			$conv[] = $row;
		}

		// Calculate the scaling parameter to ensure that the mean of the
		// matrix is zero
		$scale = -$conv[$radius][$radius] - $norm;
		// Set the center pixel to obtain a sharpening matrix
		$conv[$radius][$radius] = -$norm * 2;
		// Add the matrix descriptor
		array_unshift( $conv, [ $radius * 2 + 1, $radius * 2 + 1, $scale, 0 ] );
		return $conv;
	}

	/**
	 * Check the file and params against $wgVipsOptions
	 *
	 * @param ImageHandler $handler
	 * @param File $file
	 * @param array $params
	 * @return bool|array
	 */
	protected static function getHandlerOptions( $handler, $file, $params ) {
		global $wgVipsOptions;

		if ( !isset( $params['page'] ) ) {
			$page = 1;
		} else {
			$page = $params['page'];
		}

		# Iterate over conditions
		foreach ( $wgVipsOptions as $option ) {
			if ( isset( $option['conditions'] ) ) {
				$condition = $option['conditions'];
			} else {
				# Unconditionally pass
				return $option;
			}

			if ( isset( $condition['mimeType'] ) &&
					$file->getMimeType() != $condition['mimeType'] ) {
				continue;
			}

			if ( $file->isMultipage() ) {
				$area = $file->getWidth( $page ) * $file->getHeight( $page );
			} else {
				$area = $handler->getImageArea( $file );
			}
			if ( isset( $condition['minArea'] ) && $area < $condition['minArea'] ) {
				continue;
			}
			if ( isset( $condition['maxArea'] ) && $area >= $condition['maxArea'] ) {
				continue;
			}

			$shrinkFactor = $file->getWidth( $page ) / (
				( ( $handler->getRotation( $file ) % 180 ) == 90 ) ?
				$params['physicalHeight'] : $params['physicalWidth'] );
			if ( isset( $condition['minShrinkFactor'] ) &&
					$shrinkFactor < $condition['minShrinkFactor'] ) {
				continue;
			}
			if ( isset( $condition['maxShrinkFactor'] ) &&
					$shrinkFactor >= $condition['maxShrinkFactor'] ) {
				continue;
			}

			# This condition passed
			return $option;
		}
		# All conditions failed
		return false;
	}

	/**
	 * Sets the JPEG comment on a file using exiv2.
	 * Requires $wgExiv2Command to be setup properly.
	 *
	 * @todo FIXME need to handle errors such as $wgExiv2Command not available
	 *
	 * @param string $fileName File where the comment needs to be set
	 * @param string $comment The comment
	 */
	public static function setJpegComment( $fileName, $comment ) {
		global $wgExiv2Command;

		Shell::command( $wgExiv2Command, 'mo', '-c', $comment, $fileName )
			->execute();
	}

	/**
	 * Return the appropriate im_XXX2vips handler for this file
	 * @param File $file
	 * @return mixed String or false
	 */
	public static function getVipsHandler( $file ) {
		list( $major, $minor ) = File::splitMime( $file->getMimeType() );

		if ( $major == 'image' && in_array( $minor, [ 'jpeg', 'png', 'tiff' ] ) ) {
			return "im_{$minor}2vips";
		} else {
			return false;
		}
	}

	/**
	 * Hook to BitmapHandlerCheckImageArea. Will set $result to true if the
	 * file will by handled by VipsScaler.
	 *
	 * @param File $file
	 * @param array &$params
	 * @param mixed &$result
	 * @return bool
	 */
	public static function onBitmapHandlerCheckImageArea( $file, &$params, &$result ) {
		global $wgMaxImageArea;
		/** @phan-suppress-next-line PhanTypeMismatchArgumentSuperType ImageHandler vs. MediaHandler */
		if ( self::getHandlerOptions( $file->getHandler(), $file, $params ) !== false ) {
			wfDebug( __METHOD__ . ": Overriding $wgMaxImageArea\n" );
			$result = true;
			return false;
		}
		return true;
	}
}
