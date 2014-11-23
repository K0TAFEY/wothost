<?php

$config=require(dirname(__FILE__).'/main.php');

$config=CMap::mergeArray($config,array(
	'name'=>'Yii Demo',

	'defaultController'=>'test',

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'post/<id:\d+>/<title:.*?>'=>'post/view',
				'posts/<tag:.*?>'=>'post/index',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				// uncomment the following to show log messages on web pages
				array(
					'class'=>'CWebLogRoute',
					// 'levels'=>'trace',
				),
			),
		),
	),
));

return $config;