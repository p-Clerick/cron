<?php

class CalculateGrafikController extends Controller
{	
	/**
	 * Завантаження початкових даних для форми генерації графіка
	 * @param int $id Ідентифікатор графіка
	 */
	public function actionRead($id){
		$result = array();
		if( Yii::app()->user->checkAccess('getInitialParamsForCalculation') ){
			$graphType = $_GET['graphType'];

			$graph = Graphs::model()->findByPk($id);
			$schedule = $graph->getCurrentSchedule($graphType);
			if ($schedule) {
				$from = new Time($schedule->startup_time);
				$result = array(
					'success' => true,
					'data' => array(
						'from' => $from->getFormattedTime(),
						'ctrlPointId' => $schedule->startup_ctrl_point_scen_id,
						'time_to_dinner' => $schedule->time_to_dinner / (60 * 60),
						'dinner_duration' => $schedule->dinner_duration / 60,
						'dinner_in_final' => $schedule->is_dinner_in_final,
						'dinnerCtrlPointId' => $schedule->dinner_ctrl_point_scen_id,
						'end_in_final' => $schedule->is_end_in_final,
						'endCtrlPointId' => $schedule->end_ctrl_point_scen_id,
					)
				);
				$workshifts = $schedule->workshifts;
				$result['data']['shiftCount'] = count($workshifts);
				foreach ($workshifts as $item) {
					$result['data']['workshift'.$item->number] = $item->duration_limit / (60 * 60);
				}			
			} else {
				$result = array(
					'success' => true,
					'data' => array()
				);	
			}
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}	
		echo CJSON::encode($result);
	}

	/**
	 * Формує розклад руху (часи, в які маршрутка повинна бути у відповідній
	 * конрольній точці)
	 * @param int $id Ідентифікатор графіка, для якого необхідно розрахувати розклад
	 */
	public function actionCreate ($id) {
		$result = array();
		if( Yii::app()->user->checkAccess('calculateGrafik') ){
			$requestData = $this->parseRequest($_POST);			
			$grafik = Graphs::model()->findByPk($id);			
			$today = date("Y-m-d");

			$startTime = Time::factory($requestData['from']);
			$startControlPointScenario = 
					ControlPointScenario::model()->findByPk($requestData['ctrlPointScenarioId']);

			$workDuration = new Time(60 * 60 * $requestData['shifts'][0]['duration']); 
			// $workDuration = new Time(60 * 60 * $requestData['worktime']); 
			$timeToDinner = new Time(60 * 60 * $requestData['timeToDinner']);
			$dinnerDuration = new Time(60 * $requestData['dinnerDuration']);
			
			$prevTimeTable = $grafik->getScheduleByDate($today, $requestData['graphType']);

			$timeTable = new Schedules;
			
			$timeTable->graphs_id = $id;
			$timeTable->status = 'yes';
			$timeTable->create_date = $today;
			$timeTable->schedule_types_id = $requestData['graphType'];
			$timeTable->startup_time = $startTime->getTimeInSeconds();
			$timeTable->startup_ctrl_point_scen_id = $startControlPointScenario->id;
			// $timeTable->worktime = $workDuration->getTimeInSeconds();
			$timeTable->time_to_dinner = $timeToDinner->getTimeInSeconds();
			$timeTable->dinner_duration = $dinnerDuration->getTimeInSeconds();
			if ($requestData['dinnerParams']['isInFinalCtrlPoints']) {
				$timeTable->is_dinner_in_final = 1;
				$timeTable->dinner_ctrl_point_scen_id = -1;
			} else {
				$timeTable->is_dinner_in_final = 0;
				$timeTable->dinner_ctrl_point_scen_id = $requestData['dinnerParams']['ctrlPoint'];
			}
			if ($requestData['endParams']['isInFinalCtrlPoints']) {
				$timeTable->is_end_in_final = 1;
				$timeTable->end_ctrl_point_scen_id = -1;
			} else {
				$timeTable->is_end_in_final = 0;
				$timeTable->end_ctrl_point_scen_id = $requestData['endParams']['ctrlPoint'];
			}

			$timeTable->save();
			foreach ($requestData['shifts'] as $item) {
				$temp = new Workshift;
				$temp->schedule_id = $timeTable->id;
				$temp->number = $item['number'];
				$temp->duration_limit = $item['duration'] * (60 * 60);
				$temp->save();
			}

			$workshifts = $timeTable->workshifts;

			try {
				$result = $timeTable->calculateSchedule($startTime, $workshifts, 
					$startControlPointScenario, $timeToDinner, $dinnerDuration, $requestData['dinnerParams'],
					$requestData['endParams']);
				
				// Видаляємо попередній розклад руху, якщо він створений сьогодні
				if ($prevTimeTable && $prevTimeTable->create_date == date("Y-m-d")){
					ScheduleTimes::model()->deleteAll('schedules_id = :id', array(':id' => $prevTimeTable->id));
					Workshift::model()->deleteAll('schedule_id = :sId', array(':sId' => $prevTimeTable->id));
					$prevTimeTable->delete();
				}
			} catch (CException $e) {
				// Видаляємо щойно сформований розклад руху
				Workshift::model()->deleteAll('schedule_id = :sId', array(':sId' => $timeTable->id));
				$timeTable->delete();
				$result = array(
					'success' => false,
					'msg' => $e->getMessage(),
				);
				echo CJSON::encode($result);
				Yii::log(Yii::app()->session['CalculationOfTheGraph'].$e->getMessage(), "error", "app.controllers.CalculateGrafik");
				Yii::app()->end();
			}

			$times = $result['times'];
			$dinners = $result['dinners'];

			foreach ($dinners as $item) {
				$item->save();

				/*echo '------------------------------------------------------'."\n";
				$temp = new Time($item->start_time);
				echo 'Від: '.$temp->getFormattedTime();
				echo "\n";
				$temp = new Time($item->end_time);
				echo 'До: '.$temp->getFormattedTime();
				echo "\n";*/
			}

			foreach ($times as $time){
				$time->save();

				/*echo '------------------------------------------------------'."\n";
				$temp = new Time($time->time);
				echo 'Рейс: '.$time->flight_number;
				echo "   ";
				$point = ControlPointScenario::model()->findByPk($time->points_control_scenario_id);
				echo 'Контрольна точка: '.$point->number;
				echo "   ";
				echo 'time: '.$temp->getFormattedTime();
				echo "\n";*/
			}

			$result = array(
				'success' => true,
			);
			
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}	
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

			foreach ($flightData as $key => $value) {
				$ctrlPointScenId = $key;
				$startTime = Time::factory($value);
			}

			$graph = Graphs::model()->findByPk($id);
			$prevSchedule = $graph->getCurrentSchedule($graphType);
			$startControlPointScenario = ControlPointScenario::model()->findByPk($ctrlPointScenId);

			try {
				$scheduleTimeBeforeEditing = $prevSchedule->getScheduleTime($flightNum, $ctrlPointScenId);
			} catch (CException $e) {
				$result = array(
					'success' => false,
					'msg' => $e->getMessage(),
				);
				echo CJSON::encode($result);
				Yii::app()->end();
			}
			
			$prevScheduleTime = $prevSchedule->getPrevScheduleTime($scheduleTimeBeforeEditing, $startControlPointScenario);
			if ($prevScheduleTime && Time::compare($startTime, $prevScheduleTime->getTime()) == 0) {
				$result = array(
					'success' => false,
					'msg' => Yii::app()->session['IntroducedLessThanPreviousTimeInSchedule'],
				);
				echo CJSON::encode($result);
				Yii::log(Yii::app()->session['ChangeSchedule'].$e->getMessage(), "error", "app.controllers.CalculateGrafik");
				Yii::app()->end();
			}
			
			$schedule = new Schedules;
			$schedule->save();
			$schedule->copyWorkshiftsAndInitParams($prevSchedule);
			$schedule->save();

			$workshifts = $schedule->getWorkshiftsAfterTime($scheduleTimeBeforeEditing);
			$timeToDinner = new Time($schedule->time_to_dinner);
			$dinnerDuration = new Time($schedule->dinner_duration);
			$dinnerParams = array(
				'isInFinalCtrlPoints' => $schedule->is_dinner_in_final ? true: false,
				'ctrlPoint' => $schedule->dinner_ctrl_point_scen_id ? 
					$schedule->dinner_ctrl_point_scen_id :
					false
			);
			$endParams = array(
				'isInFinalCtrlPoints' => $schedule->is_end_in_final ? true: false,
				'ctrlPoint' => $schedule->end_ctrl_point_scen_id ? 
					$schedule->end_ctrl_point_scen_id :
					false
			);

			$initialParams = $prevSchedule->getInitialParams($startTime, $prevScheduleTime, $flightNum, $startControlPointScenario, $schedule, $scheduleTimeBeforeEditing);

			try {
				$result = $schedule->calculateSchedule($startTime, $workshifts, 
					$startControlPointScenario, $timeToDinner, $dinnerDuration, $dinnerParams, $endParams, $initialParams);
				
				$times = $result['times'];
				$dinners = $result['dinners'];

				/*if ($schedule->create_date != $today) {
					// Create new schedule
					$prevSchedule = $schedule;
					$schedule = new Schedules;
					$schedule->graphs_id = $id;
					$schedule->create_date = $today;
					$schedule->schedule_types_id = $graphType;
					$schedule->save();
				}*/
				foreach ($dinners as $item) {
					$item->save();

					/*echo '------------------------------------------------------'."\n";
					$temp = new Time($item->start_time);
					echo 'Від: '.$temp->getFormattedTime();
					echo "\n";
					$temp = new Time($item->end_time);
					echo 'До: '.$temp->getFormattedTime();
					echo "\n";*/
				}
				foreach ($times as $time) {
					$time->save();

					/*echo '------------------------------------------------------'."\n";
					$temp = new Time($time->time);
					echo 'Рейс: '.$time->flight_number;
					echo "   ";
					$point = ControlPointScenario::model()->findByPk($time->points_control_scenario_id);
					echo 'Контрольна точка: '.$point->number;
					echo "   ";
					echo 'time: '.$temp->getFormattedTime();
					echo "    ";
					echo "Розклад руху: ".$time->schedules_id;
					echo "\n";*/
				}
				// $schedule->delete();
				$result = array(
					"success" => true,
					// "rows" => $rows,
				);
				
			} catch (CException $e) {
				$schedule->clearAndDelete();
				$result = array(
					'success' => false,
					'msg' => $e->getMessage(),
				);
				echo CJSON::encode($result);
				Yii::log(Yii::app()->session['ChangeSchedule'].$e->getMessage(), "error", "app.controllers.CalculateGrafik");
				Yii::app()->end();
			}
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}
		echo CJSON::encode($result);		
	}

	public function actionDelete ($id, $type) {
		$result = array();
		if( Yii::app()->user->checkAccess('deleteCurrentSchedule') ){
			$grafik = Graphs::model()->findByPk($id);			
			$today = date("Y-m-d");
			$timeTable = Schedules::model()->find(
				"graphs_id = :gId AND create_date = :today AND schedule_types_id = :id", 
				array(
					':today'=>$today, 
					':id'=>$type,
					':gId' => $grafik->id,
				)
			);
			if( $timeTable != NULL){
				ScheduleTimes::model()->deleteAll('schedules_id = :id', array(':id' => $timeTable->id));
				$timeTable->delete();				
				$result = array(
					'success' => true,
				);
			} else {				
				$result = array(
					'success' => false,
					'msg' => Yii::app()->session['YouCanOnlyDeleteGraphicsCreatedToday']
				);
			}

		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}	
		echo CJSON::encode($result);
	}
	
	/**
	 * Розбирає вхідний запит, який містить дані про графік та обіди
	 * 
	 * @param Array $rawRequest Дані про графік та обіди 
	 * @return Array Упорядковані дані про графік та обіди 
	 */
	private function parseRequest($rawRequest){
		$orderedRequest = array(
			'from' => $rawRequest['from'],
			// 'worktime' => $rawRequest['worktime'],
			'ctrlPointScenarioId' => $rawRequest['ctrlPointId'],
			'graphType' => $rawRequest['graphType'],
			'timeToDinner' => $rawRequest['time_to_dinner'],
			'dinnerDuration' => $rawRequest['dinner_duration'],
		);

		if (isset($rawRequest['dinner_in_final'])) {
			$orderedRequest['dinnerParams']['isInFinalCtrlPoints'] = true;
		} else {			
			$orderedRequest['dinnerParams']['isInFinalCtrlPoints'] = false;
			$orderedRequest['dinnerParams']['ctrlPoint'] = $rawRequest['dinnerCtrlPointId'];
		}

		if (isset($rawRequest['end_in_final'])) {
			$orderedRequest['endParams']['isInFinalCtrlPoints'] = true;
		} else {			
			$orderedRequest['endParams']['isInFinalCtrlPoints'] = false;
			$orderedRequest['endParams']['ctrlPoint'] = $rawRequest['endCtrlPointId'];
		}
		for ($i = 1; $i <= $rawRequest['shiftCount']; ++$i) {
			$orderedRequest['shifts'][] = array(
				'number' => $i,
				'duration' => $rawRequest['workshift'.$i],
			);
		}

		return $orderedRequest;
	}
}