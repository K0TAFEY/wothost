<?php
class ClanController extends CController
{
	public function actionIndex($id)
	{
	//	WotClan::wotLoad(array($id));
		$clan=WotClan::model()->findByPk($id);
	//	WotAccount::scan(array_keys($clan->members));
	//	WotAccount::scanTanks(array_keys($clan->members));
		foreach ($clan->members as $member){
			$member->scanTanksStat();
		}
	}
}