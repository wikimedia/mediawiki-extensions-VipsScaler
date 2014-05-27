<?php
class VipsScalerTest extends MediaWikiMediaTestCase {

	private $handler;
	function setUp() {
		parent::setUp();
		$this->handler = new BitmapHandler;
	}

	/**
	 * @dataProvider shrinkCommandProvider
	 * @param Array $params Thumbnailing parameters
	 * @param String $type Mime type
	 * @param Array $expectedCommands
	 */
	function testShrinkCommand( $params, $type, $expectedCommands ) {
		// This file doesn't neccesarily need to actually exist
		$fakeFile = $this->dataFile( "non-existent", $type );
		$actualCommands = VipsScaler::makeCommands( $this->handler, $fakeFile, $params, array() );
		$this->assertEquals( $expectedCommands, $actualCommands );
	}

	function shrinkCommandProvider() {
		global $wgVipsCommand;
		$paramBase = array(
			'comment' => '',
			'srcWidth' => 2048,
			'srcHeight' => 1536,
			'mimeType' => 'image/tiff',
			'dstPath' => '/tmp/fake/thumb/path.jpg',
			'dstUrl' => 'path.jpg',
			'physicalWidth' => '1024',
			'physicalHeight' => '768',
			'clientWidth' => '1024',
			'clientHeight' => '768',
		);
		return array(
			array(
				$paramBase,
				'image/tiff',
				array(
					new VipsCommand( $wgVipsCommand, array( 'shrink', 2, 2 ) )
				)
			),
			array(
				$paramBase,
				'image/png',
				array(
					new VipsCommand( $wgVipsCommand, array( 'shrink', 2, 2 ) )
				)
			),
			array(
				array( 'page' => 3 ) + $paramBase,
				'image/tiff',
				array(
					new VipsCommand( $wgVipsCommand, array( 'im_shrink', $this->calcScale( 2048, 1024), $this->calcScale( 1536, 768 ) ) )
				)
			),
			array(
				array( 'physicalWidth' => 1065 ) + $paramBase,
				'image/tiff',
				array(
					new VipsCommand( $wgVipsCommand, array( 'im_shrink', $this->calcScale( 2048, 1065 ), $this->calcScale( 1536, 768 ) ) )
				)
			),
			array(
				array( 'physicalHeight' => 1065 ) + $paramBase,
				'image/tiff',
				array(
					new VipsCommand( $wgVipsCommand, array( 'im_shrink', $this->calcScale( 2048, 1024 ), $this->calcScale( 1536, 1065 ) ) )
				)
			),
			array(
				array( 'physicalWidth' => 1065, 'page' => 5 ) + $paramBase,
				'image/tiff',
				array(
					new VipsCommand( $wgVipsCommand, array( 'im_shrink', $this->calcScale( 2048, 1065 ), $this->calcScale( 1536, 768 ) ) )
				)
			),
			array(
				array( 'physicalWidth' => 1065 ) + $paramBase,
				'image/png',
				array(
					new VipsCommand( $wgVipsCommand, array( 'shrink', $this->calcScale( 2048, 1065 ), $this->calcScale( 1536, 768 ) ) )
				)
			),
		);
	}

	private function calcScale( $srcDim, $finalDim ) {
		return sprintf( "%.18e", $srcDim / ($finalDim + 0.125) );
	}
}
