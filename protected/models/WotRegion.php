<?php

class WotRegion extends EActiveRecord
{
	
	private static $_models;
	
	public $onDuplicate = self::DUPLICATE_UPDATE;
	
	/**
	 * 
	 * @return WotRegion
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_region';
	}

	public static function getRegionId($name, $i18n)
	{
		if(isset(self::$_models[$name]))
			return self::$_models[$name]->region_id;
		if(preg_match('/\w+_(\d+)/', $name, $matches))
			$regionId=$matches[1];
		else
			throw new CException('Region ID invalid format');
		$model=new self();
		$model->region_id=$regionId;
		$model->region_name=$name;
		$model->region_i18n=$i18n;
		$model->save(false);
		self::$_models[$name]=$model;
		return $regionId;
	}
}