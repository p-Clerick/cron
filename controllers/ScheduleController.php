<?php

class ScheduleController extends Controller
{
	public function actionRead($id){
		$schedule = Graphs::model()->findByPk($id);
		$route = $schedule->route;
		$type = $_GET['graphType'];

		$metaData = array(
			'idProperty' => 'flight',
			'root' => 'rows',
			'totalProperty' => 'results',
			'successProperty' => 'success',				
		);
		$fields[] = array(
			'name' => 'flight'
		);

		// Контрольні точки
		if($allControlPointsScenario = $route->controlPointScenarios){
			foreach($allControlPointsScenario as $ctlPointScenario){ 
				$ctrlPointIntervals = $ctlPointScenario->intervals; 
				$fields[] = array(
					'name' => $ctlPointScenario->id,
				);
			}

			$result = array(
				'success' => true,
				'rows' => array(),
			);
		   
		} else {
			$result = array(
				'success' => false,
				'msg' => Yii::app()->session['RouteHasNoControlPoints'],
			);
		}	

		$timeTable = $schedule->getCurrentSchedule($type);
		$rows = array();
		$dinners = array();
		$shifts = array();
		if( !empty($timeTable) ){
			$scheduleTimes = $timeTable->times;
			$items = array();
			foreach($scheduleTimes as $item){
				$time = new Time($item->time);
				$items[$item->flight_number][] = array( 
					'pointid' => $item->points_control_scenario_id,
					'time' => $time->getFormattedTime(),
				);
			}
			$i = 0;
			foreach($items as $flight_num => $times){
				$rows[$i]['flight'] = $flight_num;
				foreach($times as $time){
					$rows[$i][$time['pointid']] = $time['time'];
				}
				++$i;
			}
			$dinnersRaw = Dinner::model()->findAll("schedules_id = :sId", array(":sId"=>$timeTable->id));
			$shifts = Workshift::model()->findAll(array(
				"condition" => "schedule_id = :sId",
				"params" => array(":sId"=>$timeTable->id),
				"order" => "number",
			));

			foreach ($dinnersRaw as $item) {
				$start_time = new Time($item->start_time);
				$end_time = new Time($item->end_time);
				$dinners[] = array(
					"start_time" => $start_time->format(),
					"end_time" => $end_time->format(),
					"point_id" => $item->points_control_scenario_id,
					"flight_number" => $item->flight_number,
					"serial_number" => $item->number,
				);
			}

			if (count($shifts) > 1) {
				$end = new Time($shifts[0]->end_time);
				$start = new Time($shifts[1]->start_time);
				$shifts = array(
					"end" => $end->format(),
					"start" => $start->format(),
				);
			}
		}
		$result['rows'] = $rows;
		$result['dinners'] = $dinners;
		$result['shifts'] = $shifts;

		$metaData['fields'] = $fields;
		$result['metaData'] = $metaData;
		echo CJSON::encode($result);
	}

	public function actionUpdate(){
		$result = array(
			'success' => false
		);

		if( Yii::app()->user->checkAccess('changeSchedule') ){			
			parse_str(urldecode(stripslashes(file_get_contents('php://input'))), $received);
			
			$id = $received['grafikid'];
			$graphType = $received['graphType'];
			$flightData = CJSON::decode($received['rows']);
			$flightNum = $flightData['flight'];
			unset($flightData['flight']);

			$graph = Graphs::model()->findByPk($id);
			$schedule = $graph->getCurrentSchedule($graphType);

			$today = date('Y-m-d');
			if ($schedule->create_date != $today){
				// Create new schedule
				$prevSchedule = $schedule;
				$schedule = new Schedules;
				$schedule->graphs_id = $id;
				$schedule->create_date = $today;
				$schedule->schedule_types_id = $graphType;
				$schedule->save();
				$schedule->copyTimesFrom($prevSchedule);
			}
			// Update schedule times
			$times = $schedule->times;
			$timeStore = array();
			foreach($times as $time){
				if($time->flight_number == $flightNum){
					$timeStore[$time->points_control_scenario_id] = $time;
				}
			}

			$recordToSave = array();
			foreach($flightData as $controlPointScenarioId => $time){
				if(isset($timeStore[$controlPointScenarioId])){
					$nextTime = Time::factory($time)->getTimeInSeconds();
					if($timeStore[$controlPointScenarioId]->time != $nextTime){
						 $timeStore[$controlPointScenarioId]->time = $nextTime;
						 $recordToSave[] = $timeStore[$controlPointScenarioId];
					}
				}
			}

			foreach ($recordToSave as $time) {
				$time->save();
			}

			$rows['flight'] = $flightNum;
			foreach($timeStore as $item){
				$time = new Time($item->time);
				$rows[$item->points_control_scenario_id] = $time->getFormattedTime();
			}

			$result = array(
				"success" => true,
				"rows" => $rows,
			);
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}
		echo CJSON::encode($result);		
	}

	public function actionCreate($id){
		echo 'create';
	}

	public function actionDelete($id){
		$result = array();
		
		echo CJSON::encode($result);
	}
}