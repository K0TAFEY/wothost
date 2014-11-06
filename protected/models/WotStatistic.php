<?php

class WotStatistic extends CActiveRecord
{
	
	/**
	 * 
	 * @return WotStatistic
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_statistic';
	}
	
}