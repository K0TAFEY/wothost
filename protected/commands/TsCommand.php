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
	
	public function actionCreate()
	{
		Yii::import('ext.teamspeak.libraries.TeamSpeak3.*',true);//cFsOcmiR
		// connect to local server, authenticate and spawn an object for the virtual server on port 9987
		$ts3 = TeamSpeak3::factory(Yii::app()->params['tsUri']);
		$new_sid = $ts3->serverCreate(array(
			"virtualserver_name"               => "My TeamSpeak 3 Server!!!!",
			"virtualserver_maxclients"         => 128,
			"virtualserver_hostbutton_tooltip" => "dpx_",
			"virtualserver_hostbutton_url"     => "http://dpx_.wothost.ru",
			"virtualserver_hostbutton_gfx_url" => "http://clans.worldoftanks.ru/media/clans/emblems/cl_535/93535/emblem_24x24.png",
		));
	}
	
}