<?php

Yii::import('application.vendors.underscore.*');
require_once('underscore.php');

class ReportMoveOnLineController extends CController
{
	public function actionRead()
	{




		$chart    = Yii::app()->request->getParam('chart');
		$level    = Yii::app()->request->getParam('level');
		$nodeId   = Yii::app()->request->getParam('recordIdLevel');
		$fromDate = Yii::app()->request->getParam('fromDate');
		$toDate   = Yii::app()->request->getParam('toDate');

		if (isset($_GET['fromReport'])) {
			$fd=Yii::app()->request->getParam('fromReport');
			$fromDate=$fromDate." ".$fd;
		}
		if (isset($_GET['toReport'])) {
			$td=Yii::app()->request->getParam('toReport');
			$toDate=$toDate." ".$td;
		}
		if (!isset($_GET['toReport'])) {
			$toDate=$toDate." "."23:59:59";
		}

		$newFrom = strtotime($fromDate);
		$newTo   = strtotime($toDate);

		$rows = array();

		if ($level == Tree::LEVEL_VEHICLE) {
			if(Yii::app()->user->name != "guest"){
	            $carrier = Yii::app()->user->checkUser(Yii::app()->user);
	        }
	        if ($carrier) {
	        	$a = LocationsFlight::model()->with('route','stations','graph','bort')->findAll(array(
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND route.carriers_id = :carrier',
					'select'   => 't.unixtime, t.time_difference, t.arrival_plan, t.flights_number',
					'params'   =>array(':f'=>$newFrom, ':t'=>$newTo, ':carrier'=>$carrier['carrier_id'])));

				foreach ($a as $k) {
					if ($k->route->carriers_id==$carrier['carrier_id']) {
						if ($k->arrival_plan!=null) {
							$arrPlan= new Time($k->arrival_plan);
						}
						if ($k->arrival_plan==null) {
							$arrPlan= new Time('00000');
						}
						$rows[]=array(
							//'id'=>$k->id,
							//'routeId'=>$k->routes_id,
							//'graphId'=>$k->graphs_id,
							//'bortId'=>$k->borts_id,
							'bortNumber'=>$k->bort->number,
							'bortNameState'=>$k->bort->state_number,
							//'stationsId'=>$k->stations_id,
							'flightNumber'=>$k->flights_number,
							'arrival'=>strftime('%Y-%m-%d %H:%M:%S',$k->unixtime),
							'arrivalTimePlan'=>$arrPlan->getFormattedTime(),
							'timeDifference'=>$k->time_difference,
							'routeName'=>$k->route->name,
							'stationsName'=>$k->stations->name,
							'graphName'=>$k->graph->name
						);
					}
				}
	        } else {

	        	
				$a = LocationsFlight::model()->with('route','stations','graph','bort')->findAll(array(
					'condition'=> 'unixtime >= :f AND unixtime <= :t',
					'select'   => 't.unixtime, t.time_difference, t.arrival_plan, t.flights_number',
					'params'   =>array(':f'=>$newFrom, ':t'=>$newTo)));
				foreach ($a as $k) {
					if ($k->arrival_plan!=null) {
						$arrPlan= new Time($k->arrival_plan);
					}
					if ($k->arrival_plan==null) {
						$arrPlan= new Time('00000');
					}
					$rows[]=array(
						//'id'=>$k->id,
						//'routeId'=>$k->routes_id,
						//'graphId'=>$k->graphs_id,
						//'bortId'=>$k->borts_id,
						'bortNumber'=>$k->bort->number,
						'bortNameState'=>$k->bort->state_number,
						//'stationsId'=>$k->stations_id,
						'flightNumber'=>$k->flights_number,
						'arrival'=>strftime('%Y-%m-%d %H:%M:%S',$k->unixtime),
						'arrivalTimePlan'=>$arrPlan->getFormattedTime(),
						'timeDifference'=>$k->time_difference,
						'routeName'=>$k->route->name,
						'stationsName'=>$k->stations->name,
						'graphName'=>$k->graph->name
					);
				}
			}	
		}
		if ($level == Tree::LEVEL_ROUTE) {
			$a = LocationsFlight::model()->with('route','stations','graph','bort')->findAll(array(
				'condition'=> 't.routes_id = :rhid AND unixtime >= :f AND unixtime <= :t',
				'select'   => 't.unixtime, t.time_difference, t.arrival_plan, t.flights_number',
				'params'   => array(':rhid' => $nodeId, ':f'=>$newFrom, ':t'=>$newTo)));
			foreach ($a as $k) {
				if ($k->arrival_plan!=null) {
					$arrPlan= new Time($k->arrival_plan);
				}
				if ($k->arrival_plan==null) {
					$arrPlan= new Time('00000');
				}
				$rows[]=array(
						//'id'=>$k->id,
						//'routeId'=>$k->routes_id,
						//'graphId'=>$k->graphs_id,
						//'bortId'=>$k->borts_id,
						'bortNumber'=>$k->bort->number,
						'bortNameState'=>$k->bort->state_number,
						//'stationsId'=>$k->stations_id,
						'flightNumber'=>$k->flights_number,
						'arrival'=>strftime('%Y-%m-%d %H:%M:%S',$k->unixtime),
						'arrivalTimePlan'=>$arrPlan->getFormattedTime(),
						'timeDifference'=>$k->time_difference,
						'routeName'=>$k->route->name,
						'stationsName'=>$k->stations->name,
						'graphName'=>$k->graph->name
				);
			}
		}
		if ($level == Tree::LEVEL_SCHEDULE) {
			$records = LocationsFlight::model()->with('route','stations','graph','bort')->findAll(array(
				'condition'=> 't.graphs_id = :rhid AND unixtime >= :f AND unixtime <= :t',
				'select'   => 't.unixtime, t.time_difference, t.arrival_plan, t.flights_number',
				'params' => array(
					':rhid' => $nodeId,
					':f' => $newFrom,
					':t' => $newTo
				)
			));


			$rows = __($records)->map(function ($item) {
				$arrPlan = $item->arrival_plan ? new Time($item->arrival_plan) : new Time('00000');
				return array(
					//'id'              => $item->id,
					//'routeId'         => $item->routes_id,
					//'graphId'         => $item->graphs_id,
					//'bortId'          => $item->borts_id,
					'bortNumber'      => $item->bort->number,
					'bortNameState'   => $item->bort->state_number,
					//'stationsId'      => $item->stations_id,
					'flightNumber'    => $item->flights_number,
					'arrival'         => strftime('%Y-%m-%d %H:%M:%S',$item->unixtime),
					'unixtime'        => $item->unixtime,
					'arrivalTimePlan' => $arrPlan->getFormattedTime(),
					'timeDifference'  => $item->time_difference,
					'routeName'       => $item->route->name,
					'stationsName'    => $item->stations->name,
					'graphName'       => $item->graph->name
				);
			});
		}

//print_r($rows);
		//СЃРѕСЂС‚СѓРІР°РЅРЅСЏ Р·Р° РјР°СЂС€СЂ РіСЂР°С„ СЂРµР№СЃ РґР°С‚Р°
		function sortArrayMy ($a,$b) {
			if ( $a['routeName'] == $b['routeName'] ) {
				if ( $a['graphName'] == $b['graphName'] ) {
					if ( $a['flightNumber'] == $b['flightNumber'] ) {
						if ( $a['arrival'] == $b['arrival'] ) {
							return 0;
						}
						if ( $a['arrival'] > $b['arrival'] ) {
							return 1;
						}
						if ( $a['arrival'] < $b['arrival'] ) {
							return -1;
						}
					}
					if ( $a['flightNumber'] > $b['flightNumber'] ) {
						return 1;
					}
					if ( $a['flightNumber'] < $b['flightNumber'] ) {
						return -1;
					}
				}
				if ( $a['graphName'] < $b['graphName'] ) {
					return -1;
				}
				if ( $a['graphName'] > $b['graphName'] ) {
					return 1;
				}
			}
			else if ( $a['routeName'] < $b['routeName'] ) {
				return -1;
			}
			else {
				return 1;
			}

		}
		if (isset($rows)) {
			usort($rows, "sortArrayMy");
		}

		$rows = __($rows)->map(function ($row) {
			$exp = explode(' ', $row['arrival']);
			$row['arrivalTime'] = $exp[1];
			//row['date'] = $exp[0];
			return $row;
		});

		if ($chart) {
			$chartRows = __($rows)->map(function ($item) {
				return array(
					'unixtime' => intval($item['unixtime']),
					'difference' => $item['timeDifference'],
					'name' => $item['stationsName'],
				);
			});

			$result = array('success' => true, 'rows' => $chartRows);
		} else {
			$result = array('success' => true, 'rows' => $rows);
		}

		echo CJSON::encode($result);
	}
}
?>