<?php

class WotMap extends EActiveRecord
{
	
	public $onDuplicate = self::DUPLICATE_UPDATE;
	
	/**
	 * 
	 * @return WotMap
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_map';
	}
	
	public static function scan()
	{
		$url=new CUrlHelper();
		if($url->execute('http://api.worldoftanks.ru/wot/globalwar/maps/?application_id='.Yii::app()->params['application_id'])){
			$data=CJSON::decode($url->content);
			if($data['status']=='ok'){
				foreach ($data['data'] as $mapData){
					$map=new WotMap();
					$map->map_name=$mapData['map_id'];
					$map->type=$mapData['type'];
					$map->state=$mapData['state'];
					$map->map_url=$mapData['map_url'];
					$map->save(false);
				}
			}
		}
	}
	
}