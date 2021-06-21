<?php

class MileageController extends CController
{
	private $acceptedBorts;
	private $bortSchedule;
	private $level;
	private $node;

	public function actionRead($level, $node, $from, $to){
		$result = array();
		if( Yii::app()->user->checkAccess('getMileageReport') ){			
			isset($_GET['start']) ? $start = $_GET['start'] : $start = 0;
			isset($_GET['limit']) ? $limit = $_GET['limit'] : $limit = 200;

			$to = $this->nextDay($to);

			$url = $this->buildUrl($from, $to, 2);
			$this->module->db->setDatabase($this->module->mapDb);

			$this->acceptedBorts = $this->findAcceptedBorts($level, $node, $from, $to);

			try {
				Yii::beginProfile('mileage: fetch data');
				Yii::log("mileage: $url", 'info', 'report.mileage');
				$allRecord = $this->module->db->get($url);
				Yii::endProfile('mileage: fetch data');
				$result['success'] = true;
				Yii::beginProfile('mileage: format result');
				$result['rows'] = $this->formatResult($allRecord->body->rows);
				Yii::endProfile('mileage: format result');
			} catch (SagException $e) {
				Yii::log($e->getMessage(), "error", "report.mileage");
			}
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}
		echo CJSON::encode($result);
	}

	protected function findAcceptedBorts ($level, $node, $from, $to) {
		$this->setVehicleParams($level, $node);
		$url = $this->buildBortScheduleUrl($from, $to);
		try {
			$bortSchedule = $this->module->db->get($url)->body->rows;
		} catch (SagException $e) {
			Yii::log($e->getMessage(), "error", "report.mileage");
		}
		$result = array_filter($bortSchedule, array($this, 'filterAcceptedBorts'));
		$this->bortSchedule = $this->mapBortSchedule($result);
		$result = $this->mapAcceptedBorts($result);
		return $result;
	}

	protected function mapBortSchedule ($all) {
		$result = array();
		foreach ($all as $item) {
			$result[$this->dateToStr($item->key[0])][intval($item->key[1])] = intval($item->key[2]);
		}
		return $result;
	}

	protected function filterAcceptedBorts ($item) {
		if (!Borts::model()->findByPk($item->key[1])) {
			Yii::log("Не знайдено борт: " . $item->key[1], "warning", "report.mileage");
			return false;
		}
		$carrier_id = Borts::model()->findByPk($item->key[1])->park->carriers_id;
		
		$res = false;
		if (Yii::app()->user->carrier->id == 0 || $carrier_id == Yii::app()->user->carrier->id) {
			switch ($this->level) {
				case Tree::LEVEL_VEHICLE:
					if (Borts::model()->findByPk($item->key[1])->model->transport_types_id == $this->node) $res = true;
					else 
						$res = false;
					break;
				case Tree::LEVEL_ROUTE:
					if (Schedules::model()->findByPk(intval($item->key[2]))->graph->routes_id == $this->node)
					{
						$res = true;
					}
					else 
						$res = false;
					break;
				case Tree::LEVEL_SCHEDULE: 
					if (Schedules::model()->findByPk(intval($item->key[2]))->graphs_id == $this->node) 
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

	protected function mapAcceptedBorts ($all) {
		$result = array();
		foreach ($all as $item) {
			$result[$this->dateToStr($item->key[0])][] = intval($item->key[1]);
		}
		foreach ($result as $key => $item) {
			$result[$key] = array_unique($item);
		}
		return $result;
	}

	protected function dateToStr ($date) {
		return implode($date, "-");
	}

	protected function setVehicleParams ($level, $node) {
		$this->level = $level;
		$this->node = $node;
	}

	protected function formatResult ($data) {
		$filtered = array_filter($data, array($this, 'filter'));
		$result = array();
		$g_n = -1;
		$m_n = -1;

		foreach ($filtered as $item) {
			$sched_id = $this->bortSchedule[$this->dateToStr($item->key[0])][$item->key[1]];
			if ($s = Schedules::model()->findByPk($sched_id) ) {
				$bort = Borts::model()->findByPk($item->key[1]);
				if ($s->graph && $s->graph->route) {
					$result[] = array(
						'date' => implode('-', $item->key[0]),
						'bort' => $bort->number,
						'mileage' => round($item->value / 1000 , 1),
						'g_id' => $s->graph->id,
						'g_n' => $s->graph->name,
						'm_n' => $s->graph->route->name,
						'carrier' => $bort->park->carrier->name,
					);
				}
			} else {
				Yii::log("Not found schedule with id:".$sched_id, "error", "report.mileage");
			}
		}
		return $result;
	}

	protected function filter ($item) {
		return isset($this->acceptedBorts[$this->dateToStr($item->key[0])]) ? in_array($item->key[1], $this->acceptedBorts[$this->dateToStr($item->key[0])]) : false;
	}

	protected function buildUrl($from, $to, $groupLevel) {
		$from = implode('","', explode('-', $from));
		$to = implode('","', explode('-', $to));
		
		$url = "_design/mileage/_view/all";
		$url .= '?startkey=[["'.$from.'"]]'; 
		$url .= '&endkey=[["'.$to.'"]]'; 
		$url .= '&group_level='.$groupLevel;
		return $url;
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

	private function nextDay($date) {
		$oneDay = new DateInterval('P1D');
		$date = new DateTime($date);
		$date = $date->add($oneDay);
		$date = $date->format('Y-m-d');
		return $date;
	}
}