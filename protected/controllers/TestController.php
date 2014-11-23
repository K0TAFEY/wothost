<?php
class TestController extends CController
{
	public function actionIndex()
	{
		echo '<pre>';
		CVarDumper::dump($_SERVER);
	//	WotProvince::scan('globalmap');
	//	phpinfo();
	}
}