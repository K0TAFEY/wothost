<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.

$config=require(dirname(__FILE__).'/common.php');

$config=CMap::mergeArray($config,array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Console Application',
));
return $config;