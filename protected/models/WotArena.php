<?php

class WotArena extends EActiveRecord
{
	
	private static $_models;
	
	public $onDuplicate= self::DUPLICATE_UPDATE;
	
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

	public static function getArenaId($name, $i18n)
	{
		if(isset(self::$_models[$name]))
			return self::$_models[$name]->arena_id;
		if(preg_match('/(\d+)_\w+/', $name, $matches))
			$arenaId=$matches[1];
		$model=new WotArena();
		$model->arena_id=$arenaId;
		$model->arena_name=$name;
		$model->arena_i18n=$i18n;
		$model->save(false);
		self::$_models[$name]=$model;
		return $arenaId;
	}
}