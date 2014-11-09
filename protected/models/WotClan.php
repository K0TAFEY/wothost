<?php

class WotClan extends CActiveRecord
{
	
	public $clan_color;
	
	/**
	 * 
	 * @return WotClan
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_clan';
	}
	
	public function relations()
	{
		return array(
			'members'=>array(self::HAS_MANY, 'WotMember', 'clan_id', 'index'=>'account_id'),
		);
	}
	
	public function behaviors()
	{
		return array(
			'locale'=>'application.behaviors.CLocaleBehavior',
		);
	}
	
	protected function beforeSave()
	{
		if(parent::beforeSave()){
			$this->m_time=new CDbExpression('now()');
			return true;
		}
		else
			return false;
	}

	/**
	 * 
	 * @param integer $clanId
	 * @param array() $data
	 */
	public static function wotLoad($clanId, $data)
	{
		$clan=self::model()->findByPk($clanId);
		if(empty($clan)){
			$clan=new self();
			$clan->clan_id=$clanId;
		}
		foreach ($data as $key=>$value){
			$clan->$key=$value;
		}
		$clan->save(false);
	}
	
	public static function process()
	{
		$models=self::model()->findAll(array(
			'index'=>'clan_id',
			'limit'=>100,
			'condition'=>'t.m_time<NOW()-INTERVAL 2 DAY OR t.m_time IS NULL',
		));
		if(count($models)>0){
			$url='http://api.worldoftanks.ru/wot/clan/info/?'.http_build_query(array(
				'application_id'=>Yii::app()->params['application_id'],
				'language'=>'ru',
				'clan_id'=>implode(',', array_keys($models)),
			));
			$urlHelper=new CUrlHelper();
			if($urlHelper->execute($url)){
				$data=json_decode($urlHelper->content, true);
				if($data['status']=='ok'){
					foreach ($data['data'] as $clanId=>$clanData){
						$clan=$models[$clanId];
						if(is_array($clanData)){
							foreach ($clanData as $key=>$value){
								$clan->$key=$value;
							}
							$clan->save(false);
							$clan->setMembers($clanData['members']);
						}
						else
							$clan->save(false);
					}
				}
			}
		}
	}
	
	public function setMembers($value)
	{
	//	CVarDumper::dump($value);
		//$members=$this->members;
		$sql=<<<SQL
INSERT INTO wot_member(clan_id,account_id,created_at,role)
VALUES{values}
ON DUPLICATE KEY UPDATE created_at=values(created_at),role=values(role),updated_at=values(updated_at)
SQL;
	//	CVarDumper::dump($value);
		if(is_array($value)){
			
			$values=array();
			foreach ($value as $row){
				$values[]='('.
				implode(',', array(
					$this->clan_id,
					$row['account_id'],
					date("'Y-m-d H:i:s'",$row['created_at']),
					"'{$row['role']}'")
				).')';
			}
			//echo  implode(',', $values);
			//CVarDumper::dump($values);
			Yii::app()->db->createCommand(str_replace('{values}', implode(',', $values), $sql))->execute();
		}
	}
	
	public function setEmblems($value)
	{
		if(is_array($value)){
			foreach ($value as $key=>$emblem){
				$this->$key=$emblem;
			}
		}
	}
	
	public function setPrivate($value)
	{
		if(is_array($value)){
			CVarDumper::dump($value);
		}
	}
}