<?php

Yii::import('application.models.*');

class ExportCommand extends CConsoleCommand
{
	private $lastFlushTime;

	private $cache = array();

	public function run($args)
	{
		$channels = Yii::app()->RediskaConnection->getConnection()->subscribe(array(
			Yii::app()->params['map_channel'],
			Yii::app()->params['schedule_channel']
		));

		$this->lastFlushTime = time();
		try {
			foreach ($channels as $channel => $message) {
				if ($channel == Yii::app()->params['map_channel']) {
					$this->processMapMessage($message);
				}

				if ($channel == Yii::app()->params['schedule_channel']) {
					$this->processScheduleMessage($message);
				}

				if (time() > $this->lastFlushTime + Yii::app()->params['flushtime']) {
					$this->flushMapCache();
				}
			}
		} catch (SagCouchException $e) {
			Yii::log($e->getMessage(), 'error', 'application.commands.ExportCommand');
			throw $e;
		} catch (SagException $e) {
			Yii::log($e->getMessage(), 'error', 'application.commands.ExportCommand');
			throw $e;
		}
	}

	public function processMapMessage ($message) {
		echo "MAP: ";
		print $message;
		echo "\n";
		$message = CJSON::decode($message);

		$item = new StdClass();
		$item->bort = $message['bort'];
		$item->latitude = $message['latitude'];
		$item->longitude = $message['longitude'];
		$item->schedules_id = $message['schedules_id'];
		$item->speed = $message['speed'] ? intval($message['speed']) : -1;
		try {
			$date = new DateTime($message['datatime']);
		} catch (Exception $e) {
			Yii::log($e->getMessage(), 'warning', 'application.commands.ExportCommand');
			$date = new DateTime('@0');
		}

		$item->date = explode('-', $date->format('Y-m-d')); 
		$item->time = explode(':', $date->format('H:i:s')); 

		$id = $this->uuid();
		Yii::app()->cache->set($id, $item, 120);
		array_push($this->cache, $id);

	}

	public function processScheduleMessage ($message) {
		echo "SCHEDULE: ";
		print $message;
		echo "\n";

		$message = CJSON::decode($message);
		Yii::app()->CouchConnection->getConnection()->setDatabase(Yii::app()->params['schedule_db'], true);

		$schedule = Schedules::model()->findByPk($message['schedules_id']);
		
		$item = new StdClass();
		// $item->_id = $this->uuid();
		$item->bort = $message['bort'];
		try {
			$date = new DateTime($message['datatime']);
		} catch (Exception $e) {
			Yii::log($e->getMessage(), 'warning', 'application.commands.ExportCommand');
			$date = new DateTime('@0');
		}

		$item->date = explode('-', $date->format('Y-m-d')); 
		$item->time = explode(':', $date->format('H:i:s')); 
		$item->flight_number = $message['flight_number'];
		$item->points_control_scenario_id = $message['points_control_scenario_id'];
		$item->schedules_id = $message['schedules_id'];
		$item->time_difference = $message['time_difference'];

		if ($schedule) {
			$item->graph = $schedule->graph->id;
			$item->route = $schedule->graph->route->id;
			$item->vehicle_type = $schedule->graph->route->vehicletype->id;
		} else {
			Yii::log('Not exists schedule with id ' . $message['schedules_id'], 'warning', 'application.commands.ExportCommand');
		}

		echo "Push schedule data: ".date("Y-m-d H:i:s")."\n\n"; 
		Yii::app()->CouchConnection->getConnection()->put($this->uuid(), $item);
	}

	public function flushMapCache () {
		$this->lastFlushTime = time();
		if (empty($this->cache)) return;
		Yii::app()->CouchConnection->getConnection()->setDatabase(Yii::app()->params['map_db'], true);
		
		$result = new StdClass();
		$result->date = explode('-', date("Y-m-d"));
		$result->time = explode(':', date("H:i:s"));
		$result->items = array();

		foreach ($this->cache as $id) {
			array_push($result->items, Yii::app()->cache->get($id));
		}

		echo "Push map data: ".date("Y-m-d H:i:s")."\n\n"; 
		Yii::app()->CouchConnection->getConnection()->put($this->uuid(), $result);
		$this->cache = array();
	}

	private function uuid() {
		return substr(sha1(uniqid(mt_rand(), true)), 0, 32);
	}
}