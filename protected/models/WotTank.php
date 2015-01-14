<?php

class WotTank extends CActiveRecord
{
	
	private static $_ids;
	
	/**
	 * 
	 * @return WotTank
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'wot_tank';
	}
	
	public static function ensureTankId($tankId)
	{		
		if(empty(self::$_ids)){
			$keys=array_keys(self::model()->findAll(array('index'=>'tank_id')));
			self::$_ids=array_combine($keys, $keys);
		}
		if(!isset(self::$_ids[$tankId])){
			self::scan();
			$keys=array_keys(self::model()->findAll(array('index'=>'tank_id')));
			self::$_ids=array_combine($keys, $keys);
		}
		return self::$_ids[$tankId]=$tankId;
	}
	
	public static function scan()
	{
		$url='http://api.worldoftanks.ru/wot/encyclopedia/tanks/?'.http_build_query(array(
				'application_id'=>Yii::app()->params['application_id'],
				'language'=>'ru',
		));
		$urlHelper=new CUrlHelper();
		if($urlHelper->execute($url)){
			$data=CJSON::decode($urlHelper->content);
			if($data['status']=='ok'){
				$sql='INSERT INTO wot_tank(tank_id,is_premium,level,name,nation,type,short_name_i18n,name_i18n,image,image_small,contour_image)VALUES(';
				$values=array();
				foreach ($data['data'] as $key=>$value){
					$values[]=implode(',', array(
							$value['tank_id'],
							($value['is_premium']=='true')?1:0,
							$value['level'],
							"'{$value['name']}'",
							"'".WotTankNation::ensureTankNation($value['nation'], $value['nation_i18n'])."'",
							"'".WotTankType::ensureTankType($value['type'], $value['type_i18n'])."'",
							"'{$value['short_name_i18n']}'",
							"'{$value['name_i18n']}'",
							"'{$value['image']}'",
							"'{$value['image_small']}'",
							"'{$value['contour_image']}'",
					));
				}
				$sql.=implode('),(', $values).')';
				$sql.='ON DUPLICATE KEY UPDATE is_premium=VALUES(is_premium),level=VALUES(level),name=VALUES(name),nation=VALUES(nation),type=VALUES(type),short_name_i18n=VALUES(short_name_i18n),name_i18n=VALUES(name_i18n),image=VALUES(image),image_small=VALUES(image_small),contour_image=VALUES(contour_image);';
				Yii::app()->db->createCommand($sql)->execute();
			}
		}
	}	
}