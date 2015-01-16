<?php

class WotProvince extends CActiveRecord
{
	
	private static $_keys;
	
	
	/**
	 * 
	 * @return WotProvince
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_province';
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
	
	public function behaviors()
	{
		return array(
			'locale'=>'application.behaviors.CLocaleBehavior',
		);
	}
	
	public static function getProvinceKey($provinceData)
	{
		if(isset(self::$_keys[$name]))
			return self::$_ids[$name];
		$prov=new WotProvince();
		$prov->province_name=$name;
		$prov->save(false);
		self::$_ids[$name]=$prov->province_id;
		return $prov->province_id;
	}
	
	public static function scan($mapName)
	{
		$map=WotMap::model()->findByAttributes(array('map_id'=>$mapName));
		if(empty($map))
			throw new CException('Invalid map name');
		
		$helper=new CUrlHelper();
		$url='http://api.worldoftanks.ru/wot/globalwar/provinces/?'.http_build_query(array(
			'application_id'=>Yii::app()->params['application_id'],
			'map_id'=>$mapName
		));
		if($helper->execute($url)){
			$data=CJSON::decode($helper->content);
			if($data['status']=='ok'){
				$sql=<<<SQL
INSERT INTO wot_province
(map_key,province_id,province_i18n,status,arena_key,vehicle_max_level,revenue,primary_region_key,prime_time,updated_at,neighbors)
VALUES
(:map_key,:province_id,:province_i18n,:status,:arena_key,:vehicle_max_level,:revenue,:primary_region_key,:prime_time,:updated_at,:neighbors)
ON DUPLICATE KEY UPDATE map_key=LAST_INSERT_ID(map_key),province_id=VALUES(province_id),province_i18n=VALUES(province_i18n),status=VALUES(status),arena_key=VALUES(arena_key),vehicle_max_level=VALUES(vehicle_max_level),revenue=VALUES(revenue),primary_region_key=VALUES(primary_region_key),prime_time=VALUES(prime_time),updated_at=VALUES(updated_at),neighbors=VALUES(neighbors)
SQL;
				$command=Yii::app()->db->createCommand($sql);				
				$tran=Yii::app()->db->beginTransaction();
				foreach ($data['data'] as $provId=>$provData){
					
					$command->execute(array(
						'map_key'=>$map->map_key,
						'province_id'=>$provData['province_id'],
						'province_i18n'=>$provData['province_i18n'],
						'status'=>$provData['status'],
						'arena_key'=>WotArena::getArenaKey($provData['arena_id'], $provData['arena_i18n']),
						'vehicle_max_level'=>$provData['vehicle_max_level'],
						'revenue'=>$provData['revenue'],
						'primary_region_key'=>WotRegion::getRegionKey(
								$provData['regions'][$provData['primary_region']]['region_id'], 
								$provData['regions'][$provData['primary_region']]['region_i18n']
						),
						'prime_time'=>$provData['prime_time'],
						'updated_at'=>$provData['updated_at'],
						'neighbors'=>serialize($provData['neighbors']),
					));
					$provinceKey=$command->getConnection()->getLastInsertID();
					foreach ($provData['regions'] as $regData){
						WotProvinceRegion::addProvinceRegion(
							$provinceKey, 
							WotRegion::getRegionKey($regData['region_id'], $regData['region_i18n'])
						);						
					}
				}
				$tran->commit();
			}
			else{
				echo '<pre>';
				CVarDumper::dump($data);
			}
				
		}
	}
	
}