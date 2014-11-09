<?php

class WotProvinceNeighbor extends EActiveRecord
{
	
	public $onDuplicate=self::DUPLICATE_IGNORE;
	
	/**
	 * 
	 * @return WotProvinceNeighbor
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_province_neighbor';
	}	
}