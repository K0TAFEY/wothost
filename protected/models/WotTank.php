<?php

class WotTank extends CActiveRecord
{
	
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
	
	public static function scan()
	{
		$sql1=<<<SQL1
INSERT IGNORE INTO wot_tank_nation(nation,nation_i18n)
VALUES(:nation,:nation_i18n);
SQL1;
		$sql2=<<<SQL2
INSERT IGNORE INTO wot_tank_type(type,type_i18n)
VALUES(:type,:type_i18n);
SQL2;
		$sql3=<<<SQL3
INSERT INTO wot_tank(tank_id,is_premium,level,name,nation,type,short_name_i18n,name_i18n)
VALUES(:tank_id,:is_premium,:level,:name,:nation,:type,:short_name_i18n,:name_i18n)
ON DUPLICATE KEY UPDATE is_premium=:is_premium,level=:level,name=:name,nation=:nation,type=:type,short_name_i18n=:short_name_i18n,name_i18n=:name_i18n;
SQL3;
		
		$url='http://api.worldoftanks.ru/wot/encyclopedia/tanks/?'.http_build_query(array(
				'application_id'=>Yii::app()->params['application_id'],
				'language'=>'ru',
		));
		$urlHelper=new CUrlHelper();
		if($urlHelper->execute($url)){
			$cmd1=Yii::app()->db->createCommand($sql1);
			$cmd2=Yii::app()->db->createCommand($sql2);
			$cmd3=Yii::app()->db->createCommand($sql3);
			$data=json_decode($urlHelper->content, true);
			if($data['status']=='ok'){
				foreach ($data['data'] as $key=>$value){
					$value['is_premium']=($value['is_premium']=='true')?1:0;
					$cmd1->execute(array_intersect_key($value, array('nation'=>'','nation_i18n'=>'')));
					$cmd2->execute(array_intersect_key($value, array('type'=>'','type_i18n'=>'')));
					$cmd3->execute(array_intersect_key($value, array('tank_id'=>'','is_premium'=>'','level'=>'','name'=>'','nation'=>'','type'=>'','short_name_i18n'=>'','name_i18n'=>'')));
				}
			}
		}
	}
	
}