<?php

class CLocaleBehavior extends CActiveRecordBehavior 
{

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
			$value=$model->$columnName;
			if(!(is_object($value) && is_a($value, 'CDbExpression')))
			{
				switch (strtolower($column->dbType)){
					case 'date':
						if(is_numeric($value))
							$model->$columnName=date('Y-m-d', $value);
						elseif(preg_match('/\d{4}-\d{2}-\d{2}/', $value, $matches))
							$model->$columnName=date('Y-m-d', strtotime($matches[0]));
						else
							$model->$columnName=date('Y-m-d',strtotime($value));
						break;
					case 'datetime':
						if(is_numeric($value))
							$model->$columnName=date('Y-m-d H:i:s', $value);
						else
							$model->$columnName=date('Y-m-d H:i:s',strtotime($value));
						break;
				}
			}
		}
		return true;
	}
	
}