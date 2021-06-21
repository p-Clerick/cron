<?php

class ParkingController extends CController
{
	private $acceptedBorts;
	private $orders;
	private $mappedBortSchedule;
	private $level;
	private $node;
	private $from;
	private $to;

	public function actionRead($level, $node, $from, $to){
		$result = array();
		/*if( Yii::app()->user->checkAccess('getNoconnectionReport') ){			
			isset($_GET['start']) ? $start = $_GET['start'] : $start = 0;
			isset($_GET['limit']) ? $limit = $_GET['limit'] : $limit = 200;

			$this->prepareResult($level, $node, $from, $to);
			$inOrder = $this->getBortScheduleInOrder($from, $to);
			$inFact= $this->getBortScheduleInFact($from, $to);
			$result['success'] = true;
			$result['rows'] = $this->findNoconnectionBorts($inOrder, $inFact);
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}*/
		$result['success'] = true;
		$result['rows'] = array();
		echo CJSON::encode($result);
	}

	protected function prepareResult ($level, $node, $from, $to) {
		$this->level = $level;
		$this->node = $node;
		$this->from = $from;
		$this->to = $to;

		$this->orders = $this->findOrders($from, $to);
		$this->acceptedBorts = $this->findAcceptedBorts();
	}

	protected function findOrders ($from, $to) {
		$orders = Orders::model()->findAll("(`from` BETWEEN :from AND :to) OR (`to` BETWEEN :from AND :to)", array(
			':from' => $from,
			':to' => $to,
		));
		$result = $this->fillOrderByDate($orders);
		return $result;
	}

	/**
	 * Дано наряд як період між двома датами, id борта і графіка.
	 * Знаходить id борта та графіка для кожної дати з періода наряда
	 * @param array $records
	 * @return array У форматі 'date:bort:graph'
	 */
	protected function fillOrderByDate ($records) {
		$result = array();
		foreach ($records as $item) {
			$bort = Borts::model()->findByPk($item->borts_id);
			$graph = Graphs::model()->findByPk($item->graphs_id);
			$interval = new DateInterval('P1D');

			$start = DateTime::createFromFormat('Y-m-d', $item->from);
			$end = DateTime::createFromFormat('Y-m-d', $item->to);
			$end = $end->add($interval);

			$period = new DatePeriod($start, $interval, $end);
			foreach ($period as $date) {
				$key = implode(array($date->format('Y-m-d'), $item->borts_id, $item->graphs_id), ":");
				$result[$key] = array(
					'bort' => $bort->number,
					'bort_id' => $bort->id,
					'tz_type' => $bort->model->vehicletype->name,
					'tz_type_id' => $bort->model->vehicletype->id,
					'graph' => $graph->name,
					'graph_id' => $graph->id,
					'route' => $graph->route->name,
					'route_id' => $graph->route->id,
					'date' => $date->format('Y-m-d'),
					'carrier_id' => $bort->park->carrier->id,
					'carrier' => $bort->park->carrier->name,
				);
			}
		}
		return $result;
	}

	protected function findAcceptedBorts () {
		$filtered = array_filter($this->orders, array($this, 'filterAcceptedBorts'));
		$result = $this->groupAcceptedBortsByDate($filtered);
		return $result;
	}

	protected function groupAcceptedBortsByDate ($all) {
		$result = array();
		foreach ($all as $item) {
			$result[$item['date']][] = intval($item['bort_id']);
		}
		foreach ($result as $key => $item) {
			$result[$key] = array_unique($item);
		}
		return $result;
	}

	protected function filterAcceptedBorts ($item) {
		$res = false;
		if (Yii::app()->user->carrier->id == 0 || $item['carrier_id'] == Yii::app()->user->carrier->id) {
			switch ($this->level) {
				case Tree::LEVEL_VEHICLE:
					if ($item['tz_type_id'] == $this->node) $res = true;
					else 
						$res = false;
					break;
				case Tree::LEVEL_ROUTE:
					if ($item['route_id'] == $this->node)
					{
						$res = true;
					}
					else 
						$res = false;
					break;
				case Tree::LEVEL_SCHEDULE: 
					if ($item['graph_id'] == $this->node) 
						$res = true;
					else 
						$res = false;
					break;
			}
		} else {
			$res = false;
		}
		return $res;
	}

	protected function getBortScheduleInOrder ($from, $to) {
		return array_filter($this->orders, array($this, 'filterOrderedByRequestParams'));
	}

	protected function filterOrderedByRequestParams ($item) {
		return isset($this->acceptedBorts[$item['date']]) ? in_array($item['bort_id'], $this->acceptedBorts[$item['date']]) : false;
	}

	protected function getBortScheduleInFact ($from, $to) {
		$this->module->db->setDatabase($this->module->scheduleDb);
		$url = $this->buildUrl($from, $to, 3);
		try {
			$records = $this->module->db->get($url);
		} catch (SagException $e) {
			Yii::log($e->getMessage(), "error", "report.noconnection");
		}

		$filtered = array_filter($records->body->rows, array($this, 'filter'));

		$result = array_map(array($this, 'mapInFactRecords'), $filtered);
		$rs = array();
		foreach ($result as $item) {
			$rs[key($item)] = $item[key($item)];
		}
		return $rs;
	}

	protected function mapInFactRecords ($item) {
		$date = $this->dateToStr($item->key[0]);
		$bort = $item->key[1];
		$search_key = implode(array($date, $bort), ":");
		$schedule = isset($this->mappedBortSchedule[$search_key]) ? 
					$this->mappedBortSchedule[$search_key]['schedule'] : -1;
		$graph = $schedule != -1 ? Schedules::model()->findByPk($schedule)->graphs_id : -1;
		$key = implode(array($date, $bort, $graph), ":");
		return array(
			$key => array(
				'bort_id' => $bort,
				'schedule_id' => $schedule,
				'graph_id' => $graph,
			),
		);
	}

	protected function findNoconnectionBorts ($inOrder, $inFact) {
		$noconnectionBortKeys = array_diff(array_keys($inOrder), array_keys($inFact));
		$rs = array();
		foreach ($noconnectionBortKeys as $key) {
			$rs[] = $inOrder[$key];
		}
		return $rs;
	}
	
	protected function filter ($item) {
		return isset($this->acceptedBorts[$this->dateToStr($item->key[0])]) ? in_array($item->key[1], $this->acceptedBorts[$this->dateToStr($item->key[0])]) : false;
	}

	protected function dateToStr ($date) {
		return implode($date, "-");
	}

	protected function buildBortScheduleUrl($from, $to) {
		$from = implode('","', explode('-', $from));
		$to = implode('","', explode('-', $to));
		
		$url = "_design/bortschedule/_view/all";
		$url .= '?startkey=[["'.$from.'"]]'; 
		$url .= '&endkey=[["'.$to.'"]]';
		$url .= '&group_level=3';
		return $url;
	}
	
	protected function buildUrl($from, $to, $groupLevel) {
		$from = implode('","', explode('-', $from));
		$to = implode('","', explode('-', $to));
		
		$url = "_design/connection/_view/all";
		$url .= '?startkey=[["'.$from.'"]]'; 
		$url .= '&endkey=[["'.$to.'"]]'; 
		$url .= '&group_level='.$groupLevel;
		return $url;
	}

	private function nextDay($date) {
		$oneDay = new DateInterval('P1D');
		$date = new DateTime($date);
		$date = $date->add($oneDay);
		$date = $date->format('Y-m-d');
		return $date;
	}
}