<?php

class Schedules extends CActiveRecord
{
	const TYPE_WORK = 1;
	const TYPE_HOLLYDAY = 2;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function relations(){
		return array(
			'graph' => array(self::BELONGS_TO, 'Graphs', 'graphs_id'),
			'moveonschedule'=>array(self::HAS_ONE, 'MoveOnSchedule', 'schedules_id'),
			'moveonmap'=>array(self::HAS_ONE, 'MoveOnMap', 'schedules_id'),
			'moveonmaptotal'=>array(self::HAS_ONE, 'MoveOnMapTotal', 'schedules_id'),
			'moveonscheduletotal'=>array(self::HAS_ONE, 'MoveOnScheduleTotal', 'schedules_id'),
			'scheduletime'=>array(self::HAS_MANY, 'ScheduleTimes', 'schedules_id'),
			//'order'=>array(self::HAS_ONE, 'Orders', 'schedules_id'),
			'times' => array(self::HAS_MANY, 'ScheduleTimes', 'schedules_id'),
			'timeCount' => array(self::STAT, 'ScheduleTimes', 'schedules_id'),
			'workshifts'=>array(self::HAS_MANY, 'Workshift', 'schedule_id', 'order' => 'number ASC'),
		);
	}

	/**
	 * Копіює всі часи з розкладу руху $source до поточного розкладу руху
	 * @param Schedules  $source Розклад руху, з якого копіюємо часи
	 */
	public function copyTimesFrom ($source) {
		$sourceTimes = $source->times;
		foreach($sourceTimes as $item){
			$time = new ScheduleTimes;
			$time->flight_number = $item->flight_number;
			$time->points_control_scenario_id = $item->points_control_scenario_id;
			$time->time = $item->time;
			$time->schedules_id = $this->id;
			$time->save();
		}
	}

	/**
	 * Копіює робочі зміни та початкові параметри з іншого розкладу руху.
	 * Використовується при редагуванні графіка.
	 *
	 * @param Schedules $sourceSchedule Розклад руху, з якого копіюються параметри
	 */
	public function copyWorkshiftsAndInitParams ($sourceSchedule) {
		$this->graphs_id = $sourceSchedule->graphs_id;
		$this->create_date = date("Y-m-d");
		$this->schedule_types_id = $sourceSchedule->schedule_types_id;
		$this->startup_time = $sourceSchedule->startup_time;
		$this->startup_ctrl_point_scen_id = $sourceSchedule->startup_ctrl_point_scen_id;
		$this->dinner_duration = $sourceSchedule->dinner_duration;
		$this->time_to_dinner = $sourceSchedule->time_to_dinner;
		$this->is_dinner_in_final = $sourceSchedule->is_dinner_in_final;
		$this->dinner_ctrl_point_scen_id = $sourceSchedule->dinner_ctrl_point_scen_id;
		$this->is_end_in_final = $sourceSchedule->is_end_in_final;
		$this->end_ctrl_point_scen_id = $sourceSchedule->end_ctrl_point_scen_id;

		foreach ($sourceSchedule->workshifts as $workshift) {
			$temp = new Workshift;
			$temp->schedule_id = $this->id;
			$temp->number = $workshift->number;
			$temp->duration_limit = $workshift->duration_limit;
			$temp->start_time = $workshift->start_time;
			$temp->duration = $workshift->duration;
			$temp->start_time = $workshift->start_time;
			$temp->end_time = $workshift->end_time;
			$temp->save();
		}
	}

	/**
	 * Повертає час початку руху
	 * @return ScheduleTimes 
	 */
	public function getFirstTime () {
		// будемо надіятись, що часи додаються в порядку зростання, 
		// від найменшого до найбільшого
		$time = ScheduleTimes::model()->find(array(
			'condition' => 'schedules_id = :sId', 
			'params' => array(':sId' => $this->id),
			'order' => 'id ASC',
			'limit' => 1
		));
		if(!$time) throw new CException('First time not found');
		return $time;
	}

	/**
	 * Повертає час закінчення руху
	 * @return ScheduleTimes
	 */
	public function getLastTime () {
		// будемо надіятись, що часи додаються в порядку зростання, 
		// від найменшого до найбільшого
		$time = ScheduleTimes::model()->find(array(
			'condition' => 'schedules_id = :sId', 
			'params' => array(':sId' => $this->id),
			'order' => 'id DESC',
			'limit' => 1
		));
		if(!$time) throw new CException('Last time not found');
		return $time;
	}

	/**
	 * Розрахунок розкладу руху для графіка
	 * 
	 * @param Time $startTime Початок графіка: прибуття маршрутки на перший рейс
	 * @param Time $workshifts Робочі зміни
	 * @param ControlPointScenario $firstControlPoint Контрольна точка, з якої починається рух
	 * @param Time $timeToDinnerLimit Кількість напрацьованих годин до обіду
	 * @param Time $dinnerDuration Тривалість обіду
	 * @param array $dinnerParams Параметри знаходження обіду: 
	 * 		- isInFinalCtrlPoints - обіди в кінцевих контрольних точках
	 * 		- ctrlPoint - точка контролю обіду
	 * @param array $endParams Параметри закінчення графіка: 
	 * 		- isInEndCtrlPoints - закінчення графіка в кінцевих контрольних точках
	 * 		- endCtrlPoint - контрольна точка закінчення  графіка
	 * @return array Результат обчислень:
	 * 		['times'] - часи розкладу
	 * 		['dinners'] - розраховані обіди
	 */
	public function calculateSchedule ($startTime, $workshifts, $firstControlPoint, $timeToDinnerLimit, $dinnerDuration, $dinnerParams, $endParams, $initialParams = array()) {

		if (empty($initialParams)) {
			$totalScheduleTimes = array();
			$totalScheduleDinners = array();			

			$flightnum = 1;

			$firstScheduleTime = new ScheduleTimes;
			$firstScheduleTime->time = $startTime->getTimeInSeconds();
			$firstScheduleTime->flight_number = $flightnum;
			$firstScheduleTime->schedules_id = $this->id;
			$firstScheduleTime->points_control_scenario_id = $firstControlPoint->id;

			$totalScheduleTimes[] = $firstScheduleTime;

			$workDuration = 0;
			$dinnerDurationAmount = 0;
			$dinnerNumber = 1;
			$firstWorkshiftTime = $firstScheduleTime->getTime()->getTimeInSeconds();
		} else {
			$totalScheduleTimes = $initialParams['totalScheduleTimes'];
			$totalScheduleDinners = $initialParams['totalScheduleDinners'];

			$flightnum = $initialParams['flightnum'];

			$firstScheduleTime = $initialParams['firstScheduleTime'];

			$workDuration = $initialParams['workDuration']->getTimeInSeconds();
			$dinnerDurationAmount = $initialParams['worktimeFromPrevDinner']->getTimeInSeconds();
			$dinnerNumber = $initialParams['nextDinnerNumber'];
			if ($initialParams['isFirstTimeInSchedule']) {
				$firstWorkshiftTime = $startTime->getTimeInSeconds();
			} else {
				$firstWorkshiftTime = $workshifts[0]->start_time;				
			}
			Yii::trace('Редагування графіка. Початкові параметри: напрацьований час='.$initialParams['workDuration']->format().', напрацьований час до обіду='.$initialParams['worktimeFromPrevDinner']->format().', час початку розрахунку='.$firstScheduleTime->getTime()->format(), 'app.models.schedules');
		}

		
		
		foreach ($workshifts as $workshift) {
			$s = new Time($workshift->start_time);
			$e = new Time($workshift->end_time);
			$d = new Time($workshift->duration);
			Yii::trace('Робоча зміна редагується: id='.$workshift->id.", start=".$s->format().", end=".$e->format().", duration=".$d->format(), 'app.models.schedules');
			
			$workDurationLimit = $workshift->duration_limit;
			
			$result = $this->calculateScheduleTimesAndDinners ($workDuration, $workDurationLimit, 
				$dinnerDurationAmount, $timeToDinnerLimit, $firstScheduleTime, $firstControlPoint, 
				$dinnerDuration, $dinnerNumber, $dinnerParams, $endParams, $workshift);

			$workshift->duration = $result['duration']->getTimeInSeconds();
			$workshift->start_time = $firstWorkshiftTime;				
			$workshift->end_time = $result['lastScheduleTime']->getTime()->getTimeInSeconds();
			$workshift->save();

			$firstScheduleTime = $result['lastScheduleTime'];
			$firstWorkshiftTime = $firstScheduleTime->getTime()->getTimeInSeconds();
			$firstControlPoint = $result['lastCtrlPoint'];
			$totalScheduleTimes = array_merge($totalScheduleTimes, $result['times']);
			$totalScheduleDinners = array_merge($totalScheduleDinners, $result['dinners']);

			$workDuration = 0;
			$dinnerDurationAmount = 0;
			$dinnerNumber = 1;

			$s = new Time($workshift->start_time);
			$e = new Time($workshift->end_time);
			$d = new Time($workshift->duration);
			Yii::trace('Перерахована робоча зміна: id='.$workshift->id.", start=".$s->format().", end=".$e->format().", duration=".$d->format(), 'app.models.schedules');
		}

		
		return array(
			'times' => $totalScheduleTimes,
			'dinners' => $totalScheduleDinners,
		);
	}

	/**
	 * Розрахунок розкладу руху для одної зміни
	 * 
	 * @param int $workDuration Поточна тривалість напрацьованого часу
	 * @param int $workDurationLimit Ліміт тривалості напрацьованого часу
	 * @param int $timeToDinnerDurationAmount Напрацьований час, що пройшов від 
	 * початку розрахунку графіка або з попереднього обіду
	 * @param Time $timeToDinnerLimit Кількість напрацьованих годин до обіду
	 * @param ScheduleTimes firstScheduleTime Початкий час розрахунку
	 * @param ControlPointScenario $firstControlPoint Контрольна точка, з якої починається рух
	 * @param Time $dinnerDuration Тривалість обіду
	 * @param int $dinnerNumber Порядковий номер обіду для поточної зміни поточного розкладу руху
	 * @param array $dinnerParams Параметри знаходження обіду: 
	 * 		- isInFinalCtrlPoints - обіди в кінцевих контрольних точках
	 * 		- ctrlPoint - точка контролю обіду
	 * @param array $endParams Параметри закінчення графіка: 
	 * 		- isInEndCtrlPoints - закінчення графіка в кінцевих контрольних точках
	 * 		- endCtrlPoint - контрольна точка закінчення  графіка
	 * @param Workshift $workshift Робоча зміна
	 * @return array Результат обчислень:
	 * 		['times'] - часи розкладу
	 * 		['dinners'] - розраховані обіди
	 *		['duration'] - напрацьований час
	 *		['lastScheduleTime'] - останній розрахований час розкладу
	 *		['lastCtrlPoint'] - точка контролю, що відповідає останньому розрахованому часу розкладу
	 *		['lastFlightnum'] - номер останнього розрахованого рейсу 
	 */
	public function calculateScheduleTimesAndDinners ($workDuration, $workDurationLimit, 
		$timeToDinnerDurationAmount, $timeToDinnerLimit, $firstScheduleTime, $firstControlPoint,
		$dinnerDuration, $dinnerNumber, $dinnerParams, $endParams, $workshift) {
		
		$ctrlPointScenarios = $this->graph->route->controlPointScenarios;
		$ctrlPointScenarios = $this->formatCtrlPointByNumber($ctrlPointScenarios);

		$times = array(); // розраховані часи розкладу
		$dinners = array(); // обіди

		$prevTime = $firstScheduleTime->getTime();
		$prevCtrlPointNumber = $firstControlPoint->number;
		$currCtrlPointNumber = $firstControlPoint->number + 1;
		$flightnum = $firstScheduleTime->flight_number;
		$nextScheduleTime = $firstScheduleTime;

		if ($dinnerNumber > 1) {
			$isNeedDinner = false;			
		} else {
			$isNeedDinner = true;
		}

		Yii::trace('Напрацьований час: '.$workDuration.", ліміт: ".$workDurationLimit, 'app.models.schedules');
		Yii::trace('Останній час зміни: '.$nextScheduleTime->getTime()->format(), 'app.models.schedules');

		while($workDuration < $workDurationLimit){
			if( $currCtrlPointNumber <= $this->lastCtrlPointScNumber($ctrlPointScenarios)){
				if ($this->isEnd($workDuration, new Time($workDurationLimit), $endParams, $ctrlPointScenarios[$currCtrlPointNumber], $prevTime)) {
					break;
				}

				try {
					$dayInt = DayInterval::searchDayIntByTime($this->graph->route, $prevTime);
				} catch (CException $e) {
					echo "{success: false, msg: 'Для часу <b>".$prevTime->getFormattedTime()."</b> не заданий період доби. Додайте період доби в налаштуваннях маршрута для цього часу.'} ";
					throw new CException('Не знайдено період доби');
				}

				$result = $this->calculateNextTime($prevTime, $dayInt, $prevCtrlPointNumber, $currCtrlPointNumber, $ctrlPointScenarios, $flightnum);
				
				$nextScheduleTime = $result['nextTime'];
				$interval = $result['interval'];
				$nextTime = $nextScheduleTime->getTime();
								 
				$workDuration += $interval;
				$timeToDinnerDurationAmount += $interval;
							
				if ($isNeedDinner && $this->isNeedDinner($timeToDinnerDurationAmount, $timeToDinnerLimit, $dinnerParams, $ctrlPointScenarios[$currCtrlPointNumber], $nextTime)) {
					$nextTime = Time::add($nextScheduleTime->getTime(), $dinnerDuration);
					$dinner = new Dinner;
					$dinner->number = $dinnerNumber;
					$dinner->schedules_id = $this->id;
					$dinner->flight_number = $flightnum;
					$dinner->points_control_scenario_id = $ctrlPointScenarios[$currCtrlPointNumber]->id;
					$dinner->start_time = $prevTime->getTimeInSeconds();
					$dinner->end_time = $nextTime->getTimeInSeconds();
					$dinner->duration = $dinnerDuration->getTimeInSeconds();
					$dinner->elapsed_worktime = $timeToDinnerDurationAmount;
					$dinner->workshift_id = $workshift->id;

					$dinners[] = $dinner;
					$timeToDinnerDurationAmount = 0;

					$nextScheduleTime = new ScheduleTimes;
					$nextScheduleTime->time = $nextTime->getTimeInSeconds();
					$nextScheduleTime->flight_number = $flightnum;
					$nextScheduleTime->schedules_id = $this->id;
					$nextScheduleTime->points_control_scenario_id = $ctrlPointScenarios[$currCtrlPointNumber]->id;

					++$dinnerNumber;
					$isNeedDinner = false;
				}
				$prevTime = $nextTime;
				$times[] = $nextScheduleTime;
				$prevCtrlPointNumber = $currCtrlPointNumber;
				++$currCtrlPointNumber;  
			} else {
				$prevCtrlPointNumber = $currCtrlPointNumber-1;
				$currCtrlPointNumber = 1;
				++$flightnum;
				continue;
			}
		}

		return array(
			'times' => $times,
			'duration' => new Time($workDuration),
			'dinners' => $dinners,
			'lastScheduleTime' => $nextScheduleTime,
			'lastCtrlPoint' => $ctrlPointScenarios[$prevCtrlPointNumber],
			'lastFlightnum' => $flightnum
		);
	}

	/**
	 * Розраховує наступний час за розкладом
	 * @param ScheduleTimes $prevTime Попереднай час за розкладом
	 * @param DayInterval $dayInt Попереднай час за розкладом
	 * @param int $prevCtrlPointNumber Порядковий номер попередньої контрольної точки
	 * @param int $currCtrlPointNumber Порядковий номер поточної контрольної точки
	 * @param array $ctrlPointScenarios Масив контрольних точок для маршрута
	 * @param int $flightnum Номер рейсу
	 * @return ScheduleTimes Час, наступний по розкладу
	 */
	protected function calculateNextTime ($prevTime, $dayInt, $prevCtrlPointNumber, $currCtrlPointNumber, $ctrlPointScenarios, $flightnum) {
		$interval = $ctrlPointScenarios[$prevCtrlPointNumber]
					->getControlPointInterval($dayInt)->interval;
		
		$int = new Time();
		$int->setSeconds($interval);

		$nextTime = new ScheduleTimes;
		$nextTime->time = Time::add($prevTime, $int)->getTimeInSeconds();
		$nextTime->flight_number = $flightnum;
		$nextTime->schedules_id = $this->id;
		$nextTime->points_control_scenario_id = $ctrlPointScenarios[$currCtrlPointNumber]->id;

		return array(
			'nextTime' => $nextTime,
			'interval' => $interval,
		);
	}

	/**
	* Формує асоціативний масив контрольних точок, де в якості індекса 
	* служить номер сценарію контрольної точки в маршруті
	* 
	* @param $ctrlPointScenarios Array Масив сценаріїв контрольних точок
	* @return Array Відсортований асоціативний масив сценаріїв контрольних точок у форматі 
	*               
	*         array[serialNumber] = ctrlPoint
	*/
	protected function formatCtrlPointByNumber($ctrlPointScenarios){
		$res = array();
		foreach($ctrlPointScenarios as $point){
			$res[$point->number] = $point;
		}
		return $res;
	}

	protected function lastCtrlPointScNumber ($ctrlPointScenarios) {
		return count($ctrlPointScenarios);
	}

	/**
	 * Перевіряє умови закінчення графіка
	 *
	 * @param int $workDurationAmount Напрацьований час, с
	 * @param Time $workDurationLimit Час напрацювання до обіду
	 * @param array $endParams Параметри знаходження обіду: 
	 * 		- isInEndCtrlPoints - закінчення графіка в кінцевих контрольних точках
	 * 		- endCtrlPoint - контрольна точка закінчення  графіка
	 * @param ControlPointScenario $currCtrlPoint Поточка контрольна точка
	 * @param Time $currTime Поточний час розрахунку
	 * @return boolean Результат перевірки
	 */
	protected function isEnd ($workDuration, $workDurationLimit, $endParams, $currCtrlPoint, $prevTime) {
		return $this->isLastTimeInCalculation($workDuration, $workDurationLimit, $endParams, $currCtrlPoint, $prevTime);
	}

	/**
	 * Перевіряє умови додавання обіда
	 *
	 * @param int $dinnerDurationAmount Напрацьований час, с
	 * @param Time $timeToDinnerLimit Час напрацювання до обіду
	 * @param array $dinnerParams Параметри знаходження обіду: 
	 * 		- isInFinalCtrlPoints - обіди в кінцевих контрольних точках
	 * 		- ctrlPoint - точка контролю обіду
	 * @param ControlPointScenario $currCtrlPoint Поточка контрольна точка
	 * @param Time $currTime Поточний час розрахунку
	 * @return boolean Результат перевірки
	 */
	protected function isNeedDinner ($dinnerDurationAmount, $timeToDinnerLimit, $dinnerParams, $currCtrlPoint, $currTime) {
		return $this->isLastTimeInCalculation($dinnerDurationAmount, $timeToDinnerLimit, $dinnerParams, $currCtrlPoint, $currTime);
	}

	/**
	 * Перевіряє, чи вистачить ще часу для одного рейсу до наступної кінцевої точки маршруту
	 * або до конкретної точки
	 *
	 * @param int $currentDuration Напрацьований час, с
	 * @param Time $durationLimit Ліміт напрацювьованого часу
	 * @param array $config Параметри знаходження: 
	 * 		- isInFinalCtrlPoints - в кінцевих контрольних точках, інакше в якійсь конкретній точці
	 * 		- ctrlPoint - конкретна точка контролю
	 * @param ControlPointScenario $currCtrlPoint Поточка контрольна точка
	 * @param Time $currTime Поточний час розрахунку
	 * @return boolean Результат перевірки
	 */
	protected function isLastTimeInCalculation ($currentDuration, $durationLimit, $config, $currCtrlPoint, $currTime) {
		if ($config['isInFinalCtrlPoints']) {
			$result = $this->checkLastTimeInFinalCtrlPoint(new Time($currentDuration), $durationLimit, $currCtrlPoint, $currTime);
		} else {
			// шукаємо наступну контрольну точку, тому що при розрахунку
			// поточний розрахований час анулюється
			$ctrlPoint = ControlPointScenario::model()->findByPk($config['ctrlPoint']);
			$nextAfterDinnerCtrlPoint = $this->graph->route->getNextPoint($ctrlPoint);
			if ($currCtrlPoint->id == $nextAfterDinnerCtrlPoint->id) {
				$result = $this->checkLastTimeInSpecificCtrlPoint(new Time($currentDuration), $durationLimit, $currCtrlPoint, $currTime);
			} else {
				$result = false;
			}
		}
		return $result;
	}

	/**
	 * Перевіряє, чи вистачить ще часу для одного рейсу до наступної кінцевої точки маршруту
	 * 
	 * @param Time $currentDuration Напрацьований час, с
	 * @param Time $durationLimit Ліміт напрацювьованого часу
	 * @param ControlPointScenario $currCtrlPoint Поточка контрольна точка
	 * @param Time $currTime Поточний час розрахунку
	 * @return boolean Результат перевірки
	 */
	protected function checkLastTimeInFinalCtrlPoint ($currentDuration, $durationLimit, $currCtrlPoint, $currTime) {
		if ($this->graph->route->isFirstPointInFlight($currCtrlPoint)) {
			if ($this->checkLastByFlight($currentDuration, $durationLimit, $currCtrlPoint, $currTime)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Перевіряє, чи вистачить ще часу для одного рейсу до конкретної точки
	 * 
	 * @param Time $currentDuration Напрацьований час, с
	 * @param Time $durationLimit Ліміт напрацювьованого часу
	 * @param ControlPointScenario $ctrlPoint Контрольна точка
	 * @param Time $currTime Поточний час розрахунку
	 * @return boolean Результат перевірки
	 */
	protected function checkLastTimeInSpecificCtrlPoint ($currentDuration, $durationLimit, $ctrlPoint, $currTime) {		
		$timeBetweenDinnerCtrlPoint = $this->graph->route->getFlightDurationBySpecificPoint($ctrlPoint, $currTime);
		$expectedDuration = Time::add($currentDuration, $timeBetweenDinnerCtrlPoint);
		if ( !Time::compare($expectedDuration, $durationLimit) == 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Перевіряє чи необхідний обід на кінці рейсу
	 *
	 * @param Time $currentDuration Напрацьований час
	 * @param Time $durationLimit Ліміт напрацювьованого часу
	 * @param ControlPointScenario $ctrlPoint Кінцева контрольна точка маршрута, початок рейсу
	 * @param Time $currTime Поточний час розрахунку
	 * @return boolean
	 */
	protected function checkLastByFlight ($currentDuration, $durationLimit, $ctrlPoint, $currTime) {
		$flightDuration = $this->graph->route->getFlightDurationByPoint($ctrlPoint, $currTime);
		
		$expectedDuration = Time::add($currentDuration, $flightDuration);
		if ( !Time::compare($expectedDuration, $durationLimit) == 0 ) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Початкові параметри для редагування розкладу руху
	 *
	 * @param Time $time Час, від якого починається редагування розкладу руху
	 * @param SchduleTimes $prevScheduleTime
	 * @param int $flightnum Номер рейсу
	 * @param ControlPointScenario $ctrlPointScen Поточна контрольна точка
	 * @param Schedules $targetSchedule Розклад руху, якому будуть присвоєні початкові параметри
	 * @param ScheduleTimes $scheduleTimeBeforeEditing Первинний час розкладу руху,
	 * який замінюється на час $time
	 * @return array Початкові параметри
	 * 		totalScheduleTimes - масив часів розкладу, що йдуть перед $time згідно розкладу руху
	 * 		totalScheduleDinners - масив обідів для розкладу руху, які раніше часу $time
	 * 		flightnum Номер рейсу для часу, що редагується
	 * 		firstScheduleTime Позначає новий час, з якого розпочнеться розрахунку графіка 
	 * 		workDuration - напрацьований час з початку зміни
	 * 		worktimeFromPrevDinner - напрацьований час з останнього обіду або початку нової зміни
	 * 		nextDinnerNumber - порядковий номер наступного обіду
	 * 		isFirstTimeInSchedule - чи редагуєтья перший час графіка
	 */
	public function getInitialParams ($time, $prevScheduleTime, $flightnum, $ctrlPointScen, $targetSchedule, $scheduleTimeBeforeEditing) {
		$totalScheduleTimes = $this->getScheduleTimes($prevScheduleTime, $targetSchedule);
		$totalScheduleDinners = $this->getTotalDinners($time, $targetSchedule, $scheduleTimeBeforeEditing);
		
		
		$firstScheduleTime = new ScheduleTimes;		
		$firstScheduleTime->time = $time->getTimeInSeconds();
		$firstScheduleTime->flight_number = $flightnum;
		$firstScheduleTime->schedules_id = $targetSchedule->id;
		$firstScheduleTime->points_control_scenario_id = $ctrlPointScen->id;

		$totalScheduleTimes[] = $firstScheduleTime;

		if (!empty($totalScheduleDinners)){
			$lastDinner = $totalScheduleDinners[count($totalScheduleDinners) - 1];
			$nextDinnerNumber = $totalScheduleDinners[count($totalScheduleDinners) - 1]->number + 1;	
		} else {
			$lastDinner = false;
			$nextDinnerNumber = 1;
		}

		$isFirstTimeInSchedule = $this->isFirstTimeInSchedule($scheduleTimeBeforeEditing);
		if (!$isFirstTimeInSchedule) {
			$workDuration = $this->getWorkDurationByLastTime ($time, $totalScheduleTimes, $totalScheduleDinners, $scheduleTimeBeforeEditing);
			$worktimeFromPrevDinner = $this->getWorktimeFromPrevDinner($time, $lastDinner, $scheduleTimeBeforeEditing, $targetSchedule);
		} else {
			unset($totalScheduleTimes);
			$totalScheduleTimes[0] = new ScheduleTimes;
			$totalScheduleTimes[0]->copyFrom($scheduleTimeBeforeEditing, $targetSchedule);
			$totalScheduleTimes[0]->time = $time->getTimeInSeconds();
			$workDuration = new Time(0);
			$worktimeFromPrevDinner = new Time(0);
		}
		return array(
			'totalScheduleTimes' => $totalScheduleTimes,
			'totalScheduleDinners' => $totalScheduleDinners,
			'flightnum' => $flightnum,
			'firstScheduleTime' => $firstScheduleTime,
			'workDuration' => $workDuration,
			'worktimeFromPrevDinner' => $worktimeFromPrevDinner,
			'nextDinnerNumber' => $nextDinnerNumber,
			'isFirstTimeInSchedule' => $isFirstTimeInSchedule,
		);
	}

	protected function getScheduleTimes ($scheduleTime, $targetSchedule) {
		$times = ScheduleTimes::model()->findAll(array(
			'condition' => 'schedules_id = :sId AND time <= :time',
			'params' => array(
				':sId' => $this->id,
				':time' => $scheduleTime->getTime()->getTimeInSeconds(),
			),
			'order' => 'flight_number, id'
		));
		$total = array();
		foreach ($times as $item) {
			$temp = new ScheduleTimes;
			$temp->copyFrom($item, $targetSchedule);
			$total[] = $temp;
		}
		return $total;
	}

	protected function getTotalDinners ($time, $targetSchedule, $scheduleTimeBeforeEditing) {
		$total = Dinner::model()->findAll(array(
			'condition' => 'schedules_id = :sId AND end_time < :time',
			'params' => array(
				':sId' => $this->id,
				':time' => $scheduleTimeBeforeEditing->getTime()->getTimeInSeconds(),
			),
		));
		$cutDinner = Dinner::model()->find(array(
			'condition' => 'schedules_id = :sId AND end_time = :time',
			'params' => array(
				':sId' => $this->id,
				':time' => $scheduleTimeBeforeEditing->getTime()->getTimeInSeconds(),
			),
		));
		if(!empty($cutDinner)){
			$cutDinner->end_time = $time->getTimeInSeconds();
			$cutDinner->duration = $cutDinner->end_time - $cutDinner->start_time;
			array_push($total, $cutDinner);

			Yii::trace('Редагування обіда. Нове закінчення обіду: '.$cutDinner->end_time, 'app.models.schedules');
		}

		$targetDinners = array();
		if ($total){
			foreach ($total as $item) {
				$temp = new Dinner;
				$temp->copyFrom($item, $targetSchedule);
				$targetDinners[] = $temp;
			}
		}
		return $targetDinners;
	}

	protected function getWorkDurationByLastTime ($time, $totalScheduleTimes, $totalScheduleDinners, $scheduleTimeBeforeEditing) {
		$duration = new Time(0);
		$workshift = $this->getWorkshiftContaintTime ($scheduleTimeBeforeEditing->getTime());
		Yii::trace('Час: '.$scheduleTimeBeforeEditing->getTime()->format(), 'app.models.schedules');
		Yii::trace('Початок робочої зміни: '.$workshift->start_time, 'app.models.schedules');
		$totalScheduleTimes = ScheduleTimes::model()->findAll(array(
			'condition' => 'schedules_id = :sId AND time < :time AND time >= :workshift_begin',
			'params' => array(
				':sId' => $this->id,
				':time' => $time->getTimeInSeconds(),
				':workshift_begin' => $workshift->start_time,
			),
			'order' => 'flight_number, id'
		));
		for ($i = 0; $i < count($totalScheduleTimes); ++$i) {
			if (isset($totalScheduleTimes[$i + 1])){
				$interval = Time::sub($totalScheduleTimes[$i + 1]->getTime(), 
					$totalScheduleTimes[$i]->getTime());			
			} else {
				$interval = Time::sub($time, $totalScheduleTimes[$i]->getTime());
			}
			$duration = Time::add($duration, $interval);
		}
		$dinnerDuration = new Time(0);
		foreach ($totalScheduleDinners as $dinner) {
			$dinnerDuration = Time::add($dinnerDuration, new Time($dinner->duration));
		}
		$duration = Time::sub($duration, $dinnerDuration);
			
		// Yii::trace('Edit schedule: workDuration = '.$duration->getFormattedTime().', dinner duration = '.$dinnerDuration->getFormattedTime().', start time = '.$totalScheduleTimes[0]->getTime()->getFormattedTime().', end time = '.$time->getFormattedTime(), 'app.models.schedules');
		return $duration;
	}

	protected function getWorktimeFromPrevDinner ($time, $lastDinner, $scheduleTimeBeforeEditing, $targetSchedule) {
		$workshift = $targetSchedule->getWorkshiftContaintTime ($scheduleTimeBeforeEditing->getTime());
		if ($lastDinner && $lastDinner->workshift_id == $workshift->id) {
			$worktime = Time::sub($time, new Time($lastDinner->end_time));
		} else {
			$worktime = Time::sub($time, new Time($workshift->start_time));
		}
		return $worktime;
	}

	/**
	 * Повертає робочі зміни розкладу руху, які будуть редагуватись
	 * Перша робоча змін повинна включати час $time
	 *
	 * @param ScheduleTimes $time
	 * @return array Робочі зміни
	 */
	public function getWorkshiftsAfterTime ($scheduleTime) {
		$workshifts = Workshift::model()->findAll(array(
			'condition' => 'schedule_id = :sId AND end_time >= :time',
			'params' => array(
				':sId' => $this->id,
				':time' => $scheduleTime->getTime()->getTimeInSeconds(),
			),
			'order' => 'number'
		));
		return $workshifts;
	}

	public function getWorkshiftContaintTime ($time) {
		$workshift = Workshift::model()->find(array(
			'condition' => 'schedule_id = :sId AND start_time <= :time AND end_time >= :time',
			'params' => array(
				':sId' => $this->id,
				':time' => $time->getTimeInSeconds(),
			),
			'order' => 'number'
		));		
		Yii::trace('Робоча зміна: розклад - '.$this->id.', шуканий час - '.$time->getFormattedTime().', сек - '.$time->getTimeInSeconds(), 'app.models.schedules');
		return $workshift;
	}

	public function getPrevScheduleTime ($currScheduleTime, $startControlPointScenario) {
		if ($this->isFirstTimeInSchedule($currScheduleTime)) {
			return $currScheduleTime;
		}	
		if ($startControlPointScenario->number == 1){
			$flightNum = $currScheduleTime->flight_number - 1;
		} else {
			$flightNum = $currScheduleTime->flight_number;
		}
		$prevScheduleTime = ScheduleTimes::model()->find(array(
			'condition' => 'schedules_id = :sId AND flight_number = :fNum AND points_control_scenario_id = :ctrlPId',
			'params' => array(
				':sId' => $this->id,
				':fNum' => $flightNum,
				':ctrlPId' => $this->graph->route->getPrevPoint($startControlPointScenario)->id,
			),
		));
		return $prevScheduleTime;
	}

	public function clearAndDelete () {
		Workshift::model()->deleteAll('schedule_id = :sId', array(':sId'=>$this->id));
		Dinner::model()->deleteAll('schedules_id = :sId', array(':sId'=>$this->id));
		$this->delete();
	}

	public function isFirstTimeInSchedule ($currScheduleTime) {
		$min = ScheduleTimes::model()->findAll(array(
			'condition' => 'schedules_id = :sId AND flight_number = 1',
			'params' => array(
				':sId' => $this->id,
			),
			'order' => 'time ASC',
		));

		if ($currScheduleTime->id == $min[0]->id) {
			Yii::trace('Розрахунок з першого часу графіка. Знайдено перший час графіка', 'app.models.schedules');
			return true;
		} else {
			return false;
		}
	}

	public function getScheduleTime ($flightnum, $ctrlPointScenId) {
		$scheduleTime = ScheduleTimes::model()->find(array(
			'condition' => 'schedules_id = :sId AND flight_number = :fNum AND points_control_scenario_id = :ctrlPId',
			'params' => array(
				':sId' => $this->id,
				':fNum' => $flightnum,
				':ctrlPId' => $ctrlPointScenId,
			),
		));
		if (empty($scheduleTime)) {
			throw new CException('Відсутній час графіка');
		}
		return $scheduleTime;
	}

	public function tableName(){
		return 'schedules';
	}
}
?>