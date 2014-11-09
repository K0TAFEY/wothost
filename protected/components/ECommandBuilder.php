<?php

class ECommandBuilder extends CDbCommandBuilder
{
	public $onDuplicate;
	
	/**
	 * Creates an INSERT command.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $data data to be inserted (column name=>column value). If a key is not a valid column name, the corresponding value will be ignored.
	 * @return CDbCommand insert command
	 */
	public function createInsertCommand($table,$data)
	{
		$this->ensureTable($table);
		$fields=array();
		$values=array();
		$placeholders=array();
		$i=0;
		foreach($data as $name=>$value)
		{
			if(($column=$table->getColumn($name))!==null && ($value!==null || $column->allowNull))
			{
				$fields[]=$column->rawName;
				if($value instanceof CDbExpression)
				{
					$placeholders[]=$value->expression;
					foreach($value->params as $n=>$v)
						$values[$n]=$v;
				}
				else
				{
					$placeholders[]=self::PARAM_PREFIX.$i;
					$values[self::PARAM_PREFIX.$i]=$column->typecast($value);
					$i++;
				}
			}
		}
		if($fields===array())
		{
			$pks=is_array($table->primaryKey) ? $table->primaryKey : array($table->primaryKey);
			foreach($pks as $pk)
			{
				$fields[]=$table->getColumn($pk)->rawName;
				$placeholders[]=$this->getIntegerPrimaryKeyDefaultValue();
			}
		}		
		
		$sql=false;
		
		//begin onDuplicate logic
		if ($this->onDuplicate!==null){
			$autoIncrement=false;
			foreach($table->columns as $columnSchema){
				if ($columnSchema->autoIncrement){
					$autoIncrement=$columnSchema->rawName;
					break;
				}
			}
			if ($autoIncrement!==false || $this->onDuplicate=='update'){								
				$sql="INSERT INTO {$table->rawName} (".implode(', ',$fields).') VALUES ('.implode(', ',$placeholders).')';
				$sql.=" ON DUPLICATE KEY UPDATE";
				
				$updateFields=array();
				if ($autoIncrement!==false){
					if (($autoIncrementIndex=array_search($autoIncrement,$fields))!==false){
						unset($fields[$autoIncrementIndex]);
						$placeHolder=$placeholders[$autoIncrementIndex];
						unset($placeholders[$autoIncrementIndex]);
						$updateFields=array_values($updateFields);
					}
					$updateFields[]="{$autoIncrement}=LAST_INSERT_ID({$autoIncrement})";
				}
				 
				if ($this->onDuplicate=='update'){
					foreach ($fields as $key=>$field){
						$updateFields[]="{$field}={$placeholders[$key]}";
					}
				}
				$sql.=implode(',',$updateFields);
				
			} else if ($this->onDuplicate=='ignore' && $autoIncrement===false){
				$sql="INSERT IGNORE INTO {$table->rawName} (".implode(', ',$fields).') VALUES ('.implode(', ',$placeholders).')';
			}
		}
		//end onDuplicate logic
		
		if($sql===false)
			$sql="INSERT INTO {$table->rawName} (".implode(', ',$fields).') VALUES ('.implode(', ',$placeholders).')';
		
		$command=$this->getDbConnection()->createCommand($sql);
	
		foreach($values as $name=>$value)
			$command->bindValue($name,$value);
	
		return $command;
	}
}