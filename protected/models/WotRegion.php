<?php

class WotRegion extends CActiveRecord
{
	
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
}