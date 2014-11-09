<?php

class WotClanProvince extends EActiveRecord
{
	
	public $onDuplicate=self::DUPLICATE_IGNORE;
	
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
	
	public function behaviors()
	{
		return array(
			'locale'=>'application.behaviors.CLocaleBehavior'
		);
	} 
}