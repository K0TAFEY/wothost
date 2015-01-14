<?php

class WotAccountTank extends CActiveRecord
{
	
	/**
	 * 
	 * @return WotAccountTank
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_account_tank';
	}
	
	public static function ensureAccountTank($account_id,$tank_id,$mark_of_mastery,$wins,$battles,$max_frags,$max_xp)
	{
		static $command;
		if(empty($command)){
			$sql=<<<SQL
INSERT INTO wot_account_tank(account_id,tank_id,mark_of_mastery,wins,battles,max_frags,max_xp)
VALUES(:account_id,:tank_id,:mark_of_mastery,:wins,:battles,:max_frags,:max_xp)
ON DUPLICATE KEY UPDATE mark_of_mastery=VALUES(mark_of_mastery),wins=VALUES(wins),battles=VALUES(battles),max_frags=VALUES(max_frags),max_xp=VALUES(max_xp)
SQL;
			$command=Yii::app()->db->createCommand($sql);
		}
		$command->execute(compact('account_id','tank_id','mark_of_mastery','wins','battles','max_frags','max_xp'));
	}
	
}