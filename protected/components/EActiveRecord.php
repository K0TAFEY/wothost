<?php

class EActiveRecord extends CActiveRecord
{
	private $_builder;
	
	const DUPLICATE_IGNORE='ignore';
	const DUPLICATE_UPDATE='update';
	
	public $onDuplicate;
	
	public function getCommandBuilder()
	{
		if(empty($this->_builder))
			$this->_builder=new ECommandBuilder($this->getDbConnection()->getSchema());
		$this->_builder->onDuplicate=$this->onDuplicate;
		return $this->_builder;
	}
		
}