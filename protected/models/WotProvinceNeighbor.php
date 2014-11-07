<?php

class WotProvinceNeighbor extends CActiveRecord
{
	
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