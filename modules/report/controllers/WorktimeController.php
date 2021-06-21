<?php

Yii::import('application.vendors.underscore.*');
require_once('underscore.php');

class WorktimeController extends CController
{
	public function actionRead($level, $node, $from, $to){
		$result = array();
		if( Yii::app()->user->checkAccess('getWorktimeReport') ){
			Yii::beginProfile('[worktime:request]');
			$count_url = $this->buildCountUrl($level, $node, $from, $to);
			$this->module->db->setDatabase($this->module->scheduleDb);
			try {
				$count = $this->module->db->get($count_url);
			} catch (Exception $e) {
				Yii::log($e->getMessage(), "error", "report.worktime");
				throw $e;
			}

			$borts   = Borts::model()->findAll(array('select'=>'id, number'));
			$ids     = __($borts)->pluck('id');
			$numbers = __($borts)->pluck('number');
			$borts   = array_combine($ids, $numbers);

			// Very slow. Optimize!
			Yii::beginProfile('schedules');
			$schedules = Schedules::model()
				->with('timeCount', 'graph')
				->findAll();

			Yii::beginProfile('schedules relation');
			$schedules = __::chain($schedules)
				->filter(function ($item) {
					return __::isObject($item->graph);
				})
				->map(function ($item) {
					return array(
						'id'             => $item->id,
						'graph_id'       => $item->graph->id,
						'graph_name'     => $item->graph->name,
						'route_id'       => $item->graph->route->id,
						'route_name'     => $item->graph->route->name,
						'vehicletype_id' => $item->graph->route->vehicletype->id,
						'timeCount'      => $item->timeCount,
					);
				})
				->groupBy('id');
			Yii::endProfile('schedules relation');
			Yii::endProfile('schedules');

			$all = __::chain($count->body->rows)
				->filter(function ($item) use($schedules, $borts) {
					return __($schedules)->has($item->key[2]) && __($borts)->has($item->key[1]);
				})
				->map(function ($item) use ($borts, $schedules) {
					$schedule = $schedules[$item->key[2]][0];
					$bort = $borts[intval($item->key[1])];
					return array(
						'date' => implode($item->key[0], "-"),
						'bort' => $bort,

						'schedule'   => $schedule['id'],
						'graph_id'   => $schedule['graph_id'],
						'graph_name' => $schedule['graph_name'],

						'route_id'       => $schedule['route_id'],
						'route_name'     => $schedule['route_name'],
						'vehicletype_id' => $schedule['vehicletype_id'],

						'count'      => $item->value,
						'all_count'  => $schedule['timeCount'],
						'percent'    => ceil($item->value * 100 / $schedule['timeCount']),
					);
				});

			$filterProperties = array(
				Tree::LEVEL_VEHICLE  => 'vehicletype_id',
				Tree::LEVEL_ROUTE    => 'route_id',
				Tree::LEVEL_SCHEDULE => 'graph_id',
			);

			$filterValue = $node;
			$property = $filterProperties[$level];

			$byBort = $all
				->filter(function ($item) use($property, $filterValue) {
					return $item[$property] == $filterValue;
				})
				->groupBy(function ($v) {
					return $v['bort'] . ':' . $v['route_name'];
				});


			$result['rows'] = __($byBort)
				->map(function ($v, $key) {
					$sum = __::chain($v)
						->map(function ($v) {
							return $v['percent'];
						})
						->reduce(function ($result, $next) {
							return $result + $next;
						})
						->value();

					$bortRoute = explode(':', $key);
					return array(
						'bort'       => $bortRoute[0],
						'route_name' => $bortRoute[1],
						'percent'    => round($sum / count($v), 1),
					);
				});

				Yii::endProfile('[worktime:request]');
			$result['success'] = true;
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}
		echo CJSON::encode($result);
	}

	private function buildCountUrl ($level, $node, $from, $to, $staleRequest = TRUE) {
		$baseUrl = '_design/worktime/_view/by_date_bort_schedule';

		$from = join('","', explode('-', $from));
		$to   = join('","', explode('-', $this->nextDay($to)));

		$query = array(
			'startkey'    => '[["'.$from.'"]]',
			'endkey'      => '[["'.$to.'"]]',
			'reduce'      => 'true',
			'group_level' => 3,
		);

		if ($staleRequest) $query['stale'] = 'ok';

		return $url = join('?', array($baseUrl, http_build_query($query)));
	}

	private function nextDay($date) {
		$oneDay = new DateInterval('P1D');
		$date   = new DateTime($date);
		return $date->add($oneDay)->format('Y-m-d');
	}
}