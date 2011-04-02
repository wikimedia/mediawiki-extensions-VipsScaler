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
		$options = self::getHandlerOptions( $handler, $file, $params );
		if ( !$options ) {
			return true;
		} 
		
		$vipsCommands = self::makeCommands( $handler, $file, $params, $options );
		if ( count( $vipsCommands ) == 0 ) {
			return true;
		}
		
		# Execute the commands
		foreach ( $vipsCommands as $i => $command ) { 
			# Set input/output files
			if ( $i == 0 && count( $vipsCommands ) == 1 ) {
				# Single command, so output directly to dstPath
				$command->setIO( $params['srcPath'], $params['dstPath'] );
			} elseif ( $i == 0 ) {
				# First command, input from srcPath, output to temp
				$command->setIO( $params['srcPath'], 'v', VipsCommand::TEMP_OUTPUT );
			} elseif ( $i + 1 == count( $vipsCommands ) ) {
				# Last command, output to dstPath
				$command->setIO( $vipsCommands[$i - 1], $params['dstPath'] );
			} else {
				$command->setIO( $vipsCommands[$i - 1], 'v', VipsCommand::TEMP_OUTPUT );
			}
			
			$retval = $command->execute();
			if ( $retval != 0 ) {
				wfDebug( __METHOD__ . ": vips command failed!\n" );
				$mto = $handler->getMediaTransformError( $params, $command->getErrorString() );
				return false;
			}
		}
		
		# Set the output variable
		$mto = new ThumbnailImage( $file, $params['dstUrl'], 
			$params['clientWidth'], $params['clientHeight'], $params['dstPath'] );
			
		# Stop processing
		return false;
	}
	
	public static function makeCommands( $handler, $file, $params, $options ) {
		global $wgVipsCommand;
		$commands = array();
		
		# Get the proper im_XXX2vips handler
		$vipsHandler = self::getVipsHandler( $file );
		if ( !$vipsHandler ) {
			return array();
		}
		
		# Check if we need to convert to a .v file first
		if ( !empty( $options['preconvert'] ) ) {
			$commands[] = new VipsCommand( $wgVipsCommand, array( $vipsHandler ) );
		}
		
		# Do the resizing
		$rotation = 360 - $handler->getRotation( $file );
		
		if ( empty( $options['bilinear'] ) ) {
			# Calculate shrink factors. Offsetting by 0.5 pixels is required 
			# because of rounding down of the target size by VIPS. See 25990#c7 
			if ( $rotation % 180 == 90 ) {
				# Rotated 90 degrees, so width = height and vice versa
				$rx = $params['srcWidth'] / ($params['physicalHeight'] + 0.5);
				$ry = $params['srcHeight'] / ($params['physicalWidth'] + 0.5);
			} else {
				$rx = $params['srcWidth'] / ($params['physicalWidth'] + 0.5);
				$ry = $params['srcHeight'] / ($params['physicalHeight'] + 0.5);
			}

			$commands[] = new VipsCommand( $wgVipsCommand, array( 'im_shrink', $rx, $ry ) );
		} else {
			if ( $rotation % 180 == 90 ) {
				$dstWidth = $params['physicalHeight'];
				$dstHeight = $params['physicalWidth'];
			} else {
				$dstWidth = $params['physicalWidth'];
				$dstHeight = $params['physicalHeight'];
			}
			$commands[] = new VipsCommand( $wgVipsCommand, 
				array( 'im_resize_linear', $dstWidth, $dstHeight ) );
		}
		
		if ( !empty( $options['sharpen'] ) ) {
			# Use a Laplacian-of-Gaussian convolution matrix with sigma=0.4
			# See http://homepages.inf.ed.ac.uk/rbf/HIPR2/unsharp.htm for
			# an explanation on how to use the Laplacian operator to 
			# sharpen an image using a convolution matrix
			/*
			# Integer version of the convolution matrix below. Can be used 
			# if im_conv instead of im_convf is prefered.
			$convolution = array(
				# Matrix descriptor
				array( 3, 3, 8, 0 ),
				# Matrix itself
				array( 0, -1, 0 ),
				array( -1, 12, -1 ),
				array( 0, -1, 0 )
			);
			*/
			$options['convolution'] = array(
				array( 3, 3, 7.2863, 0 ),
				array( -0.1260, -1.1609, -0.1260 ),
				array( -1.1609, 12.4340, -1.1609 ),
				array( -0.1260, -1.1609, -0.1260 ),
			);
			
		}

		if ( !empty( $options['convolution'] ) ) {
			$commands[] = new VipsConvolution( $wgVipsCommand, 
				array( 'im_convf', $options['convolution'] ) );
		}
		
		# Rotation
		if ( $rotation % 360 != 0 && $rotation % 90 == 0 ) {
			$commands[] = new VipsCommand( $wgVipsCommand, array( "im_rot{$rotation}" ) );
		}
		
		return $commands;
	}
	
	
	/**
	 * Check the file and params against $wgVipsOptions
	 * 
	 * @param BitmapHandler $handler
	 * @param File $file
	 * @param array $params
	 * @return bool
	 */
	protected static function getHandlerOptions( $handler, $file, $params ) {
		global $wgVipsOptions;
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
			
			$area = $handler->getImageArea( $file, $params['srcWidth'], $params['srcHeight'] );
			if ( isset( $condition['minArea'] ) && $area < $condition['minArea'] ) {
				continue;
			}
			if ( isset( $condition['maxArea'] ) && $area >= $condition['maxArea'] ) {
				continue;
			}
			
			$shrinkFactor = $params['srcWidth'] / ( 
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
	

}

/**
 * Wrapper class around the vips command, useful to chain multiple commands 
 * with intermediate .v files
 */
class VipsCommand {
	/** Flag to indicate that the output file should be a temporary .v file */ 
	const TEMP_OUTPUT = true;
	/** 
	 * Constructor
	 * 
	 * @param string $vips Path to binary
	 * @param array $args Array or arguments
	 */
	public function __construct( $vips, $args ) {
		$this->vips = $vips;
		$this->args = $args;
	}
	/**
	 * Set the input and output file of this command
	 * 
	 * @param mixed $input Input file name or an VipsCommand object to use the
	 * output of that command
	 * @param string $output Output file name or extension of the temporary file
	 * @param bool $tempOutput Output to a temporary file
	 */
	public function setIO( $input, $output, $tempOutput = false ) {
		if ( $input instanceof VipsCommand ) {
			$this->input = $input->getOutput();
			$this->removeInput = true;
		} else {
			$this->input = $input;
			$this->removeInput = false;
		}
		if ( $tempOutput ) {
			$this->output = self::makeTemp( $output );
		} else {
			$this->output = $output;
		}
	}
	/**
	 * Returns the output filename
	 * @return string
	 */
	public function getOutput() {
		return $this->output;
	}
	/**
	 * Return the output of the command
	 * @return string
	 */
	public function getErrorString() {
		return $this->err;
	}
	
	/**
	 * Call the vips binary with varargs and returns the return value.
	 * 
	 * @return int Return value
	 */
	public function execute() {
		# Build and escape the command string
		$cmd = wfEscapeShellArg( $this->vips, 
			array_shift( $this->args ),
			$this->input, $this->output );
		
		foreach ( $this->args as $arg ) {
			$cmd .= ' ' . wfEscapeShellArg( $arg );
		}
		
		$cmd .= ' 2>&1';
		
		# Execute
		$retval = 0;
		$this->err = wfShellExec( $cmd, $retval );
		
		# Cleanup temp file
		if ( $this->removeInput ) {
			unlink( $this->input );
		}
		
		return $retval;		
	}
	
	/**
	 * Generate a random, non-existent temporary file with a specified 
	 * extension.
	 * 
	 * @param string Extension
	 * @return string
	 */
	protected static function makeTemp( $extension ) {
		do {
			# Generate a random file
			$fileName = wfTempDir() . DIRECTORY_SEPARATOR . 
				dechex( mt_rand() ) . dechex( mt_rand() ) . 
				'.' . $extension;
		} while ( file_exists( $fileName ) );
		# Create the file
		touch( $fileName );
		
		return $fileName;
	}
	
}

/**
 * A wrapper class around im_conv because that command expects a a convolution
 * matrix file as its last argument
 */
class VipsConvolution extends VipsCommand {
	public function execute() {
		# Convert a 2D array into a space/newline separated matrix
		$convolutionMatrix = array_pop( $this->args );
		$convolutionString = '';
		foreach ( $convolutionMatrix as $row ) {
			$convolutionString .= implode( ' ', $row ) . "\n";
		}
		# Save the matrix in a tempfile
		$convolutionFile = self::makeTemp( 'conv' );
		file_put_contents( $convolutionFile, $convolutionString );
		array_push( $this->args, $convolutionFile );
		
		# Call the parent to actually execute the command
		$retval = parent::execute();
		
		# Remove the temporary matrix file
		unlink( $convolutionFile );
		
		return $retval;
	}
}