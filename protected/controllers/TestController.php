<?php
class TestController extends CController
{
	public function actionIndex()
	{
		echo '<pre>';
		CVarDumper::dump($_GET);
	//	WotProvince::scan('globalmap');
	//	phpinfo();
	}
}