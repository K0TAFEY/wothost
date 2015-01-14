<?php
class ClanController extends CController
{
	public function actionIndex($id)
	{
	//	WotClan::wotLoad(array($id));
		$clan=WotClan::model()->findByPk($id);
		$accountIds=array_keys($clan->members);
		WotAccount::scan($accountIds);
		WotAccount::scanTanks($accountIds);
		WotAccount::calcEffect($accountIds);
		foreach ($clan->accounts as $account){
			$account->scanTanksStat();
		}
	}
}