<?php

Yii::import('application.vendors.sag.src.*');

class CouchConnection
{
	public $options = array();
	
	private $_connection;
	
	
	public function init()
	{
		$this->_connection = new Sag($this->options['host'], $this->options['port']);
		$this->_connection->setCache(new SagMemoryCache());
		$this->_connection->login($this->options['user'], $this->options['password']);
	}
	
	
	public function getConnection()
	{
		return $this->_connection;
	}
}