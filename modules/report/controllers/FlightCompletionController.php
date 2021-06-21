<?php

Yii::import('application.vendors.underscore.*');
require_once('underscore.php');

class FlightCompletionController extends CController
{
	public function actionRead($level, $node, $from, $to){
		$result = array();
		if( Yii::app()->user->checkAccess('getWorktimeReport') ){

			$routes = Yii::app()->db->createCommand()
				->select('routes.id as id, routes.name as name, COUNT(*) as count')
				->from('routes')
				->join('points_control_scenario as points', 'routes.id = points.routes_id')
				->group('id')
				->queryAll();

			$scheduleRoute = __::chain(Schedules::model()->findAll(array(
					'with' => array('graph.route')
				)))
				->filter(function ($item) {
					return $item->graph && $item->graph->route;
				})
				->map(function ($item) {
					return array(
						'schedule' => $item->id,
						'graph'    => $item->graph->id,
						'route'    => $item->graph->route->name
					);
				})
				->value();

			$borts = Borts::model()->findAll();
			$borts = array_combine(
				__($borts)->pluck('id'),
				__($borts)->pluck('number')				
			);

			$scheduleRoute = array_combine(
				__($scheduleRoute)->pluck('schedule'),
				__($scheduleRoute)->pluck('route')
			);

			$pointsCount = array_combine(
				__($routes)->pluck('name'),
				__($routes)->pluck('count')
			);

			$fields = implode(',', array(
				'item.borts_id as bort', 
				'item.flight_number as flight',
				'COUNT(*) as count',
				'item.schedules_id as schedule',
				'DATE(item.datatime) as date',
			));
			
			$items = Yii::app()->db->createCommand()
				->select($fields)
				->from('move_on_schedule_total as item')
				->where('DATE(datatime) BETWEEN :from AND :to', array(
					'from' => $from,
					'to'   => $to,
				))
				->group('date, bort, schedule, flight')
				->queryAll();

			$overallFlights = Yii::app()->db->createCommand()
				->select('schedules_id as schedule, flight_number as flight')
				->from('schedule_times')
				->group('schedule, flight')
				->queryAll();

			$groupedBySchedule = __($overallFlights)->groupBy('schedule');
			$flightCountBySchedule = __($groupedBySchedule)->map(function ($flights, $schedule) {
				return array(
					'schedule' => $schedule,
					'count' => __($flights)->size()
				);
			});

			$flightCountBySchedule = array_combine(
				__($flightCountBySchedule)->pluck('schedule'),
				__($flightCountBySchedule)->pluck('count')
			);

			define('COMPLETED_PERCENT', 50);

			$flights = __::chain($items)
				->map(function ($v) use ($scheduleRoute, $pointsCount, $borts) {
					$route     = isset($scheduleRoute[$v['schedule']]) ? $scheduleRoute[$v['schedule']] : -1;
					$bort      = isset($borts[$v['bort']]) ? $borts[$v['bort']] : -1;
					$completed = $v['count'];
					$all       = isset($pointsCount[$route]) ? $pointsCount[$route] : 0;
					$percent   = $all == 0 ? 0 : $completed * 100 / $all;
					return array(
						'bort'      => $bort,
						// 'route'     => $route,
						'flight'    => $v['flight'],
						'schedule'  => $v['schedule'],
						'completed' => $completed,
						'all'       => $all,
						'date'      => $v['date'],
						'percent'   => $percent,
						'completed' => $percent > COMPLETED_PERCENT
					);
				})
				->groupBy(function ($v) {
					return implode(':', array($v['date'], $v['schedule'], $v['bort']));
				});


			$result['rows'] = __($flights)->map(function ($v, $key) use ($flightCountBySchedule, $scheduleRoute) {
				$key = explode(':', $key);
				$schedule = $key[1];
				$completed = __($v)
					->filter(function ($v) {
						return $v['completed'];
					});
				$count = $flightCountBySchedule[$key[1]];
				$route = isset($scheduleRoute[$schedule]) ? $scheduleRoute[$schedule] : -1;
				
				return array(
					'date'       => $key[0],
					'schedule'   => $key[1],
					'bort'       => $key[2],
					'route'      => $route,
					'count'      => $count,
					'completed'  => __($completed)->size()
				);
			});

			if ($level == Tree::LEVEL_VEHICLE) {
				$group = function ($v) use($level) {
					return implode(':', array($v['route'], $v['bort']));
				};
			} else {
				$group = function ($v) use($level) {
					return implode(':', array($v['route']));
				};
			}

			$grouped = __($result['rows'])->groupBy($group);
			$result['rows'] = __::chain($grouped)->map(function ($v, $key) {
				$key = explode(':', $key);
				
				$count = __($v)->reduce(function ($acc, $v) {
					return $acc + $v['count'];
				}, 0);
				
				$completed = __($v)->reduce(function ($acc, $v) {
					return $acc + $v['completed'];
				}, 0);

				$percent = $completed * 100 / $count;
				
				$result = array(
					'route'      => $key[0],
					'count'      => $count,
					'completed'  => $completed,
					'completion' => round($percent),
				);

				if (isset($key[1])) {
					$result['bort'] = $key[1];
				}

				return $result;
			})->filter(function ($v) {
				return !__(array('100', '-1'))->includ($v['route']);
			})->value();
			
			$result['success'] = true;
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}
		echo CJSON::encode($result);
	}
}