<?php

/**
 * Extension registration file for Special:VipsTest. The VipsScaler extension 
 * must be enabled.
 */

if ( !defined( 'MEDIAWIKI' ) ) exit( 1 );

/**
 * The remote URL which will do the scaling. Use this to send scaling to an 
 * isolated set of servers. Set this to null to do the scaling locally.
 */
$wgVipsThumbnailerUrl = null;

/**
 * The host to send the request to when doing the scaling remotely.
 */
$wgVipsThumbnailerProxy = null;

/** Registration */
$wgAutoloadClasses['SpecialVipsTest'] = "$dir/SpecialVipsTest.php";
$wgExtensionAliasesFiles['VipsTest']    = "$dir/VipsScaler.alias.php";
$wgAvailableRights[] = 'vipsscaler-test';
$wgGroupPermissions['*']['vipsscaler-test'] = true;
$wgSpecialPages['VipsTest'] = 'SpecialVipsTest';

/** 
 * Disable VipsScaler for ordinary image scaling so that the test has something
 * to compare against.
 */
$wgVipsOptions = array();

