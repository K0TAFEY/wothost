<?php

class TsCommand extends CConsoleCommand
{
	public function actionIndex()
	{
		Yii::import('ext.teamspeak.libraries.TeamSpeak3.*',true);//cFsOcmiR
		// connect to local server, authenticate and spawn an object for the virtual server on port 9987
		$ts3 = TeamSpeak3::factory(Yii::app()->params['tsUri']);
		$clientList = $ts3->clientList();
		
		foreach ($clientList as $client){
			if(((string)$client['client_platform'])!='ServerQuery'){
				$info =$client->getInfo();
		
				CVarDumper::dump($info);
			}
		}
	}
}