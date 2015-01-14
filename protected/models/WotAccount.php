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
				'fields'=>'account_id,max_frags,max_xp,tank_id,all,clan,mark_of_mastery',
		));
		$urlHelper=new CUrlHelper();
		if($urlHelper->execute($url)){
			$data=CJSON::decode($urlHelper->content);
			if($data['status']=='ok'){
				if(isset($data['data'][$this->account_id])){
					$statData=$data['data'][$this->account_id];
					$sql='INSERT INTO wot_account_tank_statistic(account_id,tank_id,statistic,battle_avg_xp,battles,capture_points,damage_dealt,damage_received,draws,dropped_capture_points,frags,hits,hits_percents,losses,shots,spotted,survived_battles,wins,xp)';
					$sql.='VALUES(';
					$values=array();
					$tran=Yii::app()->db->beginTransaction();
					foreach ($statData as $tankStat){
						WotAccountTank::ensureAccountTank(
							$tankStat['account_id'],
							$tankStat['tank_id'],
							$tankStat['mark_of_mastery'],
							$tankStat['all']['wins'],
							$tankStat['all']['battles'],
							$tankStat['max_frags'],
							$tankStat['max_xp']
						);					
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
					$tran->commit();
					$sql.=implode('),(', $values).')';
					$sql.='ON DUPLICATE KEY UPDATE battle_avg_xp=VALUES(battle_avg_xp),battles=VALUES(battles),capture_points=VALUES(capture_points),damage_dealt=VALUES(damage_dealt),damage_received=VALUES(damage_received),draws=VALUES(draws),dropped_capture_points=VALUES(dropped_capture_points),frags=VALUES(frags),hits=VALUES(hits),hits_percents=VALUES(hits_percents),losses=VALUES(losses),shots=VALUES(shots),spotted=VALUES(spotted),survived_battles=VALUES(survived_battles),wins=VALUES(wins),xp=VALUES(xp)';
					Yii::app()->db->createCommand($sql)->execute();
				}
			}
		}
		else
			throw new CException($urlHelper->errorMessage);
	}
	
	public static function calcEffect($accountIds)
	{
		if(count($accountIds)>0){
			$accountIds=implode(',', $accountIds);
			$sql=<<<SQL
UPDATE wot_account wa
  JOIN 
(SELECT 
  ws.account_id, 
  (1240-1040/POWER(LEAST(a.midl,6),0.164))*ws.frags/ws.battles
  +ws.damage_dealt/ws.battles*530/(184*EXP(0.24*a.midl)+130)
  +ws.spotted/ws.battles*125
  +LEAST(ws.dropped_capture_points/ws.battles,2.2)*100
  +((185/(0.17+EXP((ws.wins/ws.battles*100-35)*-0.134)))-500)*0.45
  +(6-LEAST(a.midl,6))*-60 wn6,

  (1240-1040/POWER(LEAST(a.midl,6),0.164))*ws.frags/ws.battles
  +ws.damage_dealt/ws.battles*530/(184*EXP(0.24*a.midl)+130)
  +ws.spotted/ws.battles*125*LEAST(a.midl,3)/3
  +LEAST(ws.dropped_capture_points/ws.battles,2.2)*100
  +((185/(0.17+EXP((ws.wins/ws.battles*100-35)*-0.134)))-500)*0.45
  -((5-LEAST(a.midl,5))*125)/(1+EXP((a.midl-POWER(ws.battles/220,3/a.midl))*1.5)) wn7,
       
  ws.damage_dealt/ws.battles*(10/(a.midl+2))*(0.23+2*a.midl/100)
  +250*ws.frags/ws.battles
  +ws.spotted/ws.battles*150
  +log(1.732,ws.capture_points/ws.battles+1)*150
  +ws.dropped_capture_points/ws.battles*150 effect,
  
  980*a.rDAMAGEc + 210*a.rDAMAGEc*a.rFRAGc + 155*a.rFRAGc*a.rSPOTc + 75*a.rDEFc*a.rFRAGc + 145*LEAST(1.8,a.rWINc) wn8,

  LN(ws.battles)/10*(ws.xp/ws.battles+ws.damage_dealt/ws.battles*(
    2*ws.wins/ws.battles+
    0.9*ws.frags/ws.battles+
    0.5*ws.spotted/ws.battles+
    0.5*ws.capture_points/ws.battles+
    0.5*ws.dropped_capture_points/ws.battles)
  ) bronesite

  FROM wot_statistic ws
  JOIN (SELECT
      wat.account_id,
      SUM(wt.level * wat.battles)/sum(wat.battles) midl,
      GREATEST(0,(ws.damage_dealt/SUM(etv.dmg*wat.battles)-0.22)/(1-0.22)) rDAMAGEc,
      GREATEST(0,LEAST(ws.damage_dealt/SUM(etv.dmg*wat.battles)+0.2,(ws.frags/SUM(etv.frag*wat.battles)-0.12)/(1-0.12))) rFRAGc,
      GREATEST(0,LEAST(ws.damage_dealt/SUM(etv.dmg*wat.battles)+0.1,(ws.spotted/SUM(etv.spot*wat.battles)-0.38)/(1-0.38))) rSPOTc,
      GREATEST(0,LEAST(ws.damage_dealt/SUM(etv.dmg*wat.battles)+0.1,(ws.dropped_capture_points/SUM(etv.def*wat.battles)-0.10)/(1-0.10))) rDEFc,
      GREATEST(0,(ws.wins/SUM(etv.win/100*wat.battles)-0.71)/(1-0.71)) rWINc
    FROM wot_account_tank wat
    JOIN wot_statistic ws ON ws.account_id = wat.account_id AND ws.statistic='all'
    JOIN wot_tank wt ON wt.tank_id = wat.tank_id
    LEFT JOIN wot_wn8_etv etv ON etv.IDNum=wat.tank_id
    WHERE wat.account_id IN ($accountIds)
    GROUP BY wat.account_id) a ON a.account_id = ws.account_id
    WHERE ws.statistic='all') a ON a.account_id=wa.account_id
  LEFT JOIN 
  (SELECT a.account_id, a.om*POWER(a.wp,3)*POWER(a.frags,2)*POWER(a.hp,2)*100/7.5383 ivanerr
    FROM
  (SELECT wa.account_id, wa.nickname, SUM(wt.ivanerr_kef)*100/1550 om,
    SUM(CASE WHEN wat.battles>300 THEN wat.wins*300/wat.battles ELSE wat.wins END/CASE WHEN wat.battles<300 THEN wat.battles ELSE 300 END)/SUM(wt.ivanerr_kef) wp,
    SUM(ws.frags)/SUM(ws.battles) frags,
  --  SUM(wpts.frags)/SUM(wpts.battles) frags,
    SUM(CASE WHEN ws.battles>1000 THEN ws.hits_percents ELSE 0 END)/SUM(CASE WHEN ws.battles>1000 THEN 1 ELSE 0 END)/100 hp
    FROM wot_account wa
    JOIN wot_statistic ws ON wa.account_id = ws.account_id AND ws.statistic = 'all'
    JOIN wot_account_tank wat ON wa.account_id = wat.account_id
  --  JOIN wot_player_tank_statistic wpts ON wpt.player_id = wpts.player_id AND wpt.tank_id = wpts.tank_id AND wpts.statistic_id = 1
    JOIN wot_tank wt ON wat.tank_id = wt.tank_id AND wt.level=10
    WHERE wa.account_id IN ($accountIds)
    GROUP BY wa.account_id
    ) a) ivanerr ON ivanerr.account_id=wa.account_id
  SET wa.wn6=a.wn6, wa.wn7=a.wn7, wa.wn8=a.wn8, wa.effect=a.effect, wa.armor=a.bronesite, wa.ivanerr=ivanerr.ivanerr
SQL;
			Yii::app()->db->createCommand($sql)->execute();
		}
	}
}