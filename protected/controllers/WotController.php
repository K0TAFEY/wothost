<?php
class WotController extends CController
{
	public function actionIndex()
	{
		echo '<pre>';
		CVarDumper::dump($_SERVER);
		CVarDumper::dump(empty($_SERVER['SUBDOMAIN']));
	//	WotProvince::scan('globalmap');
	//	phpinfo();
	}
}