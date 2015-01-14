<?php

class WotAccount extends CActiveRecord
{
	
	public $clan_id;
	
	/**
	 * 
	 * @return WotAccount
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_account';
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
	
	public static function ensureAccountId($accountId)
	{
		static $command;
		if(empty($command)){
			$sql=<<<SQL
INSERT IGNORE INTO wot_account(account_id)
VALUES(:accountId)
SQL;
			$command=Yii::app()->db->createCommand($sql);
		}
		$command->execute(compact('accountId'));
	}
	
	public static function scan($accountIds)
	{
		if(count($accountIds)>0){
			$url='http://api.worldoftanks.ru/wot/account/info/?'.http_build_query(array(
					'application_id'=>Yii::app()->params['application_id'],
					'language'=>'ru',
					'fields'=>'account_id,clan_id,created_at,global_rating,last_battle_time,logout_at,nickname,updated_at,statistics',
					'account_id'=>implode(',', $accountIds),
			));
			$urlHelper=new CUrlHelper();
			if($urlHelper->execute($url)){
				$data=CJSON::decode($urlHelper->content);
			//	CVarDumper::dump($data);
				if($data['status']=='ok'){
					$tran=Yii::app()->db->beginTransaction();
					foreach ($data['data'] as $accountId=>$accountData){
						WotAccount::ensureAccountId($accountId);
						if(is_array($accountData)){
							$accout=new WotAccount();
							$accout->setIsNewRecord(false);
							foreach ($accountData as $key=>$value){
								$accout->$key=$value;
							}
							$accout->update();
						}
					}
					$tran->commit();
				}
			}
			else
				throw new CException($urlHelper->errorMessage);		
		}
	}
	
	public function setStatistics($value)
	{
		$this->max_damage=$value['max_damage'];
		$this->max_damage_vehicle=$value['max_damage_vehicle'];
		$this->max_xp=$value['max_xp'];
		
		$fields=array_keys(WotStatistic::model()->metaData->columns);
		$sql='INSERT INTO wot_statistic('.implode(',', $fields).') VALUES';
		$rows=array();
		foreach (array('all','clan') as $stat){ //,'company','historical', 'team'
			if(isset($value[$stat])){
				$data=$value[$stat];
				$values=array();
				foreach ($fields as $field){
					if($field=='statistic')
						$values[]="'$stat'";
					elseif($field=='account_id')
						$values[]=$this->account_id;
					elseif(isset($data[$field])){
						$values[]=$data[$field];
					}
					else
						$values[]=0;
				}
				$rows[]=implode(',', $values);
			}
		}
		if(count($rows)>0)
			$sql.='('.implode('),(', $rows). ')';
		else
			return;
		$sql.='ON DUPLICATE KEY UPDATE ';
		$update=array();
		foreach ($fields as $field){
			$update[]=$field.'=VALUES('.$field.')';
		}
		$sql.=implode(',', $update);
		Yii::app()->db->createCommand($sql)->execute();
	}
	
	public static function scanTanks($accountIds)
	{
		if(count($accountIds)>0){
			$url='http://api.worldoftanks.ru/wot/account/tanks/?'.http_build_query(array(
					'application_id'=>Yii::app()->params['application_id'],
					'language'=>'ru',
					'account_id'=>implode(',', $accountIds),
			));
			$urlHelper=new CUrlHelper();
			if($urlHelper->execute($url)){
				$data=json_decode($urlHelper->content, true);
				if($data['status']=='ok'){
					$sql='INSERT INTO wot_account_tank(account_id,tank_id,mark_of_mastery,wins,battles)VALUES(';
					$values=array();
					foreach ($data['data'] as $accountId=>$accountData){
						if(is_array($accountData)){
							foreach ($accountData as $tankData){
								$values[]=implode(',', array(
										$accountId,
										WotTank::ensureTankId($tankData['tank_id']),
										$tankData['mark_of_mastery'],
										$tankData['statistics']['wins'],
										$tankData['statistics']['battles'],
								));
							}
						}
					}
					$sql.=implode('),(', $values).')';
					$sql.='ON DUPLICATE KEY UPDATE mark_of_mastery=VALUES(mark_of_mastery),wins=VALUES(wins),battles=VALUES(battles)';
					Yii::app()->db->createCommand($sql)->execute();
				}
			}
			else
				throw new CException($urlHelper->errorMessage);
		}
	}
	
	public static function scanStat()
	{
		$fieldNames=array_keys(WotAccountTankStatistic::model()->metaData->columns);
		
		$sqlInsert="INSERT INTO wot_account_tank_statistic(".implode(',', $fieldNames).")VALUES";
		
		$sqlQuery=<<<SQL
SELECT wat.account_id, GROUP_CONCAT(wat.tank_id) tank_id
  FROM wot_account_tank wat
  JOIN wot_tank wt ON wat.tank_id = wt.tank_id AND wt.level=10
  JOIN wot_account wa ON wat.account_id = wa.account_id
  JOIN wot_statistic ws ON wat.account_id = ws.account_id AND ws.statistic='clan'
  WHERE (wa.t_time IS NULL OR wa.t_time<NOW()-INTERVAL 1 WEEK) AND wa.last_battle_time>NOW()-INTERVAL 1 WEEK 
  GROUP BY wa.account_id HAVING SUM(wat.battles)>10
  LIMIT 100
SQL;
		$statRows=array();
		$rows=Yii::app()->db->createCommand($sqlQuery)->queryAll();
	//	CVarDumper::dump($rows);
		foreach ($rows as $row){
			$account=WotAccount::model()->updateByPk($row['account_id'], array('t_time'=>new CDbExpression('now()')));
			$url='http://api.worldoftanks.ru/wot/tanks/stats/?'.http_build_query(array(
					'application_id'=>Yii::app()->params['application_id'],
					'language'=>'ru',
					'account_id'=>$row['account_id'],
					'tank_id'=>$row['tank_id'],
			));
			$urlHelper=new CUrlHelper();
			if($urlHelper->execute($url)){
				$data=json_decode($urlHelper->content, true);
				if($data['status']=='ok'){
					if(isset($data['data'][$row['account_id']])){
						$statData=$data['data'][$row['account_id']];
						foreach ($statData as $tankStat){						
							foreach (array('all','clan', 'company', 'historical') as $statistic){
								if(isset($tankStat[$statistic])){
									$statRow=$tankStat[$statistic];
									$statRow['account_id']=$tankStat['account_id'];
									$statRow['tank_id']=$tankStat['tank_id'];
									$statRow['statistic']="'$statistic'";
									$statRows[]='('.implode(',', CMap::mergeArray(array_flip($fieldNames), $statRow)).')';
								}
							}
						}
					}
				}
			}
			else
				throw new CException($urlHelper->errorMessage);
		}
		if(count($statRows)>0){
			$sqlInsert.=implode(',', $statRows);
			$fieldNames=array_diff($fieldNames, array('account_id','tank_id','statistic'));
			$updates=array();
			foreach ($fieldNames as $fieldName){
				$updates[]=$fieldName.'=VALUES('.$fieldName.')';
			}
			$sqlInsert.="ON DUPLICATE KEY UPDATE ".implode(',', $updates);
			Yii::app()->db->createCommand($sqlInsert)->execute();
		}
	}
}