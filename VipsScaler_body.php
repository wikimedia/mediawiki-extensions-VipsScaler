<?php
class VipsScaler {
	/**
	 * Hook to BitmapHandlerTransform. Transforms the file using VIPS if it
	 * matches a condition in $wgVipsConditions
	 * 
	 * @param BitmapHandler $handler
	 * @param File $file
	 * @param array $params
	 * @param MediaTransformOutput $mto
	 */
	public static function onTransform( $handler, $file, &$params, &$mto ) {
		# Check $wgVipsConditions
		if ( !self::shouldHandle( $handler, $file, $params ) ) {
			return true;
		} 
		
		# Get the proper im_XXX2vips handler
		$vipsHandler = self::getVipsHandler( $file );
		if ( !$vipsHandler ) {
			return true;
		}
		
		# Get a temporary file with .v extension
		$tmp = self::makeTempV();
		# Convert the source image to a .v file
		list( $err, $retval ) = self::vips( $vipsHandler, $params['srcPath'], $tmp );
		if ( $retval != 0 ) {
			wfDebug( __METHOD__ . ": $vipsHandler failed!\n" );
			unlink( $tmp );
			$mto = $handler->getMediaTransformError( $params, $err );
			return false;
		}
		
		# Rotate if necessary
		$rotation = 360 - $handler->getRotation( $file );
		if ( $rotation % 360 != 0 && $rotation % 90 == 0 ) {
			$tmp2 = self::makeTempV();
			
			list( $err, $retval ) = self::vips( "im_rot{$rotation}", $tmp, $tmp2 );
			unlink( $tmp );
			if ( $retval != 0 ) {
				unlink( $tmp2 );
				$mto = $handler->getMediaTransformError( $params, $err );
				return false;
			}
			
			# Set the new rotated file
			$tmp = $tmp2;
		}
		
		# Scale the image to the final output
		list( $err, $retval ) = self::vips( 'im_resize_linear', $tmp, 
			$params['dstPath'], $params['physicalWidth'], $params['physicalHeight'] );
		# Remove the temp file
		unlink( $tmp );
		if ( $retval != 0 ) {
			$mto = $handler->getMediaTransformError( $params, $err );
			return false;
		}
				
		# Set the output variable
		$mto = new ThumbnailImage( $file, $params['dstUrl'], 
			$params['clientWidth'], $params['clientHeight'], $params['dstPath'] );
			
		# Stop processing
		return false;
		
	}
	
	/**
	 * Call the vips binary with varargs and returns the error output and 
	 * return value.
	 * 
	 * @param varargs
	 * @return array
	 */
	protected static function vips( /* varargs */ ) {
		global $wgVipsCommand;
		
		$args = func_get_args();
		$cmd = wfEscapeShellArg( $wgVipsCommand );
		foreach ( $args as $arg ) {
			$cmd .= ' ' . wfEscapeShellArg( $arg );
		}
		
		$retval = 0;
		$err = wfShellExec( $cmd, $retval );
		return array( $err, $retval );
	}
	
	/**
	 * Check the file and params against $wgVipsConditions
	 * 
	 * @param BitmapHandler $handler
	 * @param File $file
	 * @param array $params
	 * @return bool
	 */
	protected static function shouldHandle( $handler, $file, $params ) {
		global $wgVipsConditions;
		# Iterate over conditions
		foreach ( $wgVipsConditions as $condition ) {
			if ( isset( $condition['mimeType'] ) && 
					$file->getMimeType() != $condition['mimeType'] ) {
				continue;						
			}
			$area = $handler->getImageArea( $file, $params['srcWidth'], $params['srcHeight'] );
			if ( isset( $condition['minArea'] ) && $area < $condition['minArea'] ) {
				continue;
			}
			if ( isset( $condition['maxArea'] ) && $area > $condition['maxArea'] ) {
				continue;
			}

			# This condition passed
			return true;
		}
		# All conditions failed
		return false;
	}
	
	/**
	 * Return the appropriate im_XXX2vips handler for this file
	 * @param File $file
	 * @return mixed String or false
	 */
	protected static function getVipsHandler( $file ) {
		list( $major, $minor ) = File::splitMime( $file->getMimeType() );

		if ( $major == 'image' && in_array( $minor, array( 'jpeg', 'png', 'tiff' ) ) ) {
			return "im_{$minor}2vips";
		} else {
			return false;
		}
	}
	
	/**
	 * Generate a random, non-existent temporary file with a .v extension.
	 * 
	 * @return string
	 */
	protected static function makeTempV() {
		do {
			# Generate a random file
			$fileName = wfTempDir() . DIRECTORY_SEPARATOR . 
				dechex( mt_rand() ) . dechex( mt_rand() ) . '.v';
		} while ( file_exists( $fileName ) );
		# Create the file
		touch( $fileName );
		
		return $fileName;
	}
}