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
}