<?php

class Route extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function relations(){
		return array(
			'graphs' => array(self::HAS_MANY, 'Graphs', 'routes_id'),
			'content_changes' => array(self::HAS_MANY, 'ContentChanges', 'routes_id'),
			'schedules' => array(self::HAS_MANY, 'Graphs', 'routes_id'),
			'waypoint' => array(self::HAS_MANY, 'PointsDirection', 'routes_id'),
			'vehicletype' => array(self::BELONGS_TO, 'VehicleType', 'transport_types_id'),
			'routemovemethod' => array(self::BELONGS_TO, 'RouteMoveMethods', 'move_methods_id'),
			'dayintervals' => array(self::HAS_MANY, 'DayInterval', 'routeid', 'order'=>'starttime ASC'),
			'controlPointScenarios' => array(self::HAS_MANY, 'ControlPointScenario',  'routes_id', 'order'=>'number ASC'),
			'points_control_scenario'=>array(self::HAS_MANY, 'PointsControlScenario', 'routes_id'),
			'advertisement_scenario'=>array(self::HAS_MANY, 'AdvertisementScenario', 'routes_id'),
			'stops_scenario'=>array(self::HAS_MANY, 'StopsScenario', 'routes_id'),
			'stations_scenario'=>array(self::HAS_MANY, 'StationsScenario', 'routes_id'),
			'carrier' => array(self::BELONGS_TO, 'Carriers', 'carriers_id'),
			'routesettings'=>array(self::HAS_MANY, 'RouteSettings', 'routes_id'),			
			'routeloc'=>array(self::HAS_MANY, 'Locations', 'routes_id'),
			'moveonmap'=>array(self::HAS_MANY, 'MoveOnMap', 'routes_id'),
			'moveonsch'=>array(self::HAS_MANY, 'MoveOnSchedule', 'routes_id'),
		);
	}

	public function getChildren(){
		$res = array();
		$carrier_id = Yii::app()->user->carrier->id;
		if ($carrier_id == 0){
			$graphs = $this->schedules;
		} else {
			$graphs = Graphs::model()->findAll(array(
				'condition' => 'routes_id = :rId AND carriers_id = :cId',
				'params' => array(
					':rId' => $this->id,
					':cId' => $carrier_id,
				),
			));
		}
		foreach($graphs as $item){
    		$resn[$item->id] = $item->name;
    	}
        asort($resn, SORT_NUMERIC);
        while (list($key, $value) = each($resn)) {
            $res[] = array(
                  'id'      => $key,
                  'name'    => $value,
            );
            
        }
    	return $res;		
		/*foreach($graphs as $item){
			$res[] = array(
				'id'=> $item->id,
				'name' => $item->name,
			);
		}
		return $res;*/
	}
	public function changeRouteName($routename){
			 if(strlen($routename) === 1){
				 $routename = "00".$routename;
			 }
			 if(strlen($routename) === 2){
				 $routename = "0".$routename;
			 }
			 $pos_a = strpos($routename, "a");
			 if($pos_a){
				 $routename =  str_replace("a","", $routename);
				 $routename = "9".$routename;
			 }
				 $routename =  str_replace("/","0", $routename);
			 return $routename;

	}

	/**
	 * Повертає кінцеві точки маршруту
	 * 
	 * @return array Кінцеві точки
	 */
	public function getExtremePoints () {
		$points = $this->controlPointScenarios;
		$lastIndex = count($points) / 2 - 1;
		return array(
			'direct' => array(
				'first' => $points[0],
				'last' => $points[$lastIndex],
			),
			'reverse' => array(
				'first' => $points[$lastIndex + 1],
				'last' => $points[($lastIndex * 2) + 1],
			),
		);
	}

	/**
	 * Знаходить тривалість рейсу
	 * 
	 * @param ControlPointScenario $ctrlPoint Контрольна точка, що визначає
	 * початок рейсу
	 * @param Time $currTime Поточний час розрахунку
	 * @return Time Тривалість рейсу
	 */
	public function getFlightDurationByPoint ($ctrlPoint, $currTime) {
		// echo "\n".'Time: '.$currTime->getFormattedTime()."\n";
		$points = $this->getFlightPoints($ctrlPoint);
		$duration = 0;
		$time = $currTime;
		foreach ($points as $item) {
			$dayInt = DayInterval::searchDayIntByTime($this, $time);
			$interval = $item->getControlPointInterval($dayInt)->interval;
			// echo "K.p: ".$item->number." \n";
			// echo "Inter: ".$interval." \n";
			$duration += $interval;
			$time = Time::add($time, new Time($interval));
			// echo 'T: '.$time->getFormattedTime()." \n";

		}
		return new Time($duration);
	}

	/**
	 * Знаходить тривалість руху від точки $ctrlPoint до $ctrlPoint 
	 * наступного рейсу починаючи з часу $time
	 *
	 * @param ControlPointScenario $ctrlPoint Контрольна точка маршрута
	 * @param ScheduleTimes $scheduleTime  Поточний час розкладу руху
	 * @return Time Тривалість руху
	 */
	public function getFlightDurationBySpecificPoint ($ctrlPoint, $scheduleTime) {
		$points = $this->controlPointScenarios;
		$duration = 0;
		$time = $scheduleTime;
		$countOfCtrlPoints = $this->getCountOfCtrlPoints();
		for ($i = 0, $number = $ctrlPoint->number - 1; $i < $countOfCtrlPoints; ++$i, ++$number) {
			if ($number == $countOfCtrlPoints) {
				$number = 0;
			}
			$dayInt = DayInterval::searchDayIntByTime($this, $time);
			$interval = $points[$number]->getControlPointInterval($dayInt)->interval;
			// echo "K.p: ".$item->number." \n";
			// echo "Inter: ".$interval." \n";
			$duration += $interval;
			$time = Time::add($time, new Time($interval));
			// echo 'T: '.$time->getFormattedTime()." \n";
		}
		return new Time($duration);
	}

	/**
	 * Знаходить всі контрольні точки для рейсу, що починається
	 * з контрольної точки $ctrlPoint.
	 * В даній функції рейс - це рух від однієї кінцевої контрольної точки
	 * до іншої. Це не той рейс що в 15 чи 15а (Луцьк) і взагалі в базі на 
	 * даний момент
	 * 
	 * @return array Контрольні точки рейса
	 */
	protected function getFlightPoints ($ctrlPoint) {
		$result = array();
		$extremePoints = $this->getExtremePoints();
		$points = $this->controlPointScenarios;
		if ($ctrlPoint->number == $extremePoints['direct']['first']->number) {
			$startNumber = $extremePoints['direct']['first']->number - 1;
			for ($i = $startNumber; $i < $extremePoints['direct']['last']->number; ++$i) {
				$result[] = $points[$i];
			}
		} else if ($ctrlPoint->number == $extremePoints['reverse']['first']->number) {
			$startNumber = $extremePoints['reverse']['first']->number - 1;
			for ($i = $startNumber; $i < $extremePoints['reverse']['last']->number; ++$i) {
				$result[] = $points[$i];
			}
		} else {
			throw new CException('Контрольна точка '.$ctrlPoint->id.' не є початком рейсу'); 
		}
		return $result;
	}

	/**
	 * Перевіряє чи є контрольна точка $point першою в рейсі
	 * 
	 * @param ControlPointScenario $point
	 * @return boolean
	 */
	public function isFirstPointInFlight ($point) {
		$extremePoints = $this->getExtremePoints();
		$points = $this->controlPointScenarios;
		if ($point->number == $extremePoints['direct']['first']->number 
			|| $point->number == $extremePoints['reverse']['first']->number) {
			return true;
		}
		return false;
	}

	/**
	 * Знаходить наступну по порядку контрольну точку маршрута
	 *
	 * @param ControlPointScenario $ctrlPoint Попередня контрольна точка
	 * @return ControlPointScenario Наступна контрольна точка
	 */
	public function getNextPoint ($ctrlPoint) {
		$countOfCtrlPoints = $this->getCountOfCtrlPoints();
		$currNumber = $ctrlPoint->number;
		if ($currNumber != $countOfCtrlPoints) {
			$nextNumber = $currNumber + 1;
		} else {
			$nextNumber = 1;
		}
		$nextPoint = ControlPointScenario::model()->find('routes_id = :rId AND number = :number', array(
			':rId' => $this->id,
			':number' => $nextNumber,
		));
		return $nextPoint;
	}

	/**
	 * Знаходить попередню по порядку контрольну точку маршрута
	 *
	 * @param ControlPointScenario $ctrlPoint Наступна контрольна точка
	 * @return ControlPointScenario Попередня контрольна точка
	 */
	public function getPrevPoint ($ctrlPoint) {
		$countOfCtrlPoints = $this->getCountOfCtrlPoints();
		$currNumber = $ctrlPoint->number;
		if ($currNumber == 1) {
			$prevNumber = $countOfCtrlPoints;
		} else {
			$prevNumber = $currNumber - 1;
		}
		$prevPoint = ControlPointScenario::model()->find('routes_id = :rId AND number = :number', array(
			':rId' => $this->id,
			':number' => $prevNumber,
		));
		return $prevPoint;
	}

	public function getCountOfCtrlPoints () {
		return count($this->controlPointScenarios);
	}

	public function tableName(){
		return 'routes';
	}

	/**
     * Повертає масив із даними про всі активні маршрути.
     *
	 * @return {array} $arr Масив із даними про всі активні маршрути.
     */
    public function getAllActiveRoutes() {
    	/**
    	 * SQL-запит до бази даних
    	 */
        $sql = $this->findAll(array(
        	'condition' => 'status = :status',
        	'params' => array (':status' => 'yes'),
        	'order' => 'id')); 

        foreach ($sql as $s) {
            $arr[] = array(
                'id' => $s->id,
                'name' => $s->name,
                'cost' => $s->cost
            );
        }

        return $arr;
    }

    /**
     * Повертає масив із даними про всі маршрути та перевізників, які за ними закріплені
     *
	 * @return {array} $arr Масив із даними про всі маршрути та перевізників, які за ними закріплені
     */
    public function getAllRoutesCarriers() {
    	/**
    	 * SQL-запит до бази даних
    	 */
        $sql = $this->findAll(array('order' => 'id')); 

        foreach ($sql as $s) {
            $arr[$s['id']] = array(
                //'name' =>$s->name,
                'carriers_id' => $s->carriers_id
            );
        }

        return $arr;
    }

    /**
     * Повертає масив з даними про всі маршрути міста відсортований по назві зупинок.
     *
     * @return {array} $arr Масив з даними про всі маршрути міста.
     */
    public function getAllRoutesOrderByName() {
        $sql = $this->findAll(array(
            'order' => 'name'
        )); 

        if ($sql) {
            foreach ($sql as $s) {
                $arr[] = array(
                    'id'        => $s->id,
                    'name'      => $s->name
                );
            }
            return $arr;
        }
        else
            return null;
    }

    public function getRouteById($routes_id){
        $route = Route::model()->findByPk($routes_id);
        return (count($route)>0) ? $route : false;
    }

    public function getRouteByName($routeName){
        $route = Route::model()->find(array(
            'condition'=>'t.name=:routeName',
            'params'=>array(':routeName'=>$routeName)
        ));
        return (count($route)>0) ? $route : false;
    }
}