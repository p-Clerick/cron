<?php

Yii::import('application.vendors.underscore.*');
require_once('underscore.php');

class ReportStopsMoveOnLineController extends CController
{
	public function actionRead()
	{
		$level    = Yii::app()->request->getParam('level');
		$nodeId   = Yii::app()->request->getParam('recordIdLevel');
		$fromDate = Yii::app()->request->getParam('fromDate');
		$toDate   = Yii::app()->request->getParam('toDate');

		$metaData = array(
			'idProperty' => 'id',
			'root' => 'rows',
			'totalProperty' => 'results',
			'successProperty' => 'success',
		);

		$fields = array(
			array('name' => 'date'),
			array('name' => 'routeId'),
			array('name' => 'routeName'),
			array('name' => 'graphId'),
			array('name' => 'graphName'),
			array('name' => 'stationsId'),
			array('name' => 'stationsName'),
			array('name' => 'timeAverage', 'type' => 'float'),
		);

		$isDetailed = isset($_GET['detailsStops']) && $_GET['detailsStops'] != 0;
		$hasStop    = isset($_GET['PoesStopsNePopav'] ) && $_GET['PoesStopsNePopav'] != 0;
		$forChart   = Yii::app()->request->getParam('chart') == 1;

		if ($hasStop) {
			$records = ReportCachStops::model()->with('poe')->findAll(array(
				'condition'=> 'date >= :f AND date <= :t',
				'params'   => array(':f' => $fromDate, ':t' => $toDate),
				'order'    => 't.id'
			));

			$rows = __($records)->map(function ($item) {
				return array(
					'date'         => $item->date,
					'routeName'    => $item->longitude,
					'graphName'    => $item->latitude,
					'stationsName' => $item->poe->name,
					'timeAverage'  => $item->amount,
				);
			});

			$metaData['fields'] = $fields;

			$result = array(
				'success'  => true,
				'metaData' => $metaData,
				'rows'     => $rows
			);

			echo CJSON::encode($result);
		}

		if ($isDetailed) {
			$stationsId = Yii::app()->request->getParam('detailsStops');
			$fromDate   = strtotime($fromDate);
			$toDate     = strtotime($toDate) + 23*60*60 + 59*60 + 59;

			if ($level == Tree::LEVEL_SCHEDULE) {
				$records = LocationsFlight::model()
						->with('route','graph','stations')
						->findAll(array(
							'condition'=> 'stations_id = :stid AND graphs_id = :gr AND unixtime >= :f AND unixtime <= :t',
							'params' => array(
								':stid' => $stationsId,
								':gr'   => $nodeId,
								':f'    => $fromDate,
								':t'    => $toDate,
							),
							'order' => 'unixtime'
						));
					
				$rows = __($records)->map(function ($item) {
					$t  = strftime('%Y-%m-%d %H:%M:%S',$item->unixtime);
					$tt = explode(" ", $t);
					return array(
						'date'          => $t,
						'routeId'       => $item->routes_id,
						'routeName'     => $item->route->name,
						'graphId'       => $item->graphs_id,
						'graphName'     => $item->graph->name,
						'stationsId'    => $item->stations_id,
						'stationsName'  => $item->stations->name,
						'timeAverage'   => $item->time_difference,
						'datetime'      => $tt[1],
						'datedate'      => $tt[0],
						'arrivalPlan'   => $item->arrival_plan,
						'unixtime'      => $item->unixtime,
						'flightsNumber' => $item->flights_number
					);
				});

				$rows = __($rows)->sortBy(function ($item) {
					return $item['date'];
				});
			}

			if ($level == Tree::LEVEL_ROUTE) {
				$records = LocationsFlight::model()->with('route','graph','stations')->findAll(array(
					'condition'=> 'stations_id = :stid AND t.routes_id = :gr AND unixtime >= :f AND unixtime <= :t',
					'params'   => array(
						':stid' => $stationsId,
						':gr'=> $nodeId,
						':f'=>$fromDate,
						':t'=>$toDate
					),
					'order'    => 'unixtime'
				));

				$rows = __($records)->map(function ($item) use ($nodeId) {
					$t  = strftime('%Y-%m-%d %H:%M:%S',$item->unixtime);
					$tt = explode(" ", $t);
					return array(
						'date'          => $t,
						'routeId'       => $nodeId,
						'routeName'     => $item->route->name,
						'graphId'       => $item->graphs_id,
						'graphName'     => $item->graph->name,
						'stationsId'    => $item->stations_id,
						'stationsName'  => $item->stations->name,
						'timeAverage'   => $item->time_difference,
						'datetime'      => $tt[1],
						'datedate'      => $tt[0],
						'arrivalPlan'   => $item->arrival_plan,
						'unixtime'      => $item->unixtime,
						'flightsNumber' => $item->flights_number,
					);
				});
			}

			if ($level == Tree::LEVEL_VEHICLE) {
				if(Yii::app()->user->name != "guest"){
		            $carrier = Yii::app()->user->checkUser(Yii::app()->user);
		        }
		        if ($carrier) {
			        	$records = LocationsFlight::model()->with('route','graph','stations')->findAll(array(
						'condition'=> 'stations_id = :stid AND unixtime >= :f AND unixtime <= :t AND route.carriers_id = :car',
						'params'   => array(
							':stid' => $stationsId,
							':f' =>$fromDate,
							':t' =>$toDate,
							':car'=>$carrier['carrier_id']
						),
						'order'    => 'unixtime'
					));
		        }else {
			        	$records = LocationsFlight::model()->with('route','graph','stations')->findAll(array(
						'condition'=> 'stations_id = :stid AND unixtime >= :f AND unixtime <= :t',
						'params'   => array(
							':stid' => $stationsId,
							':f' =>$fromDate,
							':t' =>$toDate
						),
						'order'    => 'unixtime'
					));
		        }
				
				$rows = __($records)->map(function ($item) use ($nodeId) {
					$t  = strftime('%Y-%m-%d %H:%M:%S',$item->unixtime);
					$tt = explode(" ", $t);
					return array(
						'date'          => $t,
						'routeId'       => $nodeId,
						'routeName'     => $item->route->name,
						'graphId'       => $item->graphs_id,
						'graphName'     => $item->graph->name,
						'stationsId'    => $item->stations_id,
						'stationsName'  => $item->stations->name,
						'timeAverage'   => $item->time_difference,
						'datetime'      => $tt[1],
						'datedate'      => $tt[0],
						'arrivalPlan'   => $item->arrival_plan,
						'unixtime'      => $item->unixtime,
						'flightsNumber' => $item->flights_number,
					);
				});
			}

			$result = $forChart ? prepareForChart($rows, $metaData) : prepareForTable($rows, $metaData);
			echo CJSON::encode($result);
		} else {
			if ($level == Tree::LEVEL_VEHICLE) {
				//шукаемо всі відхилення
					$a = ReportStopsAverageBuses::model()->with('station')->findAll(array(
						'condition'=> 'date >= :f AND date <= :t',
						'order' => 'station.name',
						'params' => array(
							':f' => $fromDate,
							':t' => $toDate
						)
					));

					foreach ($a as $k) {
						$data[$k->stations_id][]=$k->average_deviation;
						$routeId=$nodeId;
						$routeName=Yii::app()->session['AllRoutes'];
						$graphName=Yii::app()->session['AllGrafiks'];
						$arrayStations[$k->stations_id]=$k->station->name;
					}
		        
				

				if (isset($data)) {
					foreach ($data as $stat => $value) {
						$countRecord=count($value);
						$sumRecord=array_sum($value);
						$middle=round($sumRecord/$countRecord,1);
						$rows[]=array(
							'date'=>$fromDate." - ".$toDate,
					        'routeName'=>$routeName,
					        'graphName'=>$graphName,
					        'stationsId'=>$stat,
					        'stationsName'=>$arrayStations[$stat],
					        'timeAverage'=>$middle
						);
					}
					$countRows=count($rows);
				}

			}

			if ($level == Tree::LEVEL_ROUTE) {
				//шукаемо всі відхилення
				$a = ReportStopsAverageRoutes::model()->with('route','station')->findAll(array(
					'condition'=> 't.routes_id = :rhid AND date >= :f AND date <= :t',
					'params'   => array(':rhid' => $nodeId, ':f'=>$fromDate, ':t'=>$toDate),
					//'order'    => 't.date'));
					'order'    => 'station.name'));
				foreach ($a as $k) {
					$data[$k->stations_id][]=$k->average_deviation;
					$routeId=$nodeId;
					$routeName=$k->route->name;
					$graphName=Yii::app()->session['AllGrafiks'];
					$arrayStations[$k->stations_id]=$k->station->name;
				}
				if (isset($data)) {
					foreach ($data as $stat => $value) {
						$countRecord=count($value);
						$sumRecord=array_sum($value);
						$middle=round($sumRecord/$countRecord,1);
						$rows[]=array(
							'date'=>$fromDate." - ".$toDate,
					        'routeId'=>$routeId,
					        'routeName'=>$routeName,
					        'graphName'=>$graphName,
					        'stationsId'=>$stat,
					        'stationsName'=>$arrayStations[$stat],
					        'timeAverage'=>$middle
						);
					}
					$countRows=count($rows);
				}
			}

			if ($level == Tree::LEVEL_SCHEDULE) {
				//шукаемо всі відхилення
				$a = ReportStopsAverageGraphs::model()->with('graph','route','station')->findAll(array(
					'condition'=> 't.graphs_id = :rhid AND date >= :f AND date <= :t',
					'params'   => array(':rhid' => $nodeId, ':f'=>$fromDate, ':t'=>$toDate),
					//'order'    => 't.date'));
					'order'    => 'station.name'));

				foreach ($a as $k) {
					$data[$k->stations_id][]=$k->average_deviation;
					$routeId=$k->routes_id;
					$routeName=$k->route->name;
					$graphName=$k->graph->name;
					$arrayStations[$k->stations_id]=$k->station->name;
				}
				if (isset($data)) {
					foreach ($data as $stat => $value) {
						$countRecord=count($value);
						$sumRecord=array_sum($value);
						$middle=round($sumRecord/$countRecord,1);
						$rows[]=array(
							'date'=>$fromDate." - ".$toDate,
					        'routeId'=>$routeId,
					        'routeName'=>$routeName,
					        'graphId'=>$nodeId,
					        'graphName'=>$graphName,
					        'stationsId'=>$stat,
					        'stationsName'=>$arrayStations[$stat],
					        'timeAverage'=>$middle
						);
					}
					$countRows=count($rows);
				}
			}

			if ($forChart) {
				$chartRows = __($rows)->map(function ($item) {
					return array(
						'difference' => $item['timeAverage'],
						'yLabel'     => $item['stationsName'],
					);
				});

				$chartRows = __($chartRows)->sortBy(function ($item) {
					return $item['difference'];
				});

				$result = array('success' => true, 'rows' => $chartRows);
			} else {
				$metaData['fields'] = $fields;
				$result = array(
					'metaData' => $metaData,
					'success' => true,
					'rows' => $rows,
				);
			}

			echo CJSON::encode($result);
		}
	}
}


function prepareForChart ($rows, $metaData) {
	$byArrivalTime = __($rows)->chain()
		->map(function ($item) {
			$time = new Time($item['arrivalPlan']);
			$item['arrivalPlan'] = $time->getFormattedTime();
			return $item;
		})
		->groupBy('arrivalPlan');

	$chartRows = __($byArrivalTime)->map(function ($value, $key) {
		$keys   = __($value)->map(function ($v) {
			$date = explode('-', $v['datedate']);
			$day = $date[2];
			return 'datedate' . $day;
		});

		$values = __($value)->pluck('timeAverage');

		array_push($keys, 'datetime');
		array_push($values, $key);

		return array_combine($keys, $values);
	});

	$fields = __($rows)
		->chain()
		->map(function ($v) {
			$date = explode('-', $v['datedate']);
			$day = $date[2];
			return array(
				'name'    => 'datedate' . $day,
				'display' => (int) $day,
			);
		})
		->uniq(function ($item) {
			return $item['name'];
		})
		->value();

	array_push($fields, array('name' => 'datetime'));

	$metaData['fields'] = $fields;

	return array(
		'metaData' => $metaData,
		'success'  => true,
		'rows'     => $chartRows,
	);
}

function prepareForTable ($rows, $metaData) {
	$rows = __($rows)->map(function ($item) {
		$item['date'] = sprintf('%s (%d '.Yii::app()->session['FlightTextSmall'].')', $item['date'], $item['flightsNumber']);
		return $item;
	});

	$metaData['fields'] = array(
		array('name' => 'date'),
		array('name' => 'routeId'),
		array('name' => 'routeName'),
		array('name' => 'graphId'),
		array('name' => 'graphName'),
		array('name' => 'stationsId'),
		array('name' => 'stationsName'),
		array('name' => 'timeAverage', 'type' => 'float'),
	);

	return array(
		'success'  => true,
		'metaData' => $metaData,
		'rows'     => $rows,
	);
}
?>