<?php

class ScanCommand extends CConsoleCommand
{
	public function actionIndex()
	{
		echo "OK\n";
	}
	
	public function actionProvince()
	{
		WotProvince::scan('globalmap');
	}
	
	public function actionProvinceClan()
	{
		WotProvinceClan::scan('globalmap');
	}
	
}