<?php

class WotAccountTank extends CActiveRecord
{
	
	/**
	 * 
	 * @return WotAccountTank
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_account_tank';
	}
	
}