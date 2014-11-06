<?php

class WotAchievment extends CActiveRecord
{
	
	/**
	 * 
	 * @return WotAchievment
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_achievment';
	}
	
}