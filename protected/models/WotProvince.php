<?php

class WotProvince extends CActiveRecord
{
	
	/**
	 * 
	 * @return WotProvince
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_province';
	}	
}