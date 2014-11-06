<?php

class IvannerCommand extends CConsoleCommand
{
	public function actionIndex()
	{
		$batchSql=<<<SQL
INSERT IGNORE INTO wot_clan(clan_id)values({values});
SQL;
		$i=0;
		while ($i<40) {
			$i++;
			$helper=new CUrlHelper();
			if($helper->execute('http://ivanerr.ru/lt/showclansrating/'.$i)){
				if(preg_match_all('/<a href="\/lt\/clan\/(\d+)">/', $helper->content, $matches)){
					$clans=$matches[1];
					array_unique($clans);
					$sql=str_replace('{values}', implode('),(', $clans), $batchSql);
					Yii::app()->db->createCommand($sql)->execute();
				}
			}
		}
	}
}