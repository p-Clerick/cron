<?php

class ConvertController extends CController
{
	/**
	 * Convert bort data from MySQL to CouchDB 
	 *
	 */
	public function actionUpdate ($id) {
		try {
			$this->copyMap($id);
			// $this->copySchedule($id);
		} catch (SagCouchException $e) {
			throw $e;
			Yii::log($e->getMessage(), "error", "report.convert");
		} catch (SagException $e) {
			throw $e;
			Yii::log($e->getMessage(), "error", "report.convert");
		} catch (Exception $e) {
			throw $e;
			Yii::log($e->getMessage(), "error", "report.convert");
		}
	}

	private function copyMap ($id) {		
		if ($id) {
			$this->module->db->setDatabase('map' . $id, true);
		} else {
			$this->module->db->setDatabase('map', true);
		}
		$table = "move_on_map_total";
		$selectExp = "borts_id as bort, latitude, longitude, speed, DATE(datatime) as date, TIME(datatime) as time, schedules_id, direction";
		$this->copyMapDataInCouch($id, $table, $selectExp);
	}

	private function copySchedule ($id) {		
		if ($id) {
			$this->module->db->setDatabase('schedule' . $id, true);
		} else {			
			$this->module->db->setDatabase('schedule', true);
		}
		$table = "move_on_schedule_total";
		$selectExp = "borts_id as bort, flight_number, points_control_scenario_id, time_difference, DATE(datatime) as date, TIME(datatime) as time, schedules_id";
		$this->copyDataInCouch($id, $table, $selectExp);
	}

	private function copyDataInCouch($id, $table, $select) {
		$totalRecords = array();
		$command = Yii::app()->db->createCommand();
		$command->from($table)
		        ->select($select)
		        ->limit(10000)
		        // ->where("borts_id = :bid", array("bid"=>$id))
		        ->order("id ASC");
		
		$totalReader = $command->queryAll();

		$result = array();
		$schedulesCache = array();
		foreach ($totalReader as $item) {
			$item['date'] = explode("-", $item['date']);
			$item['time'] = explode(":", $item['time']);
			$item['_id'] = $this->uuid();

			if (!isset($schedulesCache[$item['schedules_id']])) {
				$schedulesCache[$item['schedules_id']] = Schedules::model()->findByPk($item['schedules_id']);
			}
			if (isset($schedulesCache[$item['schedules_id']])) {
				$item['speed'] = ($item['speed'] != 0) ? intval($item['speed']) : -1;
				$item['graph'] = $schedulesCache[$item['schedules_id']]->graph->id;
				$item['route'] = $schedulesCache[$item['schedules_id']]->graph->route->id;
				$item['vehicle_type'] = $schedulesCache[$item['schedules_id']]->graph->route->vehicletype->id;
				$result[] = $item;
			}
		}
		// echo CJSON::encode($result);
		// exit;
		$this->module->db->bulk($result);
		
		echo CJSON::encode($totalReader);
	}

	private function copyMapDataInCouch($id, $table, $select) {
		$totalRecords = array();
		$command = Yii::app()->db->createCommand();
		$command->from($table)
		        ->select($select)
		        ->order("id ASC");
		
		$docs = array();
		for ($i = 0; $i < 10; $i++) {
			$totalReader = $command->offset(100000 + 100 * $i)
			                       ->limit(1000)
			                       ->queryAll();
			// print_r($totalReader);
			// exit;
			$doc = array(
				'_id' => $this->uuid(),
				'date' => array("2011", "09", "15"),
				'time' => array("21", "10", "10")
			);
			$schedulesCache = array();
			foreach ($totalReader as $item) {
				$item['date'] = explode("-", $item['date']);
				$item['time'] = explode(":", $item['time']);

				if (!isset($schedulesCache[$item['schedules_id']])) {
					$schedulesCache[$item['schedules_id']] = Schedules::model()->findByPk($item['schedules_id']);
				}
				if (isset($schedulesCache[$item['schedules_id']])) {
					$item['speed'] = ($item['speed'] != 0) ? intval($item['speed']) : -1;
					$item['graph'] = $schedulesCache[$item['schedules_id']]->graph->id;
					$item['route'] = $schedulesCache[$item['schedules_id']]->graph->route->id;
					$item['vehicle_type'] = $schedulesCache[$item['schedules_id']]->graph->route->vehicletype->id;
					$doc['items'][] = $item;
				}
			}
			$docs[] = $doc;
			// echo CJSON::encode($result);
			// exit;
		}
		$this->module->db->bulk($docs);			
		
		// echo CJSON::encode($docs);
	}

	private function uuid() {
		return substr(sha1(uniqid(mt_rand(), true)), 0, 32);
	}
}