<?php

class WotClanRole extends CActiveRecord
{
	
	private static $_ids;
	
	/**
	 * 
	 * @return WotClanRole
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_clan_role';
	}
	
	public function relations()
	{
		return array(
		);
	}
	
	public static function getRoleId($role, $roleI18n)
	{
		if(isset(self::$_ids[$role]))
			return self::$_ids[$role];
		
		static $command;
		if(empty($command)){
			$sql=<<<SQL
INSERT INTO wot_clan_role(role, role_i18n)
VALUE(:role, :roleI18n)
ON DUPLICATE KEY UPDATE role_id=LAST_INSERT_ID(role_id), role_i18n=:roleI18n
SQL;
			$command=Yii::app()->db->createCommand($sql);
		}
		$command->execute(compact('role','roleI18n'));
		return self::$_ids[$role]=$command->getConnection()->getLastInsertID();
	}

}