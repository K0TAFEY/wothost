<?php

class CTimestampBehavior extends CActiveRecordBehavior {

	/**
	 * Responds to {@link CActiveRecord::onBeforeSave} event.
	 * Override this method and make it public if you want to handle the corresponding
	 * event of the {@link CBehavior::owner owner}.
	 * You may set {@link CModelEvent::isValid} to be false to quit the saving process.
	 * @param CModelEvent $event event parameter
	 */
	public function beforeSave($event)
	{
		$model=$this->owner;
		foreach ($this->owner->metaData->columns as $column){
			$columnName=$column->name;
			switch ($column->dbType){
				case 'date':
					if(is_numeric($model->$columnName))
						$model->$columnName=date('Y-m-d', $model->$columnName);
					elseif(is_string($model->$columnName))
						$model->$columnName=date('Y-m-d',strtotime($model->$columnName));
					break;
				case 'datetime':
					if(is_numeric($model->$columnName))
						$model->$columnName=date('Y-m-d H:i:s', $model->$columnName);
					elseif(is_string($model->$columnName))
						$model->$columnName=date('Y-m-d H:i:s',strtotime($model->$columnName));
					break;
			}
		}
		return true;
	}

}