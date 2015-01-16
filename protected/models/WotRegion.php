<?php

class WotRegion extends CActiveRecord
{
	
	private static $_keys;
	
	/**
	 * 
	 * @return WotRegion
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_region';
	}

	public static function getRegionKey($regionId, $regionI18n)
	{
		if(isset(self::$_keys[$regionId]))
			return self::$_keys[$regionId];
		if(preg_match('/\w+_(\d+)/', $regionId, $matches))
			$regionKey=$matches[1];
		else
			throw new CException('Region ID invalid format');

		static $command;
		if(empty($command)){
			$sql=<<<SQL
INSERT INTO wot_region(region_key,region_id,region_i18n)
VALUES(:regionKey,:regionId,:regionI18n)
ON DUPLICATE KEY UPDATE region_id=VALUES(region_id),region_i18n=VALUES(region_i18n)
SQL;
			$command=Yii::app()->db->createCommand($sql);
		}
		$command->execute(compact('regionKey','regionId','regionI18n'));
		
		return self::$_keys[$regionId]=$regionKey;
	}
}