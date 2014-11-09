<?php

class WotProvinceRegion extends EActiveRecord
{
	
	public $onDuplicate=self::DUPLICATE_IGNORE;
	
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
}