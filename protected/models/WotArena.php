<?php

class WotArena extends CActiveRecord
{
	
	private static $_keys;
	
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

	public static function getArenaKey($arenaId, $arenaI18n)
	{
		if(isset(self::$_keys[$arenaId]))
			return self::$_keys[$arenaId];
		if(preg_match('/(\d+)_\w+/', $arenaId, $matches))
			$arenaKey=$matches[1];
		static $command;
		if(empty($command)){
			$sql=<<<SQL
INSERT INTO wot_arena(arena_key,arena_id,arena_i18n)
VALUES(:arenaKey,:arenaId,:arenaI18n)
ON DUPLICATE KEY UPDATE arena_id=VALUES(arena_id),arena_i18n=VALUES(arena_i18n)
SQL;
			$command=Yii::app()->db->createCommand($sql);
		}
		$command->execute(compact('arenaKey','arenaId','arenaI18n'));
		return self::$_keys[$arenaId]=$arenaKey;
	}
}