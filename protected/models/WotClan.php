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

	public static function ensureClanId($clanId)
	{
		$sql=<<<SQL
INSERT IGNORE INTO wot_clan(clan_id)
VALUES($clanId)
SQL;
		Yii::app()->db->createCommand($sql)->execute();
	}
	
	/**
	 * 
	 * @param array() $clanIds
	 * @param array() $data
	 */
	public static function wotLoad($clanIds)
	{
		if(count($clanIds)>0){
			$url='http://api.worldoftanks.ru/wot/clan/info/?'.http_build_query(array(
					'application_id'=>Yii::app()->params['application_id'],
					'language'=>'ru',
					'clan_id'=>implode(',', array_keys($clanIds)),
			));
			echo $url;
			$urlHelper=new CUrlHelper();
			if($urlHelper->execute($url)){
				echo $urlHelper->content;
				$data=CJSON::decode($urlHelper->content);
				echo '<pre>';
				CVarDumper::dump($data);
				if($data['status']=='ok'){
					foreach ($data['data'] as $clanId=>$clanData){
						self::ensureClanId($clanId);
						if(is_array($clanData)){
							$clan=new WotClan();
							foreach ($clanData as $key=>$value){
								$clan->$key=$value;
							}
							$clan->setIsNewRecord(false);
							$clan->update();
							$clan->setMembers($clanData['members']);
						}						
					}
				}
			}
		}
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
ON DUPLICATE KEY UPDATE role=values(role), escaped_at=null
SQL;
	//	CVarDumper::dump($value);
		$accountIds=array();
		if(is_array($value)){
			$values=array();
			foreach ($value as $row){
				$accountIds[]=$row['account_id'];
				$values[]='('.
				implode(',', array(
					$this->clan_id,
					$row['account_id'],
					$row['created_at'],
					"'{$row['role']}'")
				).')';
			}
			//echo  implode(',', $values);
			//CVarDumper::dump($values);
			Yii::app()->db->createCommand(str_replace('{values}', implode(',', $values), $sql))->execute();
		}
		
		$accountIds=implode(',', $accountIds);
		$sql=<<<SQL
UPDATE wot_member wm SET wm.escaped_at = UNIX_TIMESTAMP()
  WHERE wm.clan_id=:clan AND wm.escaped_at IS NULL AND wm.account_id IN ($accountIds)
SQL;
		Yii::app()->db->createCommand($sql)->execute(array('clan'=>$this->clan_id));
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