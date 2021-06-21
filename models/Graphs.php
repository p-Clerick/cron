<?php
class Graphs extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	public function relations()
	{
		return array(
			'orders'=>array(self::HAS_ONE, 'Orders', 'graphs_id'),
			'route'=>array(self::BELONGS_TO, 'Route', 'routes_id'),
			'moveonschedule'=>array(self::HAS_ONE, 'MoveOnSchedule', 'graphs_id'),
			'schedules' => array(self::HAS_MANY, 'Schedules', 'graphs_id'),
			'advertisement_scenario_graphs' => array(self::HAS_ONE, 'AdvertisementScenarioGraphs', 'graphs_id'),
			'graphloc' => array(self::HAS_MANY, 'Locations', 'graphs_id'),
			'moveonmap'=>array(self::HAS_MANY, 'MoveOnMap', 'graphs_id'),
			'carrier'=>array(self::BELONGS_TO, 'Carriers', 'carriers_id'),
		);
	}

	public function getCurrentSchedule ($type) {
		$today = date("Y-m-d");
		return $this->getScheduleByDate($today, $type);
	}

	/**
	 * Повертає останній розклад руху(schedule), створений до $date
	 *
	 * @param string $date Дата у форматі yyyy-mm-dd
	 * @param int $type Тип розкладу руху - робочий, вихідний etc.
	 * @return Schedule
	 */
	public function getScheduleByDate ($date, $type) {
		$timeTables = Schedules::model()->findAll('graphs_id = :id AND schedule_types_id = 	:schId AND create_date <= :today AND status = "yes" ORDER BY create_date, id', array(
			"id" => $this->id,
			'schId' => $type,
			"today" => $date,
		));

		if( !empty($timeTables)){
			$last = count($timeTables) - 1;
			return $timeTables[$last];
		} else {
			return $timeTables;
		}
	}

	/**
	 * Шукає розклади руху за період
	 * 
	 * @param string @from Дата початку періода
	 * @param string @to Дата закінчення періода
	 * @return array Асоціативний масив, де конкретній даті з періода відповідає
	 * номер розкладу руху, який був дійсний на дану дату
	 */
	public function getSchedulesByPeriod ($from, $to) {
		$schedulesData = Schedules::model()->findAll('graphs_id = :gId ORDER BY id', array(
			':gId' => $this->id,
		));

		$hollydaySchedules = array();
		$workSchedules = array();
		foreach ($schedulesData as $item) {
			switch ($item->schedule_types_id) {
				case Schedules::TYPE_WORK:
					$workSchedules[] = $item;
					break;
				case Schedules::TYPE_HOLLYDAY:
					$hollydaySchedules[] = $item;
					break;
				default: 
					throw new CException('Не знайдено тип розкладу руху: '.$item->schedule_types_id);
			}
		}

		$from = new DateTime($from);
		$to = new DateTime($to);
		$period = new DatePeriod($from, new DateInterval('P1D'), $to);
		foreach ($period as $date) {
			$type = $this->isHollyday($date) ? 'Вихідний': 'Робочий';
			echo $date->format('Y-m-d').':'.$type."\n";
		}
		// echo $from->format('Y-m-d');
		// echo $to->format('Y-m-d');
	}

	/**
	 * Перевіряє чи створено сьогодні розклад руху для графіка
	 *
	 * @param int $type
	 * @return boolean 
	 */
	public function isCreatedScheduleToday ($type) {
		$today = date("Y-m-d");
		$exists = Schedules::model()->exists(
			"graphs_id = :gId AND create_date = :today AND schedule_types_id = :type", 
			array(
				':today'=>$today, 
				':type'=>$type,
				':gId' => $this->id,
			)
		);
		return $exists;
	}

	public function changeGraphName($graphname){
		if(strlen($graphname) === 1){
			 $graphname = "00".$graphname;
		}
		if(strlen($graphname) === 2){
			 $graphname = "0".$graphname;
		}
		return $graphname;
	}

	public function getMissingConnectionBort ($from, $to) {
		$schedule = $this->getSchedulesByPeriod($from, $to);
	}

	/**
	 * Перевіряє чи є $day вихідним
	 * ToDo: додати святкові дні
	 * 
	 * @param DateTime $day День для перевірки
	 * @return boolean true якщо вихідний, інакше - false
	 */
	protected function isHollyday ($day) {
		$day = getdate($day->getTimestamp());
		if ($day['wday'] == 0 || $day['wday'] == 6) {
			return true;
		} else {
			return false;
		}
	}


    public function getGraphsById($graphs_id){
        $graph = Graphs::model()->findByPk($graphs_id);
        return (count($graph)>0) ? $graph : false;
    }

    public function getGraphByRouteIdAndGraphName($routeId,$graphName){
        $graph = Graphs::model()->find(array(
            'condition'=>'t.name=:graphName and t.routes_id =:routeId',
            'params'=>array(':graphName'=>$graphName,':routeId'=>$routeId)
        ));
        return (count($graph) >0) ? $graph : false;
    }

	public function tableName()
	{
		return 'graphs';
	}
}
?>