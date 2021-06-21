<?php

Yii::import('application.vendors.rediska.library.*');

class RediskaConnection
{
	public $options = array();
	
	private $_rediska;
	
	
	public function init()
	{
		$this->_rediska = new Rediska($this->options);
	}
	
	
	public function getConnection()
	{
		return $this->_rediska;
	}
}