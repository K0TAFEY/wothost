<?php
class TestController extends CController
{
	public function actionIndex()
	{
	//	echo '<pre>';
		WotProvince::scan('globalmap');
	//	phpinfo();
	}
}