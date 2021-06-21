<?php

class SpeedmodeController extends CController
{
	private $acceptedBorts;
	private $bortSchedule;
	private $level;
	private $node;

	public function actionRead($level, $node, $from, $to) {
		$result = array();
		if( Yii::app()->user->checkAccess('getSpeedmodeReport') ){
			$to = $this->nextDay($to);
			$this->module->db->setDatabase($this->module->mapDb);
			$recordCountByBort = $this->recordCountByBort($level, $node, $from, $to);
			$recordCountBySpeedInterval = $this->recordCountBySpeedInterval($level, $node, $from, $to);
			$records = $this->calcSpeedmode($recordCountByBort, $recordCountBySpeedInterval);


			$this->acceptedBorts = $this->findAcceptedBorts($level, $node, $from, $to);
			// print_r($this->acceptedBorts); exit;

			$result['success'] = true;
			// $result['totalCount'] = $count->body->rows[0]->value;
			$result['rows'] = $this->formatResult($records);

		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}
		echo CJSON::encode($result);
	}
// ------------------------------------------------------------------------------

	protected function findAcceptedBorts ($level, $node, $from, $to) {
		$this->setVehicleParams($level, $node);
		$url = $this->buildBortScheduleUrl($from, $to);
		try {
			$bortSchedule = $this->module->db->get($url)->body->rows;
		} catch (SagException $e) {
			Yii::log($e->getMessage(), "error", "report.speedmode");
		}
		$result = array_filter($bortSchedule, array($this, 'filterAcceptedBorts'));
		$this->bortSchedule = $this->mapBortSchedule($result);
		$result = $this->mapAcceptedBorts($result);
		return $result;
	}

	protected function mapBortSchedule ($all) {
		$result = array();
		foreach ($all as $item) {
			$result[intval($item->key[1])] = intval($item->key[2]);
		}
		return $result;
	}

	protected function filterAcceptedBorts ($item) {
		if (!Borts::model()->findByPk($item->key[1])) {
			Yii::log("Не знайдено борт: " . $item->key[1], "warning", "report.speedmode");
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

/*		print_r($all);
		print_r($this->acceptedBorts);
		exit;*/

		foreach ($all as $item) {
			$result[$this->dateToStr($item->key[0])][] = intval($item->key[1]);
		}
		$result = array_unique($result);
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

		// print_r($records); exit;
		foreach ($data as $bort => $info) {
			$bort = Borts::model()->findByPk($bort);
			foreach ($info as $int => $val) {
				$result[] = array(
					'speed' => $int,
					'bort'  => $bort->number,
					'race' => 0,
					'graf' => 0,
					'speed_time_proz' => $val,
					'carrier' => $bort->park->carrier->name,
				);
			}
		}
		return $result;
	}

	protected function filter ($item) {
		// return isset($this->acceptedBorts[$this->dateToStr($item->key[0])]) ? in_array($item->key[1], $this->acceptedBorts[$this->dateToStr($item->key[0])]) : false;
		return true;
	}


// ------------------------------------------------------------------------------
	protected function recordCountBySpeedInterval ($level, $node, $from, $to) {
		$allByPeriodUrl = $this->buildCountBySpeedUrl($level, $node, $from, $to);
		try {
			$allRecord = $this->module->db->get($allByPeriodUrl);
		} catch (SagException $e) {
			Yii::log($e->getMessage(), "error", "report.speedmode");
		}
		$records = $this->sumRecordForPeriod ($allRecord->body->rows);
		$intervals = $this->buildSpeedCountByIntervals($records);
		return $intervals;
	}

	protected function sumRecordForPeriod ($records) {
		$res = array();
		foreach ($records as $item) {
			// print_r($item);
			$bort = $item->key[1];
			$speed = $item->key[2];
			if (isset($res[$bort][$speed])) {
				$res[$bort][$speed] += $item->value;
			} else {
				$res[$bort][$speed] = $item->value;
			}
		}
		return $res;
	}

	protected function buildSpeedCountByIntervals ($records) {

		$genericIntervals = array();
		$count = count($this->module->speedmode['intervals']);
		for ($i = 0; $i < $count - 1; $i++) {
			$genericIntervals[$this->module->speedmode['intervals'][$i] . '-' . $this->module->speedmode['intervals'][$i + 1]] = 0;
		}

		foreach ($records as $bort => $item) {
			$intervals[$bort] = array();

			foreach ($genericIntervals as $int => $val) {
				$intervals[$bort][$int] = 0;	
			}

			foreach ($item as $speed => $count) {
				($speed == -1) ? $speed = 0 : $speed; 
				// need optimization
				foreach ($genericIntervals as $key => $value) {
					if ($this->isBelongsToIntervals($speed, $key)) {
						// echo "speed: ".$speed."\n";
						// echo "int: ".$key."\n";
						// echo "val: ".$count."\n";
						// echo "bort: ".$bort."\n";
						// print_r($intervals[$bort]);
						$intervals[$bort][$key] += $count;
					}
				}
			}
		}
		return $intervals;
	}

	protected function isBelongsToIntervals ($speed, $intervals) {
		$limits = explode("-", $intervals);
		if ( $speed >= $limits[0] && $speed < $limits[1])
			return true;
		return false;
	}

	protected function calcSpeedmode ($recordCountByBort, $recordCountBySpeedInterval){
		$res = array();
		foreach ($recordCountBySpeedInterval as $bort => $item) {
			foreach ($item as $interval => $count) {
				$recordCountBySpeedInterval[$bort][$interval] = round( ($count / ($recordCountByBort[$bort] / 100)), 0);
			}
		}
		return $recordCountBySpeedInterval;
	}

	protected function recordCountByBort ($level, $node, $from, $to) {
		$allByPeriodUrl = $this->buildCountAllUrl($level, $node, $from, $to);
		try {
			$allRecord = $this->module->db->get($allByPeriodUrl);
		} catch (SagException $e) {
			Yii::log($e->getMessage(), "error", "report.speedmode");
		}
		$res = array();
		foreach ($allRecord->body->rows as $item) {
			$key = $item->key[1];
			if (isset($res[$key])) {
				$res[$key] += $item->value;
			} else {
				$res[$key] = $item->value;
			}
		}
		return $res;
	}

	protected function buildCountAllUrl($level, $node, $from, $to) {
		return $this->buildCountUrl($level, $node, $from, $to, 2);
	}

	protected function buildCountBySpeedUrl($level, $node, $from, $to) {
		return $this->buildCountUrl($level, $node, $from, $to, 3);
	}

	protected function buildCountUrl($level, $node, $from, $to, $groupLevel) {
		$from = implode('","', explode('-', $from));
		$to = implode('","', explode('-', $to));
		
		$url = "_design/speedmode/_view/count";
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

	protected function getSpeedCountByIntervals ($level, $node, $from, $to, $intervals) {
		$result = array();

		foreach ($intervals as $startSpeed => $endSpeed) {
			$url = $this->buildCountPeriodUrl($level, $node, $from, $to, $startSpeed, $endSpeed);
			// echo $url . "\n";
			$result[$startSpeed . "-" . $endSpeed] = $this->module->db->get($url)->body->rows[0]->value;
		}
		return $result;
	}

	private function nextDay($date) {
		$oneDay = new DateInterval('P1D');
		$date = new DateTime($date);
		$date = $date->add($oneDay);
		$date = $date->format('Y-m-d');
		return $date;
	}
}