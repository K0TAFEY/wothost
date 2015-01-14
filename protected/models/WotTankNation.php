<?php

class WotTankNation extends CActiveRecord
{
	
	private static $_ids;
	
	/**
	 * 
	 * @return WotTank
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_tank';
	}
	
	public static function ensureTankNation($nation, $nationI18n)
	{
		if(isset(self::$_ids[$nation]))
			return $nation;
		$sql=<<<SQL
INSERT INTO wot_tank_nation(nation,nation_i18n)
VALUES(:nation,:nationI18n)
ON DUPLICATE KEY UPDATE nation_i18n=:nationI18n;
SQL;
		Yii::app()->db->createCommand($sql)->execute(compact('nation','nationI18n'));
		return self::$_ids[$nation]=$nation;
	}	
}