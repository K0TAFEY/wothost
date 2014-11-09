<?php

class ScanCommand extends CConsoleCommand
{
	public function actionIndex()
	{
		WotProvince::scan('globalmap');
	}
}