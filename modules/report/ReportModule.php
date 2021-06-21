<?php

class ReportModule extends CWebModule
{
	/**
	 * Database connection instance
	 * @var Sag
	 */
	private $db = null;

	/**
	 * Connection config
	 * @var Array
	 */
	public $connection;

	/**
	 * Speedmode report config
	 * @var Array
	 */
	public $speedmode;

	/**
	 * Name of map database
	 * @var string
	 */
	public $mapDb;

	/**
	 * Name of schedule database
	 * @var string
	 */
	public $scheduleDb;

	public function beforeControllerAction() {
		Yii::import('application.vendors.*');
		require_once('sag/src/Sag.php');
		require_once('sag/src/SagFileCache.php');
		require_once('sag/src/SagMemoryCache.php');
		return true;
	}

	protected function getDb() {
		if (!$this->db) {
			$this->setupConnection();
			return $this->db;
		}
		return $this->db;
	}

	private function setupConnection () {		
		$this->db = new Sag($this->connection['host'], $this->connection['port']);
		$this->db->setCache(new SagMemoryCache());
		$this->db->login($this->connection['user'], $this->connection['password']);
	}

}