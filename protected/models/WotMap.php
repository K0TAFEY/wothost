<?php

class WotMap extends CActiveRecord
{
	
	/**
	 * 
	 * @return WotMap
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_map';
	}	
}