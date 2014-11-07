<?php

class WotClanProvince extends CActiveRecord
{
	
	/**
	 * 
	 * @return WotClanProvince
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_clan_province';
	}	
}