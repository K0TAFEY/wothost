<?php

class WotAchievmentOption extends CActiveRecord
{
	
	/**
	 * 
	 * @return WotAchievmentOption
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_achievment_option';
	}
	
}