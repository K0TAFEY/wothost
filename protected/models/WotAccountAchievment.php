<?php

class WotAccountAchievment extends CActiveRecord
{
	
	/**
	 * 
	 * @return WotAccountAchievment
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_account_achievment';
	}
	
}