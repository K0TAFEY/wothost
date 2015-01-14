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
	
	public function scanTanksStat()
	{
		$url='http://api.worldoftanks.ru/wot/tanks/stats/?'.http_build_query(array(
				'application_id'=>Yii::app()->params['application_id'],
				'language'=>'ru',
				'account_id'=>$this->account_id,
				'tank_id'=>implode(',', WotTank::get10lIds()),
				'fields'=>'account_id,max_frags,max_xp,tank_id,all,clan',
		));
		$urlHelper=new CUrlHelper();
		if($urlHelper->execute($url)){
			$data=CJSON::decode($urlHelper->content);
			if($data['status']=='ok'){
				if(isset($data['data'][$this->account_id])){
					$statData=$data['data'][$this->account_id];
					$sql='INSERT INTO wothost.wot_account_tank_statistic(account_id,tank_id,statistic,battle_avg_xp,battles,capture_points,damage_dealt,damage_received,draws,dropped_capture_points,frags,hits,hits_percents,losses,shots,spotted,survived_battles,wins,xp)';
					$sql.='VALUES(';
					$values=array();
					foreach ($statData as $tankStat){
						WotAccountTank::model()->updateByPk(array('account_id'=>$tankStat['account_id'],'tank_id'=>$tankStat['tank_id']), array(
							'max_frags'=>$tankStat['max_frags'],
							'max_xp'=>$tankStat['max_xp'],
						));					
						foreach (array('all','clan') as $statistic){
							if(isset($tankStat[$statistic])){
								$statRow=$tankStat[$statistic];
								$values[]=implode(',', array(
										$tankStat['account_id'],
										$tankStat['tank_id'],
										"'$statistic'",
										$statRow['battle_avg_xp'],
										$statRow['battles'],
										$statRow['capture_points'],
										$statRow['damage_dealt'],
										$statRow['damage_received'],
										$statRow['draws'],
										$statRow['dropped_capture_points'],
										$statRow['frags'],
										$statRow['hits'],
										$statRow['hits_percents'],
										$statRow['losses'],
										$statRow['shots'],
										$statRow['spotted'],
										$statRow['survived_battles'],
										$statRow['wins'],
										$statRow['xp'],
								));
							}
						}
					}
					$sql.=implode(').(', $values).')';
					$sql.='ON DUPLICATE KEY UPDATE battle_avg_xp=VALUES(battle_avg_xp),battles=VALUES(battles),capture_points=VALUES(capture_points),damage_dealt=VALUES(damage_dealt),damage_received=VALUES(damage_received),draws=VALUES(draws),dropped_capture_points=VALUES(dropped_capture_points),frags=VALUES(frags),hits=VALUES(hits),hits_percents=VALUES(hits_percents),losses=VALUES(losses),shots=VALUES(shots),spotted=VALUES(spotted),survived_battles=VALUES(survived_battles),wins=VALUES(wins),xp=VALUES(xp)';
				}
			}
		}
		else
			throw new CException($urlHelper->errorMessage);
	}
}