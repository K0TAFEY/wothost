<?php
class TestController extends CController
{
	public function actionIndex()
	{
		echo '<pre>';
		CVarDumper::dump($_SERVER);
		CVarDumper::dump(isset($_SERVER['SUBDOMAIN']));
	//	WotProvince::scan('globalmap');
	//	phpinfo();
	}
}