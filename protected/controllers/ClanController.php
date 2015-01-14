<?php
class ClanController extends CController
{
	public function actionIndex($id)
	{
		WotClan::wotLoad(array($id));
	}
}