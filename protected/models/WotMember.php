<?php

class WotMember extends CActiveRecord
{
	
	/**
	 * 
	 * @return WotMember
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_member';
	}
	
	public static function wotLoad($data)
	{
		$member=self::model()->findByPk(array('clan_id'=>$data['clan_id'], 'account_id'=>$data['account_id']));
		if(empty($member)){
			$member=new self();
		}
		foreach ($data as $key=>$value){
			$member->$key=$value;
		}
		$member->save(false);
	}
	
	public function behaviors()
	{
		return array(
			'timestamp'=>'application.behaviors.CTimestampBehavior',
		);
	}
}