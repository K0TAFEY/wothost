<?php

class WotProvince extends EActiveRecord
{
	
	private static $_ids;
	
	public $onDuplicate = self::DUPLICATE_UPDATE;
	
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
	
	public static function getProvinceId($name)
	{
		if(isset(self::$_ids[$name]))
			return self::$_ids[$name];
		$prov=new WotProvince();
		$prov->province_name=$name;
		$prov->save(false);
		self::$_ids[$name]=$prov->province_id;
		return $prov->province_id;
	}
	
	public static function scan($mapName)
	{
		$map=WotMap::model()->findByAttributes(array('map_name'=>$mapName));
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
				foreach ($data['data'] as $provId=>$provData){
					$prov=new WotProvince();
					$prov->map_id=$map->map_id;
					$prov->province_name=$provData['province_id'];
					$prov->province_i18n=$provData['province_i18n'];
					$prov->updated_at=$provData['updated_at'];
					$prov->prime_time=$provData['prime_time'];
					$prov->vehicle_max_level=$provData['vehicle_max_level'];
					$prov->status=$provData['status'];
					$prov->revenue=$provData['revenue'];
					$prov->arena_id=WotArena::getArenaId($provData['arena'], $provData['arena_i18n']);
					if(isset($provData['regions'][$provData['primary_region']]))
						$prov->primary_region_id=WotRegion::getRegionId(
								$provData['regions'][$provData['primary_region']]['region_id'], 
								$provData['regions'][$provData['primary_region']]['region_i18n']
					);
					$prov->save(false);
					if(empty($prov->province_id))
						$prov->province_id=$prov->dbConnection->getLastInsertID();
					foreach ($provData['regions'] as $regData){
						$provRegion=new WotProvinceRegion();
						$provRegion->region_id=WotRegion::getRegionId($regData['region_id'], $regData['region_i18n']);
						$provRegion->province_id=$prov->province_id;
						$provRegion->save(false);
					}
					foreach ($provData['neighbors'] as $neighData){
						$provNeigh=new WotProvinceNeighbor();
						$provNeigh->neighbor_id=WotProvince::getProvinceId($neighData);
						$provNeigh->province_id=$prov->province_id;
						$provNeigh->save(false);
					}
					if(!empty($provData['clan_id'])){
						$clanProv=new WotClanProvince();
						$clanProv->province_id=$prov->province_id;
						$clanProv->clan_id=$provData['clan_id'];
						$clanProv->hold_date=$provData['updated_at'];
						$clanProv->revenue=$provData['revenue'];
						$clanProv->save(false);
					}
				}
			}
			else{
				echo '<pre>';
				CVarDumper::dump($data);
			}
				
		}
	}
	
}