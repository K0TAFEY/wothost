<?php

class WotProvinceClan extends CActiveRecord
{
	
	/**
	 * 
	 * @return WotProvinceClan
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_province_clan';
	}
	
	public static function scan($mapId='globalmap')
	{
		$map=WotMap::model()->findByAttributes(array('map_id'=>$mapId));
		if(empty($map))
				throw new CException("Map #$mapId not found");
		
		$helper=new CUrlHelper();
		
		$count=100;
		while ($count===100) 
		{
			$count=0;
			$url='http://api.worldoftanks.ru/wot/globalwar/clans/?'.http_build_query(array(
				'application_id'=>Yii::app()->params['application_id'],
				'map_id'=>$map->map_id,
				'limit'=>100,
				'page_no'=>$pageNo,
			));
			if($helper->execute($url)){
				$data=CJSON::decode($helper->content);
				if($data['status']=='ok'){
					$count=$data['count'];
					static $command;
					if(empty($command)){
						$sql=<<<SQL
INSERT INTO wot_province_clan(province_key,clan_id,occupancy_time,start_time)
VALUES(:province_key,:clan_id,:occupancy_time,UNIX_TIMESTAMP() - MOD(UNIX_TIMESTAMP(),3600)-24*3600*:occupancy_time)
ON DUPLICATE KEY UPDATE clan_id=VALUES(clan_id),occupancy_time=VALUES(occupancy_time)
SQL;
						$command=Yii::app()->db->createCommand($sql);
					}
					$tran=Yii::app()->db->beginTransaction();
					foreach ($data['data'] as $row){
						$clanId=$row['clan_id'];
						foreach ($row['provinces'] as $provData){
							$command->execute(array(
								'province_key'=>WotProvince::getProvinceKey($map->map_key,$provData['province_id']),
								'clan_id'=>$clanId,
								'occupancy_time'=>$provData['occupancy_time'],
							));
						}
					}
					$tran->commit();
				}
			}
		}
	}
	
}