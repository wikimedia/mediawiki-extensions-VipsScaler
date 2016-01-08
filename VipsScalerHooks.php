<?php

class VipsScalerHooks {
	/**
	* @param $files array
	* @return bool
	*/
	public static function onUnitTestsList ( &$files ) {
		$files[] = __DIR__ . '/tests/VipsScalerTest.php';
		return true;
	}
}
