<?php

class WotClanProvince extends CActiveRecord
{
	
	/**
	 * 
	 * @return WotClanProvince
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_clan_province';
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
				'map_id'=>$mapName,
				'limit'=>100,
				'page_no'=>$pageNo,
			));
			if($helper->execute($url)){
				$data=CJSON::decode($helper->content);
				if($data['status']=='ok'){
					static $command;
					if(empty($command)){
						$sql=<<<SQL
INSERT INTO wot_clan_province(province_key,clan_id,start_time,revenue)
VALUES(province_key,clan_id,start_time,revenue)
ON DUPLICATE KEY UPDATE revenue=VALUES(revenue)
SQL;
						$command=Yii::app()->db->createCommand($sql);
					}
					$count=$data['status'];
					$tran=Yii::app()->db->beginTransaction();
					foreach ($data['data'] as $row){
						$clanId=$row['clan_id'];
						foreach ($row['provinces'] as $provData){
							$provinceKey=WotProvince::getProvinceKey($map->map_key,$provData['province_id']);
						}
					}
					$tran->commit();
				}
			}
		}
	}
	
}