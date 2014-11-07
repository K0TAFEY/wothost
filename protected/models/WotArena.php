<?php

class WotArena extends CActiveRecord
{
	
	/**
	 * 
	 * @return WotArena
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_arena';
	}	
}