<?php
class TestController extends CController
{
	public function actionIndex()
	{
//		echo '<pre>';
//		CVarDumper::dump($_SERVER);
//		CVarDumper::dump(empty($_SERVER['SUBDOMAIN']));
	//	WotProvince::scan('globalmap');
	//	phpinfo();
		$cookie='sessionid: 5fzxbzaaxqnwf401f8d7dg55dwuft1d7; csrftoken: Mu3EvmocTmCXQUKfhWi51tgSlp8H6pt1;';
		$helper=new CUrlHelper();
		if($helper->execute('http://worldoftanks.ru/community/accounts/5676549-K0TAFEY/',array(
				CURLOPT_HTTPHEADER=>array(
								'Cookie:'.$cookie,
								//	'User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.104 Safari/537.36',
						),
		))){
			echo $helper->content;
		}
	}
}