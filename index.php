<?php

// change the following paths if necessary
$yii=dirname(__FILE__).'/../../framework/yii.php';
if(empty($_SERVER['SUBDOMAIN']))
	$config=dirname(__FILE__).'/protected/config/main.php';
else
	$config=dirname(__FILE__).'/protected/config/subdomain.php';
// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);

require_once($yii);
Yii::createWebApplication($config)->run();
