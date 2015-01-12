<?php
return array(
		// application components
		'components'=>array(
				'db'=>array(
						'connectionString' => 'mysql:host=localhost;port=3306;dbname=DATABASE',
						'username' => 'USERNAME',
						'password' => 'PASSWORD',
						'charset' => 'utf8',
						'tablePrefix' => 'tbl_',
						'emulatePrepare' => true,
				),
		),
		'params'=>array(
				'tsUri'=>"serverquery://USERNAME:PASSWORD@127.0.0.1:10011/?server_port=9987",
				'application_id'=>'GO AND GET IT!!! https://ru.wargaming.net/developers/',
		),
);
