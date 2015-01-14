<?php

class WotTankType extends CActiveRecord
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
	
	public static function getTankModel($tankId)
	{
		if(empty(self::$_models)){
			self::$_models=self::model()->findAll(array('index'=>'tank_id'));
		}
		if(!isset(self::$_models[$tankId])){
			self::scan();
		}
	}
	
	public static function ensureTankType($type, $typeI18n)
	{
		if(isset(self::$_ids[$type]))
			return $type;
		$sql=<<<SQL
INSERT INTO wot_tank_type(type,type_i18n)
VALUES(:type,:typeI18n)
ON DUPLICATE KEY UPDATE type_i18n=:typeI18n;
SQL;
		Yii::app()->db->createCommand($sql)->execute(compact('type','typeI18n'));
		return self::$_ids[$type]=$type;
	}
	
}