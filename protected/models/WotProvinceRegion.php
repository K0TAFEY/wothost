<?php

class WotProvinceRegion extends CActiveRecord
{
	/**
	 * 
	 * @return WotProvinceRegion
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_province_region';
	}
	
	public static function addProvinceRegion($provinceKey, $regionKey)
	{
		static $command;
		if(empty($command)){
			$sql=<<<SQL
INSERT IGNORE INTO wot_province_region(province_key, region_key)
VALUES(:provinceKey, :regionKey)
SQL;
			$command=Yii::app()->db->createCommand($sql);
		}
		$command->execute(compact('provinceKey', 'regionKey'));
	}
	
}