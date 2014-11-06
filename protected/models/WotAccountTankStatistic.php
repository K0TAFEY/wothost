<?php

class WotAccountTankStatistic extends CActiveRecord
{
	
	/**
	 * 
	 * @return WotAccountTankStatistic
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_account_tank_statistic';
	}
	
}